<?php

namespace App\Services\Supplier;

use App\Models\SupplierCategory;
use App\Models\SupplierProduct;
use App\Models\Sync;
use DB;
use App\Services\Currency;

class SupplierBase
{
    protected static $login;
    protected static $password;

    protected $categories = [];

    protected $products = [];

    private $original_categories = [];

    private $settings;
    private $supplier_code;

    public function __construct($supplier_code)
    {
        $this->original_categories = SupplierCategory::where('supplier_code', $supplier_code)->select('id', 'supplier_uuid', 'parent_id')->get()->toArray();

        $this->supplier_code = $supplier_code;

        if (!$this->settings = json_decode(\Cache::get($this->settings_cache_file_name()))) {
            $this->initSettings();
        }

        //\Log::info($this->supplier_code . ' = ' . json_encode($this->settings));

    }

    protected function getSettings()
    {
        return $this->settings;
    }

    protected function removeSettings()
    {
        \Cache::delete($this->settings_cache_file_name());
    }

    protected function initSettings()
    {
        $this->settings = (object)[
            'iteration_type' => 'Categories',
            'products_offset' => null,
            'category_id_in_iteration' => null
        ];

        \Cache::set($this->settings_cache_file_name(), json_encode($this->settings));
    }

    protected function getSupplierCode(): string
    {
        return (string)$this->supplier_code;
    }

    protected function changeIterationType(?string $type)
    {
        $this->settings->iteration_type = $type;

        \Cache::set($this->settings_cache_file_name(), json_encode($this->settings));
    }

    protected function changeCategoryIdIteration(string $category_id_iteration, $has_clear = false)
    {
        $this->settings->category_id_in_iteration = $category_id_iteration;

        if($has_clear) $this->settings->products_offset = null;

        \Cache::set($this->settings_cache_file_name(), json_encode($this->settings));
    }

    private function changeProductsOffset(int $offset)
    {
        if (is_null($this->settings->products_offset)) {
            $this->settings->products_offset = $offset;
        } else {
            $this->settings->products_offset += $offset;
        }


        \Cache::set($this->settings_cache_file_name(), json_encode($this->settings));
    }

    public function saveCategories(): self
    {
        $new_records = [];

        $updating_records = [];

        foreach ($this->categories as $k => $category) {

            $is_updating = false;

            foreach ($this->original_categories as $original_category) {

                if ($original_category['supplier_uuid'] === $category['supplier_uuid']) {

                    $is_updating = true;

                    $category['id'] = $original_category['id'];

                    break;
                }
            }

            $is_updating ? $updating_records[] = $category : $new_records[] = $category;
        }

        $this->storeCategories($new_records);

        //$this->updateCategories($updating_records);// very slow operation


        return $this;
    }

    public function saveProducts(): self
    {
        foreach ($this->products as $product){

            if($product['name']){
                if($row = DB::table((new SupplierProduct())->getTable())->where('supplier_uuid', $product['supplier_uuid'])->first()){
                    try{
                        SupplierProduct::find($row->id)->update($product);
                    }catch (\Exception $exception){
                        \Log::error($exception->getMessage());
                    }
                }else{
                    try{
                        SupplierProduct::insert($product);
                    }catch (\Exception $exception){
                        \Log::error($exception->getMessage());
                    }
                }
            }
        }

        $this->changeProductsOffset(count($this->products));

        return $this;
    }

    public function save()
    {
        $this->saveCategories()->saveProducts();
    }

    private function storeCategories(array $categories): void
    {
        DB::table((new SupplierCategory)->getTable())->insert($categories);
    }

    private function updateCategories(array $categories): void
    {
        foreach ($categories as $updating_record) {
            DB::table((new SupplierCategory)->getTable())->where('id', $updating_record['id'])->update($updating_record);
        }
    }

    public function clearProductsBuffer(): void
    {
        $this->products = [];
    }

    protected function getOriginalCategories()
    {
        return $this->original_categories;
    }

    protected function settings_cache_file_name()
    {
        return "suppliers.$this->supplier_code.sync";
    }

    protected static function getDisAllowedAttributes(SupplierProduct $supplierProduct): array
    {
        $disallowed_attributes = [];

        $supplierProduct->category->attributes->each(function ($attribute) use (&$disallowed_attributes) {

            $attribute->translates->each(function ($translate) use (&$disallowed_attributes, $attribute) {
                $disallowed_attributes[$translate->name] = $attribute;
            });

        });

        return $disallowed_attributes;
    }

}