<?php

namespace App\Helpers\Excel\Export;

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

class ProductsDiscounts implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Products Wholesale Prices';
    }

    public function array(): array
    {
        return ProductDiscount::whereHas('product', function($q){
            $q->simple();
        })->with('customer_group', 'product')->get()->map(function($productDiscount){
            return [
                $productDiscount->product->extra_1,
                $productDiscount->customer_group->translate->name,
                $productDiscount->quantity,
                $productDiscount->price,
                $productDiscount->date_start,
                $productDiscount->date_end
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'product_id',
            'customer_group',
            'quantity',
            'price',
            'date_start',
            'date_end'
        ];
    }
}