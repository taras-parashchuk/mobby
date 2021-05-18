<?php

namespace App\Services\Moysklad;

use App\Models\Category;
use App\Models\CategoryExternalSource;
use App\Models\CategoryTranslation;
use App\Models\Language;
use App\Models\ProductExternalSource;
use App\Models\Setting;
use App\Models\Sync;
use App\Services\Moysklad\Client;
use App\Services\Moysklad\Service;


class CategoryService
{
    public static function getExternalCategories($sync, $offset = 0, $limit = 25)
    {
        $productFolders = [];
        $client = new Client();

        try {
            $res = $client->getGuzzle('productfolder', [
                'query' => [
                'offset' => $offset,
                'limit' => $limit,
                ]
            ]);
        } catch (\Exception $exception) {
            Service::exception($exception, $sync);
            return $productFolders;
        }

        $product_folders_info = json_decode($res->getBody()->getContents());

        $productFolders['rows'] = $product_folders_info->rows;
        $productFolders['total'] = $product_folders_info->meta->size;

        return $productFolders;
    }

    public static function storeDB($productFolder, $sync)
    {
        $categoryExternal = CategoryExternalSource::where('external_category_id', $productFolder->id)->first();

        if (is_null($categoryExternal)) {
            $category = new Category();

            if (property_exists($productFolder, 'productFolder')) {

                if (isset($productFolder->productFolder->meta->uuidHref)) {
                    $parentId = Service::extractIdFromMeta($productFolder->productFolder->meta->uuidHref);
                }

                $parentCategoryExternal = CategoryExternalSource::where('external_category_id', $parentId)->first();
                $category->parent_id = $parentCategoryExternal->category_id;
            }

            $category->slug = \Str::slug($productFolder->name);
            $category->save();

            $translations = [];
            foreach (Language::getOnlyActive() as $language) {
                $translations[] = new CategoryTranslation(
                    [
                        'name' => $productFolder->name,
                        'locale' => $language['locale']
                    ]
                );
            }
            $category->translates()->saveMany($translations);

            $categoryExternalSource = new CategoryExternalSource([
                'external_category_id' => $productFolder->id,
                'external_type' => config('syncs.moysklad.externalCode')
            ]);

            $category->externalSource()->save($categoryExternalSource);

            $assortments = ProductService::getAssortmentsByProductFolder($sync, $productFolder->meta->href);

            if (!is_null($assortments)) {
                foreach ($assortments as $assortment) {
                    $productExternalSource = ProductExternalSource::where('external_product_id', $assortment->id)
                        ->where('external_type', config('syncs.moysklad.externalCode'))
                        ->with('product')
                        ->first();
                    if (!is_null($productExternalSource)) {
                        $product = $productExternalSource->product;
                        $product->category_id = $category->id;
                        $product->update();

                        // Save category to product_to_categories and product_to_compare_categories
                        ProductService::saveCategoryToTables($product, 'upload');
                    }
                }
            }
        }
    }

    public static function recursiveCategories($categories, Sync $sync = null)
    {
        foreach ($categories as $category) {

            foreach ($category->translates as $translate) {
                if ($translate->locale == Setting::get('admin_language')) {
                    $data = [
                        'name' => $category->translates[0]->name,
                        'description' => $category->translates[0]->description
                    ];
                }
            }

            if (!is_null($category->parent_id)) {
                $externalSource = $category->parent->externalSource()->where('external_type', config('syncs.moysklad.externalCode'))->first();
                if (!is_null($externalSource)) {
                    $parent = Service::getMeta($externalSource->external_category_id, 'productfolder');
                    $data['productFolder'] = Service::formMeta($parent);
                }
            }

            $client = new Client();
            try {
                $result = $client->postGuzzle('productfolder', [
                    'json' => $data
                ]);
            } catch (\Exception $exception) {
                Service::exception($exception, $sync, trans('moy-sklad.errors.unknown_error_with_details', [
                    'id' => $category->id,
                    'name' => $category->translates[0]->name
                ]));
            }

            if(!is_null($sync)){
                $sync->refresh();
                if ($sync->fatal_error) {
                    break;
                }
            }

            $externalCategory = json_decode($result->getBody()->getContents());

            $categoryExternalSource = new CategoryExternalSource([
                'external_category_id' => $externalCategory->id,
                'external_type' => config('syncs.moysklad.externalCode')
            ]);

            $category->externalSource()->save($categoryExternalSource);

            if(!is_null($sync)){
                $sync->current++;
                $sync->success_categories_count++;

                $sync->update();
            }

            ProductService::updateProductsCategory($category->id, $externalCategory->meta);

            if ($category->children->count()) {
                self::recursiveCategories($category->children, $sync);
            }
        }
    }

