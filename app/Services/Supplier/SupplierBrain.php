<?php

namespace App\Services\Supplier;


use App\Helpers\HelperFunction;
use App\Models\SupplierProduct;
use App\Models\Sync;
use GuzzleHttp\Client;

class SupplierBrain extends SupplierBase implements SupplierInterface
{
    private $auth_url = 'http://api.brain.com.ua/auth';

    private static $auth_token;

    private $count_products_in_current_category = null;

    private $allow_categories = [
        '1181', //Ноутбуки, планшеты	Brain
        '1331', //Компьютеры, аксессуары	Brain
        '1330', //Комплектующие для ПК	Brain
        '1513', //ТВ, Аудио, Видео, Фото	Brain
        '1266', //Смартфоны, связь, навигация	Brain
        //'1378', //Периферия, оргтехника	Brain
        '8162', //Гаджеты (Hi-Tech)	Brain
        '1392', //Сетевое оборудование	Brain
        '1359', //GSM модемы	Brain
    ];

    private $original_categories_uuid = [];

    public function __construct()
    {
        parent::__construct('Brain');

        $this->auth(config('suppliers.brain.login'), config('suppliers.brain.password'));
    }

    private function auth(string $login, string $password): SupplierBrain
    {
        try {
            $response = json_decode((new Client())->post($this->auth_url, [
                'form_params' => [
                    'login' => $login,
                    'password' => md5($password)
                ]
            ])->getBody());

            if ($response->status === 1 && isset($response->result)) {
                $this::$auth_token = $response->result;
            } else {
                throw new \Exception($response->error_message);
            }

        } catch (\Exception $exception) {
            \Log::error('Can\'t connect to brain supplier. ' . $exception->getMessage());

            throw \Illuminate\Validation\ValidationException::withMessages([
                [$exception->getMessage()],
            ]);

        }

        return $this;
    }

    public function sync()
    {
        switch ($this->getSettings()->iteration_type) {
            case 'Products':

                $this->downloadAndSaveProducts();

                $this->changeIterationType(null);

                $this->removeSettings();

                break;
            case 'Categories':
            default:
                $this->downloadCategories()->saveCategories()->changeIterationType('Products');

                self::sync();

                break;

        }
    }

    public function downloadCategories(): SupplierBrain
    {
        try {

            $response = json_decode((new Client())->get("http://api.brain.com.ua/categories/{$this::$auth_token}")->getBody());

            if ($response->status === 1 && isset($response->result)) {
                foreach ($response->result as $category) {

                    if (in_array($category->categoryID, $this->allow_categories) || in_array($category->parentID, array_column($this->categories, 'supplier_uuid'))) {

                        $this->categories[] = [
                            'supplier_code' => $this->getSupplierCode(),
                            'supplier_uuid' => (string)($category->realcat ? $category->realcat : $category->categoryID),
                            'parent_id' => (string)$category->parentID,
                            'name' => $category->name
                        ];

                    }
                }
            }


        } catch (\Exception $exception) {
            \Log::error('Can\'t get categories from brain supplier. ' . $exception->getMessage());

            throw \Illuminate\Validation\ValidationException::withMessages([
                [$exception->getMessage()],
            ]);

        }

        return $this;
    }

    public function downloadAndSaveProducts()
    {
        try {

            $this->original_categories_uuid = array_column($this->getOriginalCategories(), 'supplier_uuid');

            $categories = $this->getOriginalCategories();

            foreach ($categories as $key => $category) {

                if ($category['parent_id'] == 1) {

                    \Log::info('load supplier category ' . $category['supplier_uuid'] . ', offset = ' . $this->getSettings()->products_offset . ', total = ' . $this->count_products_in_current_category . 'part compare ' . $category['supplier_uuid'] . 'with ' . $this->getSettings()->category_id_in_iteration);

                    if (!is_null($this->getSettings()->category_id_in_iteration) && $category['supplier_uuid'] !== $this->getSettings()->category_id_in_iteration) continue;

                    \Log::info('load supplier category ' . $category['supplier_uuid'] . ', offset = ' . $this->getSettings()->products_offset . ', total = ' . $this->count_products_in_current_category . ', part 1');

                    $this->changeCategoryIdIteration((string)$category['supplier_uuid']);

                    \Log::info('load supplier category ' . $category['supplier_uuid'] . ', offset = ' . $this->getSettings()->products_offset . ', total = ' . $this->count_products_in_current_category . ', part 2');

                    $offset = &$this->getSettings()->products_offset;

                    while (is_null($offset) || is_null($this->count_products_in_current_category) || $offset < $this->count_products_in_current_category) {

                        $this->downloadProducts($category['supplier_uuid'], $offset)
                            ->saveProducts()
                            ->clearProductsBuffer();
                    }

                } else {

                }

                $this->count_products_in_current_category = null;

                if (isset($categories[$key + 1])) $this->changeCategoryIdIteration($categories[$key + 1]['supplier_uuid'], true);

            }

        } catch (\Exception $exception) {
            \Log::error('Can\'t get products from brain supplier. ' . $exception->getMessage());

            throw \Illuminate\Validation\ValidationException::withMessages([
                [$exception->getMessage()],
            ]);

        }
    }

