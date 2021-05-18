<?php

namespace App\Services\Moysklad;

use App\Models\Attribute;
use App\Models\AttributeToProduct;
use App\Models\AttributeValue;
use App\Models\AttributeValueToProduct;
use App\Models\Category;
use App\Models\CategoryExternalSource;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductExternalSource;
use App\Models\ProductToCategory;
use App\Models\ProductToCompareCategory;
use App\Models\ProductTranslation;
use App\Models\ProductVariantAttributeValue;
use App\Models\Syncs\ExternalApi;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Collection;


class ProductService
{
    private static $supplyService;
    private static $parameters;

    /**
     * Uploading products from database to moy-sklad
     *
     * @param $products
     * @param $sync
     */
    public static function upload($products, $sync = null)
    {
        self::$supplyService = new SupplyService();
        $currenciesService = new CurrenciesService();

        if (!($products instanceof Collection)) {
            $products = [$products];
        }

        foreach ($products as $product) {
            $data = [
                'name' => $product->translateForAdmin->name,
                'salePrices' => [(object)[
                    'value' => $product->warehouse_price * 100,
                    'priceType' => 'Цена продажи',
                    'currency' => Service::formMeta($currenciesService->getCurrency($product->currency_code)->meta)
                ]],
                'description' => '', //$product->translate->description,
            ];

            if (!is_null($product->sku)) {
                $data['article'] = $product->sku;
            }

            if (!is_null($product->category_id)) {
                $category = Category::where('id', $product->category_id)
                    ->whereHas('externalSource', function (Builder $query) {
                        $query->where('external_type', config('syncs.moysklad.externalCode'));
                    })->with('externalSource')->first();

                if (!is_null($category)) {
                    $parent = Service::getMeta($category->externalSource[0]->external_category_id, 'productfolder');
                    $data['productFolder'] = Service::formMeta($parent);
                }
            }

            $client = new Client();
            try {
                $result = $client->postGuzzle('product', [
                    'json' => $data
                ]);
            } catch (RequestException $exception) {
                if ($exception->getCode() == 404) {
                    Service::writeLog(config('syncs.moysklad.dataTypes.product'), trans('moy-sklad.errors.category.not_exist', [
                            'id' => $product->id,
                            'name' => $product->translateForAdmin->name
                        ]
                    ), $sync);

                    unset($data['productFolder']);
                    $result = $client->postGuzzle('product', [
                        'json' => $data
                    ]);
                } else {
                    Service::writeLog(config('syncs.moysklad.dataTypes.product'), trans('moy-sklad.errors.product.product_not_uploaded', [
                        'id' => $product->id,
                        'name' => $product->translateForAdmin->name,
                    ]));
                }
            }

            if (isset($result)) {
                $moySkladProduct = json_decode($result->getBody()->getContents());

                //add supply position
                $quantity = $product->warehouse_quantity;
                $assortmentMeta = $moySkladProduct->meta;
                $productExternalSource = new ProductExternalSource([
                    'external_product_id' => $moySkladProduct->id,
                    'external_type' => config('syncs.moysklad.externalCode')
                ]);

                $product->externalSource()->save($productExternalSource);
                self::$supplyService->storeSupplyPosition($assortmentMeta, $quantity, $sync);

                // Upload images
                // ImageService::uploadProductImages($product);

                if (!is_null($sync)) {
                    $sync->current++;
                    $sync->success_products_count++;
                    $sync->update();
                }
            }
        }
    }

    /**
     * Get all assortments (products) from moy-sklad
     *
     * @param $sync
     * @param string $scope
     * @param int $limit
     * @return array
     */
    public static function getAssortments($sync, $scope = 'consignment', $offset = 0, $limit = 25)
    {
        $assortments = [
            'rows' => [],
            'total' => 0
        ];
        $client = new Client();

        try {
            $res = $client->getGuzzle('assortment', [
                'query' => [
                    'offset' => $offset,
                    'limit' => $limit,
                    'scope' => $scope
                ]
            ])->getBody()->getContents();

            $assortment_info = json_decode($res);

            $assortments['rows'] = $assortment_info->rows;
            $assortments['total'] = $assortment_info->meta->size;

        } catch (\Exception $exception) {
            Service::exception($exception, $sync);
            return $assortments;
        }

        return $assortments;
    }

