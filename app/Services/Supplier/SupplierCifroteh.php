<?php

namespace App\Services\Supplier;

use App\Helpers\HelperFunction;
use App\Models\Attribute;
use App\Models\SupplierCategory;
use App\Models\SupplierProduct;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\UriFactory;
use OAuth\ServiceFactory;


class SupplierCifroteh extends SupplierBase
{
    public $service;

    private $application_url = 'http://b2b.cifrotech.ua';

    private $limit = 10;

    public function __construct()
    {
        parent::__construct('Cifroteh');

        $uriFactory = new UriFactory();
        $storage = new CifrotehAuthStorage();

        $serviceFactory = new ServiceFactory();
        $serviceFactory->registerService('cifroteh', CifrotehAuth::class);

        $baseUri = $uriFactory->createFromAbsolute($this->application_url);

        $credentials = new Credentials(
            config('suppliers.cifroteh.consumer_key'),
            config('suppliers.cifroteh.consumer_secret'),
             'http://mobby.loc/'
        );

        $this->service = $serviceFactory->createService('cifroteh', $credentials, $storage, array(), $baseUri);
    }

    public function sync()
    {
        $this->downloadAndSaveProducts();
    }

    public function downloadAndSaveProducts()
    {
        $offset = &$this->getSettings()->products_offset;

        while($products = $this->getProducts($this->calculatePageFromOffset($offset))){

            $this->setProducts($products)->saveProducts()->clearProductsBuffer();

            $offset = &$this->getSettings()->products_offset;

        };

        $this->removeSettings();
    }

    private function getProducts($page)
    {
        try{
            $products = json_decode($this->service->request("/api/rest/getstock?limit={$this->limit}&page=$page", 'GET', null, array('Accept' => '*/*')), true);
        }catch (\Throwable $exception){
            \Log::critical($exception->getMessage());
            die();
        }

        if(count($products) < $this->limit){
            return null;
        } else {
            return $products;
        }
    }

    private function calculatePageFromOffset($offset)
    {
        return ($offset / $this->limit) + 1;
    }

    private function setProducts($products)
    {
        foreach ($products as $product_id => $product){

            $details = json_decode($this->service->request("/api/rest/products?sku={$product['sku']}", 'GET', null, array('Accept' => '*/*')), true);

            $supplier_category_name = $product['category_ids'][0] ?? null;

            if($supplier_category_name){
                $category_id  = SupplierCategory::firstOrCreate(
                    ['name' => $supplier_category_name],
                    [
                        'supplier_code' => $this->getSupplierCode(),
                        'supplier_uuid' => uniqid()
                    ]
                )->supplier_uuid;
            }else{
                $category_id = null;
            }

            $this->products[] = [
                'supplier_uuid' => (string)$product_id,
                'supplier_code' => (string)$this->getSupplierCode(),
                'category_id' => $category_id,
                'sku' => trim($details['kod_proizvoditelja'] ?? ''),
                'name' => $product['name'],
                'price' => $product['price'],
                'rrc_price' => $product['msrp_price'] ?? $product['price'],
                'quantity' => $this->calculateQuantity($product['stock_status_label']),
                'description' => (string)$details['description'],
                'extra' => $product['brand'] ?? null,
                'extra_2' => $product['sku'] ?? null
            ];

        }

        return $this;
    }

    public function calculateQuantity($stock_status_label)
    {
        switch ($stock_status_label){
            case 'Мало':
                return 5;
            case 'Свободно':
                return 100;
            default:
                return 0;
        }
    }

    public function downloadProduct( SupplierProduct $supplier_product )
    {
        try{
            $images = array_column(json_decode($this->service->request("/api/rest/products/{$supplier_product->supplier_uuid}/images", 'GET', null, array('Accept' => '*/*')), true), 'url');
        }catch (\Throwable $exception){
            $images = [];
        }

        $attributes = [];

        if($supplier_product->extra){

            $attribute = HelperFunction::getAttributeOrCreate('Производитель');
            $attribute_value = HelperFunction::getAttributeValueOrCreate($attribute, (string)($supplier_product->extra));

            $attributes[] = [
                'attribute' => $attribute,
                'values' => [$attribute_value]
            ];
        }

        return [
            'attributes' => $attributes,
            'images' => $images,
            'description' => $supplier_product->description,
            'vendor' => (string)$supplier_product->extra
        ];
    }

}