    /**
     * Update category in DB
     *
     * @param $productFolder
     * @param $category
     */
    public static function updateDB($productFolder, $category, $sync)
    {
        $parameters = Service::getUpdateParameters('download', config('syncs.moysklad.dataTypes.category'), $sync);
        if (empty($parameters)) {
            return false;
        }

        if (isset($parameters['name']) && $parameters['name']) {
            $category->slug = \Str::slug($productFolder->name);
            $translations = [];
            foreach (Language::getOnlyActive() as $language) {
                $translations[] = new CategoryTranslation(
                    [
                        'name' => $productFolder->name,
                        'locale' => $language['locale']
                    ]
                );
            }
            $category->translates()->delete();
            $category->translates()->saveMany($translations);
        }

        if (isset($parameters['category_hierarchy']) && $parameters['category_hierarchy']) {
            if (property_exists($productFolder, 'productFolder')) {
                $parentExternalId = \App\Services\Moysklad\Service::extractId($productFolder->productFolder->meta->href);
                $categoryExternalSource = CategoryExternalSource::where('external_category_id', $parentExternalId)
                    ->where('external_type', config('syncs.moysklad.externalCode'))
                    ->with('category')
                    ->first();

                if (!is_null($categoryExternalSource)) {
                    $category->parent_id = $categoryExternalSource->category->id;
                }
            }
        }

        $category->update();
    }

    public static function updateMoyskladCategories($categories, $sync = null)
    {
        $parameters = Service::getUpdateParameters('upload', config('syncs.moysklad.dataTypes.category'), $sync);
        if (empty($parameters)) {
            return false;
        }

        foreach ($categories as $category) {
            $data = [];
            if (isset($parameters['name']) && $parameters['name']) {
                foreach ($category->translates as $translate) {
                    if ($translate->locale == Setting::get('admin_language')) {
                        $data = [
                            'name' => $category->translates[0]->name
                        ];
                    }
                }
            }

            if (isset($parameters['category_hierarchy']) && $parameters['category_hierarchy']) {
                if (!is_null($category->parent_id)) {
                    $externalSource = $category->parent->externalSource()->where('external_type', config('syncs.moysklad.externalCode'))->first();
                    if (!is_null($externalSource)) {
                        $parent = Service::getMeta($externalSource->external_category_id, 'productfolder');
                        $data['productFolder'] = Service::formMeta($parent);
                    }
                }
            }

            if (!empty($data)) {
                $client = new Client();
                $externalSource = $category->externalSource()->where('external_type', config('syncs.moysklad.externalCode'))->first();
                $path = 'productfolder/' . $externalSource->external_category_id;

                try {
                    $client->putGuzzle($path, [
                        'json' => $data
                    ]);
                } catch (\Exception $exception) {
                    Service::exception($exception, $sync);
                }
            }

            if (!is_null($sync)) {
                $sync->current++;
                $sync->success_categories_count++;
                $sync->update();
            }

            if ($category->children->count()) {
                self::updateMoyskladCategories($category->children, $sync);
            }
        }
    }
}
