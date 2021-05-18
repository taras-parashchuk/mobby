<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 2020-01-03
 * Time: 20:48
 */

namespace App\Helpers\Excel\Export;

use App\Models\Product;
use App\Models\ProductSpecial;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductsSpecials implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Products Specials';
    }

    public function array(): array
    {
        return ProductSpecial::whereHas('product', function($q){
            $q->simple();
        })->with('customer_group', 'product')->get()->map(function($productSpecial){
           return [
               $productSpecial->product->extra_1,
               $productSpecial->customer_group->translate->name,
               $productSpecial->price,
               $productSpecial->date_start,
               $productSpecial->date_end
           ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'product_id',
            'customer_group',
            'price',
            'date_start',
            'date_end'
        ];
    }
}