<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\HelperFunction;
use App\Helpers\Image;
use App\Models\AttributeToProduct;
use App\Models\AttributeToSupplierCategory;
use App\Models\AttributeValueToProduct;
use App\Models\Category;
use App\Models\ImageToProduct;
use App\Models\Product;
use App\Models\ProductToCategory;
use App\Models\ProductToCompareCategory;
use App\Models\ProductTranslation;
use App\Models\Setting;
use App\Models\SupplierCategory;
use App\Models\SupplierProduct;
use App\Services\Supplier\SupplierBrain;
use App\Services\Supplier\SupplierCifroteh;
use App\Services\Supplier\SupplierElko;
use App\Services\Supplier\SupplierUg;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class SupplierController extends Controller
{
    //
    public function index(Request $request)
    {
        ini_set('max_execution_time', -1);

        $products_sku = $this->getProductsSku();
        $filters = (object)$request->input();

        $products_query = SupplierProduct::where(function ($query) use ($filters, $products_sku) {

            if (isset($filters->name_sku) && strlen($filters->name_sku)) {

                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters->name_sku . '%')
                        ->orWhere('sku', 'like', '%' . $filters->name_sku . '%');
                });
            }

            if (isset($filters->suppliers_categories) && count($filters->suppliers_categories)) {
                $query->whereHas('category', function ($q) use ($filters) {
                    $q->where('id', $filters->suppliers_categories);
                });
            }

            if (isset($filters->suppliers)) {
                $query->whereIn('supplier_code', $filters->suppliers);
            }

            if (isset($filters->quantity)) {

                $quantityObj = json_decode($filters->quantity);

                switch ($quantityObj->code) {
                    case '<':
                    case '<=':
                    case '>':
                    case '>=':
                    case '=':
                        if ($quantityObj->from !== null) {
                            $query
                                ->where(function ($q) use ($quantityObj) {
                                    $q->where('quantity', $quantityObj->code, $quantityObj->from);
                                });
                        }
                        break;
                    case 'from_to':
                        if ($quantityObj->from !== null && $quantityObj->to !== null) {
                            $query
                                ->where(function ($q) use ($quantityObj) {
                                    $q->where(function($q) use ($quantityObj){
                                        $q->where('quantity', '>=', $quantityObj->from);
                                        $q->where('quantity', '<=', $quantityObj->to);
                                    });
                                });
                        }
                        break;
                }
            }

            if (isset($filters->supplier_type)) {
                    switch ($filters->supplier_type){
                        case 0:
                            $query->where(function($query) use ($products_sku){
                                $query->whereNull('product_id')
                                    ->whereNotIn('sku', $products_sku);
                            });
                            break;
                        case 1:
                            $query->where(function($query) use ($products_sku){
                                $query->whereNull('product_id')
                                    ->whereIn('sku', $products_sku);
                            });
                            break;
                        case  2:
                            $query->whereNotNull('product_id');
                            break;
                    }
            }

        });

        foreach ($request->input('sort', []) as $sort) {

            $sort = json_decode($sort);

            $products_query->orderBy($sort->field, $sort->type);

        }

        $suppliers_products = $products_query->paginate($request->input('perPage', 1000));

        $suppliers_products->getCollection()->transform(function ($suppl_product) use ($products_sku) {

            if ($suppl_product->product_id) {
                $suppl_product->type = 2;
            } elseif ($suppl_product->sku && in_array($suppl_product->sku, $products_sku)) {
                $suppl_product->type = 1;
            } else {
                $suppl_product->type = 0;
            }

            $suppl_product->price = currency($suppl_product->price);

            return $suppl_product;

        });

        return $suppliers_products;
    }

    public function update(Request $request, $supplier_product_id)
    {
        $request->validate([
            'sku' => 'required'
        ]);

        $supplier_product = SupplierProduct::findOrFail($supplier_product_id);

        $supplier_product->sku = $request->input('sku');
        $supplier_product->update();

        if ($supplier_product->product_id) {
            $supplier_product->type = 2;
        } elseif ($supplier_product->sku && in_array($supplier_product->sku, $this->getProductsSku())) {
            $supplier_product->type = 1;
        } else {
            $supplier_product->type = 0;
        }

        return response()->json([
            'type' => $supplier_product->type,
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function getSuppliersCategories()
    {
        return SupplierCategory::whereHas('products')->with('to_attributes')->get();
    }

    public function createNewProduct(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:supplier_products,id'
        ]);

        try {
            $supplier_product = SupplierProduct::with('category.attributes.translates')->find($request->input('supplier_id'));
        } catch (ModelNotFoundException $exception) {

            throw \Illuminate\Validation\ValidationException::withMessages([
                'supplier' => [$exception->getMessage()],
            ]);

        }

        switch ($supplier_product->supplier_code) {
            case 'Elko':
                $extra_info = (new SupplierElko)->downloadProduct($supplier_product);
                break;
            case 'Brain':
                $extra_info = (new SupplierBrain)->downloadProduct($supplier_product);
                break;
            case 'Cifroteh':
                $extra_info = (new SupplierCifroteh())->downloadProduct($supplier_product);
                break;
            case 'UgContract':
        	$extra_info = (new SupplierUg())->downloadProduct($supplier_product);
        	break;
        }

        $product = new Product();

        $product->warehouse_price = $supplier_product->rrc_price;
        $product->sku = $supplier_product->sku;
        $product->category_id = $supplier_product->category ? $supplier_product->category->category_id : null;
        $product->slug = \Str::slug($supplier_product->name, '-', Setting::get('site_language'));
        $product->warehouse_quantity = 0;
        $product->quantity = $supplier_product->quantity;
        $product->vendor = $extra_info['vendor'];

        if($supplier_product->supplier_code === 'Brain'){
            $product->price_source = 'Brain';
        }

        $translate = new ProductTranslation();
        $translate->name = $supplier_product->name;
        $translate->description = $extra_info['description'];
        $translate->locale = Setting::get('site_language');

        $categories_list = Category::getGroupCategories($product->category_id);
	
	
        $to_compare_categories = $to_categories = [];

        foreach ($categories_list as $categories_list_id) {

            $p_t_compare_category = new ProductToCompareCategory();

            $p_t_compare_category->category_id = $categories_list_id;

            $p_t_category = new ProductToCategory();

            $p_t_category->category_id = $categories_list_id;

            $to_compare_categories[] = $p_t_compare_category;
            $to_categories[] = $p_t_category;

        }

        $to_images = [];

        $product_image_path = null;

        foreach ($extra_info['images'] as $k => $path) {
            if($k > 0){

                $eToImage = new ImageToProduct();

                $eToImage->src = $path;
                $eToImage->sort_order = $k;

                $to_images[] = $eToImage;

            }else{
                $product_image_path = $path;
            }
        }

        try {

            DB::transaction(function () use ($supplier_product, $product, $translate, $extra_info, $to_compare_categories, $to_categories, $to_images, $product_image_path) {

                $product->save();
                $product->translates()->save($translate);

                if($product_image_path){
                    $product->image = Image::downloadAndSaveByImagePath($product_image_path, $product);
                }

                foreach ($to_images as $to_image){

                    $to_image->src = Image::downloadAndSaveByImagePath($to_image->src, $product);

                    $product->images()->save($to_image);
                }

                $product_to_attributes = [];

                foreach ($extra_info['attributes'] as $k => $to_attribute) {

                    $attribute_to_product = new AttributeToProduct();

                    $attribute_to_product->attribute_id = $to_attribute['attribute']->id;
                    $attribute_to_product->main = $k < 6;
                    $attribute_to_product->has_variant = false;

                    foreach ($to_attribute['values'] as $value){

                        $attribute_value_to_product = new AttributeValueToProduct();
                        $attribute_value_to_product->attribute_value_id = $value->id;
                        $attribute_value_to_product->attribute_to_product_id = $attribute_to_product->id;

                        $attribute_to_product->to_values[] = $attribute_value_to_product;

                    }

                    $product_to_attributes[] = $attribute_to_product;

                }

                foreach ($product_to_attributes as $to_attribute) {

                    $product->to_attributes()->save($to_attribute);

                    $to_attribute->to_values()->saveMany($to_attribute->to_values);
                }

                $product->to_categories()->delete();
                $product->to_compare_categories()->delete();

                $product->to_categories()->saveMany($to_categories);
                $product->to_compare_categories()->saveMany($to_compare_categories);

                $supplier_product->product_id = $product->id;
                $supplier_product->update();

                $product->load('suppliers');

                $product->update();

            });

        } catch (\Throwable $exception) {
            \Log::error($exception->getMessage() . $exception->getTraceAsString());

            throw \Illuminate\Validation\ValidationException::withMessages([
                'supplier' => [$exception->getMessage()],
            ]);

        }

        return response()->json([
            'text' => trans('form.result.success-created')
        ]);

    }

    public function makeNewRelation(Request $request, $supplier_id)
    {
        $request->merge([
            'supplier_id' => $supplier_id
        ]);

        $request->validate([
            'supplier_id' => 'exists:supplier_products,id',
            'product_id' => 'nullable|exists:products,id'
        ]);

        $supplier_product = SupplierProduct::find($request->input('supplier_id'));

        $product_id = $request->input('product_id', Product::where('sku', $supplier_product->sku)->value('id'));

        if ($product_id) {

            $supplier_product->product_id = $product_id;

            $supplier_product->update();

        }

        return response()->json([
            'text' => trans('form.result.success-updated')
        ]);

    }

    public function updateSupplierCategory($supplier_category_id, Request $request)
    {
        $request->merge([
            'supplier_category_id' => $supplier_category_id
        ])->validate([
            'category_id' => 'nullable|exists:categories,id',
            'attributes.*' => 'exists:attributes,id'
        ]);

        $supplier_category = SupplierCategory::find($supplier_category_id);

        $attributes = [];

        foreach ($request->input('attributes') as $attribute_id) {

            $attribute_to_supplier_category = new AttributeToSupplierCategory();
            $attribute_to_supplier_category->attribute_id = $attribute_id;

            $attributes[] = $attribute_to_supplier_category;
        }

        try {
            DB::transaction(function () use ($supplier_category, $request, $attributes) {

                $supplier_category->category_id = $request->input('category_id');
                $supplier_category->update();

                $supplier_category->to_attributes()->delete();

                $supplier_category->to_attributes()->saveMany($attributes);


            });
        } catch (\Exception $exception) {

            throw \Illuminate\Validation\ValidationException::withMessages([
                'supplier' => [$exception->getMessage()],
            ]);

        }

        return response()->json([
            'text' => trans('form.result.success-updated')
        ]);

    }

    private function getProductsSku(): array
    {
        return Product::without('translates')->select('sku')->pluck('sku')->unique()->toArray();
    }

}
