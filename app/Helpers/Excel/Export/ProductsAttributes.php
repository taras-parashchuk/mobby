<?php

namespace App\Helpers\Excel\Export;

use App\Models\AttributeToProduct;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductsAttributes implements FromArray, WithHeadings, WithTitle
{

    public function title(): string
    {
        return 'Products characteristics';
    }

    public function headings(): array
    {
        return [
            'product_id',
            'attribute',
            'main',
            'values'
        ];
    }

    public function array(): array
    {
        return AttributeToProduct::whereHas('product', function($q){
            $q->simple();
        })->with('values', 'product', 'attribute')->get()->map(function ($attr_to_product){
            return [
                $attr_to_product->product->extra_1,
                $attr_to_product->attribute->translate->name,
                (string)($attr_to_product->main ?: '0'),
                implode(';', $attr_to_product->values->map(function ($value){
                    return $value->translate->value;
                })->toArray())
            ];
        })->toArray();
    }
}