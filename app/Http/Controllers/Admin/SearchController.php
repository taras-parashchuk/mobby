<?php

namespace App\Http\Controllers\Admin;

use App\Models\CategoryTranslation;
use App\Models\ProductTranslation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    //
    public function index(Request $request)
    {
        $products = ProductTranslation::whereHas('product', function($q){
            $q->where('type', '<>', 2);
        })->with(['product' => function ($q) {
            $q->select(['id', 'sku', 'image', 'type', 'main_id']);
        }])
            ->where([
                ['name', 'like', "%{$request->input('phrase')}%"]
            ])
            ->select(['product_id', 'name'])->limit(10)->get()->map(function ($product) {

                return [
                    'id' => $product->product->type === 3 ? $product->product->main_id : $product->product->id,
                    'name' => $product->name,
                    'sku' => $product->product->sku,
                    'image' => $product->product->resizeImage(39, 39)
                ];
            })->toArray();

        if ($products) {

            $products = array_unique($products, SORT_REGULAR);

            $products = [
                'name' => trans('admin.menu.catalog.items.products'),
                'type' => 'product',
                'items' => $products
            ];
        }

        $categories = CategoryTranslation::where([
            ['name', 'like', "%{$request->input('phrase')}%"]
        ])->limit(10)->get(['category_id', 'name'])->map(function ($category) {
            return [
                'id' => $category->category_id,
                'name' => $category->name,
            ];
        })->toArray();

        if ($categories) {

            $categories = array_unique($categories, SORT_REGULAR);

            $categories = [
                'name' => trans('admin.menu.catalog.items.categories'),
                'type' => 'category',
                'items' => $categories
            ];
        }

        $results = array_filter([$products, $categories]);

        return $results;
    }
}