    /**
     * Creating new product from moy-sklad to database
     *
     * @param $product
     * @param $sync
     */
    public static function storeDBProduct($product, $sync)
    {
        $dbProduct = self::prepareProductForStore($product, $sync);
        if (is_null($dbProduct)) {
            return null;
        }

        $translations = self::prepareProductTranslatesForStore($product);

        $productExternalSource = new ProductExternalSource([
            'external_product_id' => $product->id,
            'external_type' => config('syncs.moysklad.externalCode')
        ]);

        try {
            \DB::transaction(function () use ($dbProduct, $translations, $productExternalSource) {

                $dbProduct->save();

                $dbProduct->translates()->saveMany($translations);

                $dbProduct->externalSource()->save($productExternalSource);

            });
        } catch (\Throwable $exception) {
            Service::writeLog(config('syncs.moysklad.dataTypes.product'), trans('moy-sklad.errors.product.product_not_download', [
                'product_url' => $dbProduct->category_id,
            ]), $sync);
            Service::exception($exception);
            \Log::error($exception->getMessage());
            return null;
        }

        // Save category to product_to_categories and product_to_compare_categories
        self::saveCategoryToTables($dbProduct);

        // store product images
        ImageService::storeImages($product, $dbProduct);

        return $dbProduct;

    }

    /**
     * Update product from moy-sklad to database
     *
     * @param $dbProduct
     * @param $product
     */
    public static function updateDBProduct($dbProduct, $product, $sync)
    {
        self::$parameters = Service::getUpdateParameters('download', config('syncs.moysklad.dataTypes.product'), $sync);
        $parameters = self::$parameters;

        if (isset($parameters['name']) && $parameters['name']) {
            foreach ($dbProduct->translates as $translate) {
                $translate->name = $product->name;
                $translate->update();
            }
        }

        if (isset($parameters['description']) && $parameters['description'] && property_exists($product, 'description')) {
            foreach ($dbProduct->translates as $translate) {
                $translate->description = $product->description;
                $translate->update();
            }
        }

        if (isset($parameters['article']) && $parameters['article'] && property_exists($product, 'article')) {
            $dbProduct->sku = $product->article;
        }

        if (isset($parameters['categories']) && $parameters['categories'] && property_exists($product, 'productFolder')) {
            $externalSourceCategoryId = Service::extractId($product->productFolder->meta->href);
            $categoryExternalSource = CategoryExternalSource::where('external_category_id', $externalSourceCategoryId)
                ->where('external_type', config('syncs.moysklad.externalCode'))
                ->with('category')
                ->first();

            if (!is_null($categoryExternalSource)) {
                $dbProduct->category_id = $categoryExternalSource->category->id;
                $dbProduct->update();

                self::saveCategoryToTables($dbProduct, 'upload');
            } else {
                Service::writeLog(config('syncs.moysklad.dataTypes.product'), trans('moy-sklad.errors.category.not_related', [
                    'id' => $dbProduct->category_id,
                    'category_id' => $externalSourceCategoryId,
                ]), $sync);
            }
        }

        if (isset($parameters['price_quantity']) && $parameters['price_quantity']) {
            $dbProduct->warehouse_quantity = $product->quantity;
            $dbProduct->warehouse_price = $product->salePrices[0]->value / 100;
        }

        if (isset(self::$parameters['images']) && self::$parameters['images']) {
            ImageService::storeImages($product, $dbProduct);
        }

        $dbProduct->update();

        return $dbProduct;
    }

    /**
     * Update products quantity and price from moy-sklad to database
     *
     * @param $stocks
     * @param $sync
     */
    public static function updateQunntityAndPrice($stocks, $sync)
    {
        foreach ($stocks['rows'] as $item) {
            $id = Service::extractId($item->meta->href);
            $productExternalSource = ProductExternalSource::where('external_product_id', $id)->with('product')->first();

            if (!is_null($productExternalSource) && !is_null($productExternalSource->product_id)) {
                $productExternalSource->product->warehouse_quantity = $item->quantity;
                $productExternalSource->product->warehouse_price = $item->salePrice / 100;

                $productExternalSource->product->update();

                if ($item->meta->type == 'product') {
                    $sync->success_products_count++;
                }
            } else {
                Service::writeLog(config('syncs.moysklad.dataTypes.product'), trans('moy-sklad.errors.product.not_exist', [
                    'name' => $item->name
                ]), $sync);
            }

            $sync->current++;
            $sync->update();

        }
    }

