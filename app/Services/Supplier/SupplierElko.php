<?php


namespace App\Services\Supplier;


use App\Helpers\HelperFunction;
use App\Models\SupplierProduct;
use App\Services\Currency;
use GuzzleHttp\Client;

class SupplierElko extends SupplierBase implements SupplierInterface
{

    private $original_categories_uuid = [];
    private $api_base = 'https://uaapi.elkogroup.com/v3.0';
    private $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoiNjAyMTI0NUAxIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiQXBpIiwiZXhwIjoxNjM0MjE3MzU2LCJpc3MiOiJodHRwczovL2tpZXYuZWxrb2dyb3VwLmNvbSIsImF1ZCI6Imh0dHBzOi8va2lldi5lbGtvZ3JvdXAuY29tIn0.YWBP_AsVy9ETDgBYfDlXHrZxnjTw9YZWFM-gDgylGkY';

    public function __construct()
    {
        self::$login = config('suppliers.elko.login');

        self::$password = config('suppliers.elko.password');

        parent::__construct('Elko');
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

    public function downloadCategories(): self
    {

        try {

            $response = (new Client())->request('GET', $this->api_base . '/Catalogs/CategoryList', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept' => 'application/json'
                ]
            ])->getBody();

            $response_json = json_decode($response);

            if (count($response_json)>0) {

                foreach ($response_json as $category) {
                    $this->categories[] = [
                        'supplier_code' => $this->getSupplierCode(),
                        'supplier_uuid' => trim((string)$category->code),
                        'name' => (string)$category->name
                    ];

                }

            } else {
                throw  new \Exception((string)$response_xml->Message);
        }


        } catch (\Exception $exception) {
                \Log::error('Can\'t get categories from elko supplier. ' . $exception->getMessage());

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
            
            ini_set('max_execution_time', 60);

            foreach ($categories as $key => $category) {
                
                if (!is_null($this->getSettings()->category_id_in_iteration) && $category['supplier_uuid'] !== $this->getSettings()->category_id_in_iteration) continue;
    

                $this->changeCategoryIdIteration((string)$category['supplier_uuid']);



                $this->downloadProducts($category['supplier_uuid']);
                
                if ($this->isProducts()) {
                        $this->saveProducts();
                }

                $this->clearProductsBuffer();

                if (isset($categories[$key + 1])) $this->changeCategoryIdIteration($categories[$key + 1]['supplier_uuid']);

            }

        } catch (\Exception $exception) {

            \Log::error('Can\'t get products from elko supplier. ' . $exception->getMessage());

            throw \Illuminate\Validation\ValidationException::withMessages([
                [$exception->getMessage()],
            ]);

        }
    }

    public function downloadProducts(string $supplier_category_uuid, ?int $offset = 0): self
    {
        try {

            $response = (new Client())->request('GET', $this->api_base . '/Catalogs/Products', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept' => 'application/json'
                ],
                'query' => [
                    'catalog' => $supplier_category_uuid,
                    'lang' => 'RU'
                ]
            ])->getBody();

            $response_json = json_decode($response);

            if (count($response_json)>0) {

                foreach ($response_json as $product) {

                    //if (!in_array((string)$product->catalog, $this->original_categories_uuid)) continue;

                    $this->products[] = [
                        'supplier_uuid' => (string)$product->id,
                        'supplier_code' => (string)$this->getSupplierCode(),
                        'category_id' => trim((string)$product->catalog),
                        'sku' => (string)$product->manufacturerCode,
                        'name' => ((string)$product->uAproductName ?: (string)$product->name),
                        'price' => currency((string)($product->discountPrice ?: $product->price), (string)$product->currency, 'UAH', false),
                        'rrc_price' => currency((string)$product->rrp, (string)$product->currency, 'UAH', false),
                        'quantity' => preg_replace("/[^0-9]/", '', (string)$product->quantity),
                        'description' => (string)$product->fullDsc,
                    ];
                }

            }   else {
                    $this->products[] = [];
                    return $this;
                    //throw  new \Exception((string)$response_xml->Message);
                }

        } catch (\Exception $exception) {

            \Log::error('Can\'t get products from elko supplier. ' . $exception->getMessage());

            throw \Illuminate\Validation\ValidationException::withMessages([
                [$exception->getMessage()],
            ]);

        }

        return $this;
    }

    public function downloadProduct(SupplierProduct $supplier_product): array
    {

        $info = [
            'attributes' => [],
            'images' => null,
            'description' => '',
            'vendor' => null
        ];

        try {

            $this->loadDescription($supplier_product, $info['description']);
            
            $this->loadImages($supplier_product, $info['images']);

            $this->loadAttributes($supplier_product, $info);

            $this->loadVendor($supplier_product, $info['vendor']);

        } catch (\Exception $exception) {
            \Log::error("Can\'t get product from elko supplier. UUID = $supplier_product->supplier_uuid. " . $exception->getMessage() . $exception->getTraceAsString());

            throw \Illuminate\Validation\ValidationException::withMessages([
                [$exception->getMessage()],
            ]);

        }
        
        return $info;

    }


    private function Auth(): string 
    {

        if ($this->api_key != '') //we have static key
            return 0;

        try {
            $response = (new Client())->request('POST', $this->api_base . '/Token' , [
                'json' => [
                    'username' => self::$login,
                    'password' => self::$password
                ]
            ])->getBody();
            $this->api_key = $response;
        }
        catch (\Exception $exception) {
            \Log::error("Can\'t get API KEY." . $exception->getMessage() . $exception->getTraceAsString());

            throw \Illuminate\Validation\ValidationException::withMessages([
                [$exception->getMessage()],
            ]);
        }
        return 0;
    }

    public function isProducts() {
        if (empty($this->products[0])) {
            return false;
        }
        
        return true;
    }

    private function loadDescription($supplier_product, &$description)
    {
        $response = json_decode((new Client())->request('GET', $this->api_base . '/Catalogs/Products/' . $supplier_product->supplier_uuid . '/Description', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept' => 'application/json'
            ]
        ] )->getBody());

        foreach ($response as $attribute) {

            if ($attribute->criteria == 'Description') 
                $description = $attribute->value;
            
        }

    }

    private function loadImages($supplier_product, &$images)
    {
        $response = json_decode((new Client())->request('GET', $this->api_base . '/Catalogs/Products/' . $supplier_product->supplier_uuid . '/Description', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept' => 'application/json'
            ]
        ] )->getBody());

        foreach ($response as $attribute) {

            if ($attribute->criteria == 'Image') 
                $images[] = (string)$attribute->value;
            
        }

    }

    private function loadAttributes($supplier_product, &$attributes)
    {
        $response = json_decode((new Client())->request('GET', $this->api_base . '/Catalogs/Products/' . $supplier_product->supplier_uuid . '/Description', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept' => 'application/json'
            ]
        ] )->getBody());

        $disallowed_attributes = self::getDisAllowedAttributes($supplier_product);

        foreach ($response as $attribute_item) {

            if (!array_key_exists($attribute_item->criteria, $disallowed_attributes)) {

                $attribute = HelperFunction::getAttributeOrCreate($attribute_item->criteria);
                $attribute_value = HelperFunction::getAttributeValueOrCreate($attribute, (string)$attribute_item->value);

                if(isset($attributes['attributes'][$attribute->id])){
                    $attributes['attributes'][$attribute->id]['values'][] = $attribute_value;
                }else{
                    $attributes['attributes'][$attribute->id] = [
                        'attribute' => $attribute,
                        'values' => [$attribute_value]
                    ];
                }
    
            }
            
        }

    }

    private function loadVendor($supplier_product, &$vendor) {

        $response = json_decode((new Client())->request('GET', $this->api_base . '/Catalogs/Products/', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept' => 'application/json'
            ],
            'query' => [
                'elkoCode' => $supplier_product->supplier_uuid
            ]
        ] )->getBody());

        $vendor = $response[0]->vendorName;

    }
}