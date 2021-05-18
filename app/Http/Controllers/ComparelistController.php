<?php

namespace App\Http\Controllers;

use App\Helpers\HelperFunction;
use App\Helpers\Image;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\ProductToCompareCategory;
use App\Models\ProductVariantAttributeValue;
use Illuminate\Http\Request;
use App\Models\Product;
use DB;
use Arr;

class ComparelistController extends Controller
{

    protected $comparelist = [];
    protected $request = null;

    public function init(Request $request)
    {
        $this->comparelist = session()->get('comparelist', collect());
        $this->request = $request;
    }

    public function index(Request $request)
    {
        $this->init($request);

        /*
        $categories = Category::enabled()->whereHas('to_compare_category', function($query){
            $query->whereIn('product_id', $this->comparelist);
        })->get();

        $categories->each(function($category){
            $category->count =
        });

        dd($categories);
        */

        $compare_info = ProductToCompareCategory::whereHas('category', function ($query) {
            return $query->enabled();
        })->with('category')->whereIn('product_id', $this->comparelist->flatten())
            ->select(DB::raw('count(product_id) as products_count, category_id'))
            ->groupBy('category_id')
            ->get();

        $compare_list_categories = $compare_info->map(function ($item) {
            return [
                'name' => $item->category->translate->name,
                'count' => $item->products_count,
                'href' => route('comparelist.category', ['category_id' => $item->category->id]),
            ];
        });

        $count_not_in_categories = count($this->getNoneCategoryProducts());

        $none_category = new Category();

        if ($count_not_in_categories) {

            $compare_list_categories->push([
                'name' => trans('pages.comparelist.none_category'),
                'count' => $count_not_in_categories,
                'href' => route('comparelist.category', ['category_id' => 0]),
            ]);
        }

        return view('pages.comparelist', compact('compare_list_categories', 'none_category'));
    }

    public function show(int $category_id)
    {
        if ($category_id) {
            $category = Category::enabled()->findOrFail($category_id);
        } else {
            $category = new Category();

            $category->id = 0;

            $category->name = trans('pages.comparelist.none_category');
        }

        return view('pages.compare_category', compact('category'));
    }

    public function categoryProducts(Request $request, int $category_id)
    {
        $this->init($request);

        if ($category_id) {
            $products = ProductToCompareCategory::where([
                'category_id' => $category_id
            ])->whereIn('product_id', $this->comparelist)
                ->with(['product' => function($q){
                    return $q->enabled();
                }])
                ->get();

            $products = $products->map(function($productToCompareCategory){
               return $productToCompareCategory->product;
            });

        } else {
            $products = Product::findMany($this->getNoneCategoryProducts());
        }

        $products->each->append('translate','priceFormat', 'pricesFormat', 'specialFormat', 'stock_title');

        $attributes = [];

        $products = $products->map(function ($product){

            $product->thumb = $product->resizeImage(150, 150);

            if ($product->type === 3) {
                $product->load('main_product.to_attributes.attribute', 'main_product.to_attributes.values');

                $product->to_attributes = $product->main_product->to_attributes;
            } else {
                $product->load('to_attributes.attribute', 'to_attributes.values');
            }

            $product->to_attributes = $product->to_attributes->map(function($to_attribute) use($product){
                if($to_attribute->has_variant){

                    $values = $to_attribute->values->filter(function($attribute_value) use ($product, $to_attribute){
                        return ProductVariantAttributeValue::where([
                            ['attribute_id', $to_attribute->attribute->id],
                            ['attribute_value_id', $attribute_value->id],
                            ['product_id', $product->id]
                        ])->count();
                    });

                    unset($to_attribute->values);

                    $to_attribute->values = $values;
                }

                return $to_attribute;
            });

            return $product;
        });

        $products->each(function ($product) use (&$attributes){
            $product->to_attributes->each(function ($to_attribute) use (&$attributes) {
                foreach ($to_attribute->values as $value){
                    $attributes[$to_attribute->attribute->slug]['id'] = $to_attribute->attribute->id;
                    $attributes[$to_attribute->attribute->slug]['name'] = $to_attribute->attribute->translate->name;
                    $attributes[$to_attribute->attribute->slug]['values'][] = $value->slug;
                }
            });
        });

        foreach ($attributes as $k => $attribute){

            $count_unique_values = count(array_unique($attribute['values']));

            if (count($attribute['values']) < 2 || $count_unique_values > 1) {
                $attributes[$k]['same'] = 0;
            } else {
                $attributes[$k]['same'] = 1;
            };

        }

        return response(compact('products', 'attributes'));
    }

    public function productsIds(Request $request)
    {
        $this->init($request);

        return $this->comparelist;
    }

    public function store(Request $request, int $product_id)
    {
        $this->init($request);

        if (Product::find($product_id) && !$this->has($product_id)) {

            $this->comparelist->push($product_id);

            $this->update();
        }

        return $this->comparelist;
    }

    public function destroy(Request $request, int $product_id)
    {
        $this->init($request);

        if (Product::find($product_id) && $this->has($product_id)) {

            $this->comparelist = $this->comparelist->filter(function($id) use ($product_id){
               return $id !== $product_id;
            });

            $this->update();
        }

        return $this->comparelist;
    }

    protected function has(int $product_id): bool
    {
        return $this->comparelist->contains($product_id);
    }

    protected function key(int $product_id): bool
    {
        return array_search($product_id, $this->comparelist);
    }

    protected function update()
    {
        session()->put('comparelist', $this->comparelist);
    }

    protected function getNoneCategoryProducts()
    {
        $founded_products = ProductToCompareCategory::whereIn('product_id', $this->comparelist)->get();

        $products = [];

        foreach ($this->comparelist->flatten() as $id) {

            $founded = $founded_products->firstWhere('product_id', $id);

            if (!$founded) {
                $products[] = $id;
            }
        }

        return $products;
    }
}