    /**
     * Update product category in moy-sklad
     *
     * @param $category_id
     * @param $categoryExternalMeta
     */
    public static function updateProductsCategory($category_id, $categoryExternalMeta)
    {
        $products = Product::where('category_id', $category_id)->whereIn('type', [1, 2])->whereHas('externalSource', function (Builder $query) {
            $query->where('external_type', config('syncs.moysklad.externalCode'));
        })->get();

        if (!is_null($products)) {
            $client = new Client();
            foreach ($products as $product) {
                $path = 'product/' . $product->externalSource[0]->external_product_id;
                $data['productFolder'] = Service::formMeta($categoryExternalMeta);

                try {
                    $client->putGuzzle($path, [
                        'json' => $data
                    ]);
                } catch (\Exception $exception) {
                    Service::writeLog(config('syncs.moysklad.dataTypes.category'), trans('moy-sklad.errors.product.not_exist', [
                            'name' => $product->translateForAdmin->name,
                        ]
                    ));
                }
            }
        }
    }

    /**
     * Get products from moy-sklad by category
     *
     * @param $sync
     * @param $href
     * @param int $limit
     * @return array
     */
    public static function getAssortmentsByProductFolder($sync, $href, $limit = 25)
    {
        $offset = 0;
        $assortments = [];
        $client = new Client();
        $filter = 'productFolder=' . $href;

        while (true) {
            try {
                $res = $client->getGuzzle('assortment', [
                    'query' => [
                        'offset' => $offset,
                        'limit' => $limit,
                        'filter' => $filter,
                    ]
                ]);
            } catch (\Exception $exception) {
                Service::exception($exception, $sync);
            }
            $data = json_decode($res->getBody()->getContents())->rows;
            $assortments = array_merge($assortments, $data);

            if (count($data) < $limit) {
                break;
            }
            $offset += $limit;
        }

        return $assortments;
    }

    /**
     * Update products in moy-sklad according to the parameters
     *
     * @param $products
     * @param $sync
     */
    public static function updateMoyskladProducts($products, $sync = null)
    {
        self::$parameters = Service::getUpdateParameters('upload', config('syncs.moysklad.dataTypes.product'), $sync);

        if (empty(self::$parameters)) {
            return false;
        }

        foreach ($products as $product) {
            $data = self::prepareUpdateProduct($product);

            if (isset(self::$parameters['images']) && self::$parameters['images']) {
                ImageService::uploadProductImages($product);
            }

            $client = new Client();
            $externalSource = $product->externalSource()->where('external_type', config('syncs.moysklad.externalCode'))->first();
            $path = 'product/' . $externalSource->external_product_id;
            try {
                $result = $client->putGuzzle($path, [
                    'json' => $data
                ]);
            } catch (RequestException $exception) {
                if ($exception->getCode() == 404) {
                    Service::writeLog(config('syncs.moysklad.dataTypes.product'), trans('moy-sklad.errors.category.not_exist', [
                            'id' => $product->id,
                            'name' => $product->translateForAdmin->name
                        ]
                    ), $sync);

                    unset($data['productFolder']);

                    try{
                        $result = $client->putGuzzle($path, [
                            'json' => $data
                        ]);
                    }catch (\Throwable $exception){
                        \Log::critical($exception->getMessage());
                    }

                } else {
                    Service::writeLog(config('syncs.moysklad.dataTypes.product'), trans('moy-sklad.errors.product.product_not_uploaded', [
                        'id' => $product->id,
                        'name' => $product->translateForAdmin->name,
                    ]));
                }
            }

            if ($result && !is_null($sync)) {
                $sync->current++;
                $sync->success_products_count++;
                $sync->update();
            }
        }
    }

