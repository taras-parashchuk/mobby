<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\AttributeToCategory;
use App\Models\CategoryFilterAttribute;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryExcludedAttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $categoryExcludedAttribute = CategoryFilterAttribute::where('attribute_id', $request->input('filter_by_attribute_id'))->get();

        return response()->json(compact('categoryExcludedAttribute'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //

        $request->validate([
            'category_id' => ['exists:categories,id'],
            'attribute_id' => ['exists:attributes,id']
        ]);

        $categoryExcludedAttribute = new CategoryFilterAttribute();

        $categoryExcludedAttribute->category_id = $request->input('category_id');
        $categoryExcludedAttribute->attribute_id = $request->input('attribute_id');

        $categoryExcludedAttribute->save();

        AttributeToCategory::where([
            ['category_id', $categoryExcludedAttribute->category_id],
            ['attribute_id', $categoryExcludedAttribute->attribute_id]
        ])->delete();

        return response()->json([
            'id' => $categoryExcludedAttribute->id,
            'text' => trans('form.result.success-created')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $categoryExcludeAttribute = CategoryFilterAttribute::findOrFail($id);

        $attribute_id = $categoryExcludeAttribute->attribute_id;
        $category_id = $categoryExcludeAttribute->category_id;

        $attribute_to_categories = collect();

        //
        $categoryExcludeAttribute->delete();

        $data = Product::enabledAndDelivered()->whereHas('to_attributes')->with(['to_attributes' => function($q) use ($attribute_id){
            return $q->where('attribute_id', $attribute_id);
        }, 'to_attributes.to_values', 'to_categories' => function($q) use ($category_id){
            return $q->where('category_id', $category_id);
        }])->get();

        $data->each(function ($product) use (&$attribute_to_categories) {

            $product->to_attributes->each(function ($to_attribute) use (&$attribute_to_categories, $product) {
                $to_attribute->to_values->each(function ($to_value) use (&$attribute_to_categories, $product, $to_attribute) {
                    $product->to_categories->each(function ($to_category) use (&$attribute_to_categories, $to_attribute, $to_value) {
                        $attribute_to_categories->push([
                            'category_id' => $to_category->category_id,
                            'attribute_id' => $to_attribute->attribute_id,
                            'attribute_value_id' => $to_value->attribute_value_id,
                        ]);
                    });

                });
            });
        });

        $unique = $attribute_to_categories->unique(function ($item) {
            return $item['category_id'] . $item['attribute_id'] . $item['attribute_value_id'];
        });

        foreach ($unique as $attribute_to_category) {
            $model = new AttributeToCategory();

            $model->category_id = $attribute_to_category['category_id'];
            $model->attribute_id = $attribute_to_category['attribute_id'];
            $model->attribute_value_id = $attribute_to_category['attribute_value_id'];

            $model->save();
        }

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }
}