    public function downloadProducts(string $category_uuid, ?int $offset = 0)
    {
        try {

            $offset = !is_null($offset) ? $offset : 0;

            $response = json_decode((new Client())->get("http://api.brain.com.ua/products/$category_uuid/{$this::$auth_token}?offset=$offset")->getBody());

            if ($response->status === 1 && isset($response->result)) {

                $this->count_products_in_current_category = $response->result->count;

                if (count($response->result->list)) {

                    foreach ($response->result->list as $product) {

                        $quantity = 0;

                        foreach ($product->available as $available) {
                            $quantity += $available;
                        }

                        $this->products[] = [
                            'supplier_uuid' => (string)$product->productID,
                            'supplier_code' => (string)$this->getSupplierCode(),
                            'category_id' => (string)$product->categoryID,
                            'sku' => (string)$product->articul,
                            'name' => $product->name,
                            'price' => $product->price_uah,
                            'rrc_price' => (isset($product->recommendable_price) && $product->recommendable_price > 0) ? $product->recommendable_price : $product->retail_price_uah,
                            'quantity' => $quantity,
                            'description' => (string)$product->brief_description
                        ];
                    }

                }

            } else {
                throw new \Exception($response->error_message);
            }

        } catch (\Exception $exception) {
            \Log::error('Can\'t get categories from brain supplier. ' . $exception->getMessage());

            throw \Illuminate\Validation\ValidationException::withMessages([
                [$exception->getMessage()],
            ]);

        }

        return $this;
    }

    public function downloadProduct(SupplierProduct $supplier_product): array
    {
        $this->auth(config('suppliers.brain.login'), config('suppliers.brain.password'));

        $token = self::$auth_token;

        $info = [
            'attributes' => [],
            'images' => null,
            'description' => '',
            'vendor' => null
        ];

        try {

            $this->loadAttributes($supplier_product, $token, $info['attributes'], $info['vendor']);

            $this->loadImages($supplier_product, $token, $info['images']);

            $this->loadDescription($supplier_product, $token, $info['description']);


        } catch (\Exception $exception) {
            \Log::error('Can\'t get product from brain supplier. ' . $exception->getMessage());

            throw \Illuminate\Validation\ValidationException::withMessages([
                [$exception->getMessage()],
            ]);

        }

        return $info;

    }

    public function test()
    {
        try {

            $response = json_decode((new Client())->get("http://api.brain.com.ua/product/articul/10013/{$this::$auth_token}?lang=ua")->getBody());

            if ($response->status === 1 && isset($response->result)) {

                $product = $response->result;

                $quantity = 0;

                foreach ($product->available as $available) {
                    $quantity += $available;
                }

                $pro = [
                    'supplier_uuid' => (string)$product->productID,
                    'supplier_code' => (string)$this->getSupplierCode(),
                    'category_id' => (string)$product->categoryID,
                    'sku' => (string)$product->articul,
                    'name' => $product->name,
                    'price' => $product->price_uah,
                    'rrc_price' => (isset($product->recommendable_price) && $product->recommendable_price > 0) ? $product->recommendable_price : $product->retail_price_uah,
                    'quantity' => $quantity,
                    'description' => (string)$product->brief_description
                ];

                dd($pro);

            } else {
                throw new \Exception($response->error_message);
            }

        } catch (\Exception $exception) {
            \Log::error('Can\'t get product from brain supplier. ' . $exception->getMessage());

            throw \Illuminate\Validation\ValidationException::withMessages([
                [$exception->getMessage()],
            ]);

        }
    }

    private function loadAttributes($supplier_product, $token, &$attributes, &$vendor)
    {
        $response = json_decode((new Client())->get("http://api.brain.com.ua/product_options/{$supplier_product->supplier_uuid}/$token?lang=ua")->getBody());

        $disallowed_attributes = self::getDisAllowedAttributes($supplier_product);

        if ($response->status === 1 && isset($response->result)) {

            foreach ($response->result as $option) {

                if (!array_key_exists($option->OptionName, $disallowed_attributes)) {

                    $attribute = HelperFunction::getAttributeOrCreate($option->OptionName);
                    $attribute_value = HelperFunction::getAttributeValueOrCreate($attribute, (string)($option->ValueName));

                    if(isset($attributes[$attribute->id])){
                        $attributes[$attribute->id]['values'][] = $attribute_value;
                    }else{
                        $attributes[$attribute->id] = [
                            'attribute' => $attribute,
                            'values' => [$attribute_value]
                        ];
                    }

                    if($option->OptionName === 'Виробник'){
                        $vendor = (string)($option->ValueName);
                    }

                }
            }

        } else {
            throw new \Exception($response->error_message);
        }
    }

    private function loadImages($supplier_product, $token, &$images)
    {
        $response = json_decode((new Client())->get("http://api.brain.com.ua/product_pictures/{$supplier_product->supplier_uuid}/$token?lang=ua")->getBody());

        if ($response->status === 1 && isset($response->result)) {

            foreach ($response->result as $img) {
                $images[] = $img->large_image;
            }

        } else {
            throw new \Exception($response->error_message);
        }
    }

    private function loadDescription($supplier_product, $token, &$description)
    {
        $response = json_decode((new Client())->get("http://api.brain.com.ua/product/{$supplier_product->supplier_uuid}/$token?lang=ua")->getBody());

        if ($response->status === 1 && isset($response->result)) {

            $description = $response->result->description ?: $response->result->brief_description;

        } else {
            throw new \Exception($response->error_message);
        }
    }
}