    /**
     * Prepare product and variant for store in DB
     *
     * @param $product
     * @param $sync
     * @param null $db
     * @return Product|null
     */
    private static function prepareProductForStore($product, $sync, $db = null)
    {
        $dbProduct = new Product();

        // prepare currency
        $currencyService = new CurrenciesService();
        $isoCode = $currencyService->getExternalIsoCode($product->salePrices[0]->currency->meta->href);
        if (is_null($isoCode)) {
            Service::writeLog(config('syncs.moysklad.dataTypes.product'), trans('moy-sklad.errors.product.currency_not_exist', [
                    'name' => $product->name,
                ]
            ), $sync);

            return null;
        }

        if ($product->meta->type == 'product') {
            if (property_exists($product, 'productFolder')) {
                $externalCategoryId = Service::extractIdFromMeta($product->productFolder->meta->uuidHref);
                $categoryExternalSource = CategoryExternalSource::where('external_category_id', $externalCategoryId)
                    ->where('external_type', config('syncs.moysklad.externalCode'))
                    ->with('category')
                    ->first();

                if (!is_null($categoryExternalSource)) {
                    $dbProduct->category_id = $categoryExternalSource->category->id;
                }
            }

            if ($product->meta->type == 'product') {
                $dbProduct->type = $product->modificationsCount ? 2 : 1;
                $dbProduct->sku = property_exists($product, 'article') ? $product->article : null;
                $dbProduct->warehouse_quantity = property_exists($product, 'quantity') ? $product->quantity : null;
            }


            $dbProduct->warehouse_price = property_exists($product, 'salePrices') ? $product->salePrices[0]->value / 100 : null;
            $dbProduct->currency_code = $isoCode;
            $dbProduct->slug = \Str::slug($product->name);

            return $dbProduct;
        }

        Service::writeLog(config('syncs.moysklad.dataTypes.product'), trans('moy-sklad.errors.product.data_type_is_not_supported', [
                'data_type' => $product->meta->type,
            ]
        ), $sync);

        return null;
    }

    /**
     * Save category to product_to_categories and product_to_compare_categories
     *
     * @param $dbProduct
     */
    public static function saveCategoryToTables($dbProduct, $action = 'download')
    {
        if (!is_null($dbProduct->category_id)) {
            $categories = Category::getGroupCategories($dbProduct->category_id);

            if ($action == 'upload') {
                $dbProduct->to_categories()->delete();
                $dbProduct->to_compare_categories()->delete();
            }

            foreach ($categories as $category_id) {

                $productToCategory = new ProductToCategory();
                $productToCategory->category_id = $category_id;

                $productToCompareCategory = new ProductToCompareCategory();
                $productToCompareCategory->category_id = $category_id;

                try{
                    $dbProduct->to_compare_categories()->save($productToCompareCategory);
                    $dbProduct->to_categories()->save($productToCategory);
                }catch (\Throwable $exception){
                    \Log::error($exception->getMessage());
                    die();
                }

            }

        }
    }

    private static function prepareProductTranslatesForStore($product)
    {
        $translations = [];
        foreach (Language::getOnlyActive() as $language) {
            $translation = new ProductTranslation(
                [
                    'name' => $product->name,
                    'locale' => $language['locale'],
                ]
            );

            if (property_exists($product, 'description')) {
                $translation->description = $product->description;
            }

            $translations[] = $translation;
        }

        return $translations;
    }

    private static function prepareUpdateProduct($product)
    {
        $data = [];
        $parameters = self::$parameters;

        if (isset($parameters['name']) && $parameters['name']) {
            $data['name'] = $product->translateForAdmin->name;
        }

        if (isset($parameters['article']) && $parameters['article']) {
            $data['article'] = (string)$product->sku;
        }

        if (isset($parameters['description']) && $parameters['description']) {
            $data['description'] = $product->translate->description;
        }

        if (isset($parameters['categories']) && $parameters['categories']) {
            if (!is_null($product->category_id)) {
                $category = Category::where('id', $product->category_id)
                    ->whereHas('externalSource', function (Builder $query) {
                        $query->where('external_type', config('syncs.moysklad.externalCode'));
                    })->with('externalSource')->first();

                if (!is_null($category)) {
                    $parent = Service::getMeta($category->externalSource[0]->external_category_id, 'productfolder');
                    $data['productFolder'] = Service::formMeta($parent);
                }
            }
        }

        return $data;
    }

    /**
     * Update category to product_to_categories and product_to_compare_categories
     *
     * @param $dbProduct
     */
    private static function updateCategoryToTables($dbProduct)
    {
        if (!is_null($dbProduct->category_id)) {
            $categories = Category::getGroupCategories($dbProduct->category_id);

            $productToCategories = [];
            $productToCompareCategory = [];
            foreach ($categories as $category) {
                $productToCategories[] = new ProductToCategory([
                    'category_id' => $category
                ]);

                $productToCompareCategory[] = new ProductToCompareCategory([
                    'category_id' => $category
                ]);
            }

            $dbProduct->to_categories()->update($productToCategories);
            $dbProduct->to_compare_categories()->update($productToCompareCategory);
        }
    }
}
