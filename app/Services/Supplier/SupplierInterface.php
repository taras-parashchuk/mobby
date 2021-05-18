<?php

namespace App\Services\Supplier;


use App\Models\SupplierProduct;

interface SupplierInterface
{

    public function sync();

    public function downloadCategories();

    //public function downloadAndSaveProducts();

    public function downloadProducts(string $supplier_category_uuid);

    public function downloadProduct(SupplierProduct $supplier_product): array;


}