<?php

namespace App\Http\Controllers\Admin;

use App\Models\AttributeToCategory;
use App\Models\AttributeValue;
use App\Models\CategoryFilterAttribute;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FilterController extends Controller
{
    //
    public function refresh()
    {
        ini_set('memory_limit', -1);

        ini_set('max_execution_time', 0);

        $attribute_to_categories = collect();

        $data = Product::enabledAndDelivered()->whereHas('to_attributes')
            ->with('to_attributes.to_values', 'to_categories')
            ->get();

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

        AttributeToCategory::get()->each->delete();

        foreach ($unique as $attribute_to_category) {

            $model = new AttributeToCategory();

            $model->category_id = $attribute_to_category['category_id'];
            $model->attribute_id = $attribute_to_category['attribute_id'];
            $model->attribute_value_id = $attribute_to_category['attribute_value_id'];

            $model->save();

        }

        return response()->json([
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function categoryAttributes($category_id)
    {
        return CategoryFilterAttribute::with('attribute')
            ->where('category_id', $category_id)
            ->get();
    }
}
