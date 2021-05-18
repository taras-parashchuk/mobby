<?php

namespace App\Helpers;

use App\Models\AttributeToProduct;
use App\Models\AttributeValue;
use App\Models\AttributeValueToProduct;
use App\Models\ProductToCategory;

class CatalogFilter extends QueryFilter
{
    protected function category_id($value)
    {
        $this->builder = $this->builder->whereIn('products.id', ProductToCategory::where('category_id', $value)->select('product_id'));
    }

    protected function price($values)
    {
        $values = array_filter($values);

        if(count($values) === 2){
            $values[0] = currency($values[0], currency()->getUserCurrency(), config('settings.main_currency'), false);
            $values[1] = currency($values[1], currency()->getUserCurrency(), config('settings.main_currency'), false);

            $this->builder = $this->builder->whereBetween('pp.price_min', $values);
        }
    }

    protected function attributes($attributes = [])
    {
        //$attributes = json_decode($attributes, true);

        foreach ($attributes as $attribute_alias => $attribute) {

            if (key_exists($attribute_alias, $this->attributes)) {

                $values = $attribute['values'];

                $this->builder = $this->builder->whereIn('products.id', AttributeToProduct::whereHas('values', function($query) use ($attribute_alias, $values){
                    $query
                        ->where('attribute_id', $this->attributes[$attribute_alias])
                        ->whereIn('slug', $values);
                })->get('product_id'));

            }
        }
    }
}