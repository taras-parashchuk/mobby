<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 2019-12-29
 * Time: 00:29
 */

namespace App\Helpers\Excel\Export;

use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;


class Products implements FromArray, WithHeadings, WithTitle
{

    private $headings;

    private $configuration;

    public function __construct($configuration)
    {

        $this->locale = Setting::get('site_language');

        $this->configuration = (array)$configuration->settings;

        foreach ($this->configuration as $name => $column) {
            if (is_numeric($column)) $this->headings[$column] = $name;
        }

        $headings_length = max(array_keys($this->headings));

        for ($i = 0; $i < $headings_length; $i++) {
            if (!isset($this->headings[$i])) {
                $this->headings[$i] = '';
            }
        }

        ksort($this->headings);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        $products = [];

        Product::simple()->with('to_attributes.attribute', 'to_attributes.values', 'price_unit', 'discounts', 'special', 'images')->get()->each(function ($product) use (&$products) {

            $discounts = $product->discounts;

            $images = collect();

            $images->push($product->image);

            $product->images->sortBy('sort_order')->each(function ($image) use (&$images) {
                $images->push($image->src);
            });

            $special = $product->special;

            $values = $this->makeCells($product, $discounts, $images, $special);

            $products[] = $values;

        });

        return $products;
    }

    private function makeCells($product, $discounts, $images, $special)
    {
        $values[$this->configuration['sku']] = $product->sku;
        $values[$this->configuration['minimum']] = $product->minimum;
        $values[$this->configuration['name']] = $product->translate->name;
        $values[$this->configuration['meta_keywords']] = $product->translate->meta_keywords;
        $values[$this->configuration['description']] = $product->translate->description;
        $values[$this->configuration['vendor_price']] = $product->vendor_price;
        $values[$this->configuration['currency_code']] = $product->currency_code;
        $values[$this->configuration['price_unit']] = $product->price_unit ? $product->price_unit->translate->name : '';
        $values[explode(',', $this->configuration['whosale'])[0]] = implode($this->configuration['whosale_separator'], $discounts->pluck('price')->toArray());
        $values[explode(',', $this->configuration['whosale'])[1]] = implode($this->configuration['whosale_separator'], $discounts->pluck('quantity')->toArray());
        $values[$this->configuration['images']] = implode(',', $images->toArray());
        $values[$this->configuration['quantity']] = $product->quantity;
        $values[$this->configuration['category_id']] = $product->category_id;
        $values[$this->configuration['extra_1']] = $product->extra_1;
        $values[$this->configuration['extra_2']] = $product->extra_2;
        //$values[$this->configuration['special']] = $special->price;

        $i = $this->configuration['attributes_pos_start'];

        if ($product->type === 3) {
            $product->variant_attribute_values->each(function ($variant_value) use (&$i, &$values) {

                $values[$i] = $variant_value->attribute->translate->name;
                $values[$i + 1] = $variant_value->attribute->translate->postfix;
                $values[$i + 2] = $variant_value->translate->value;

                $i += 3;
            });
        } else {
            $product->to_attributes->each(function ($to_attribute) use (&$i, &$values) {

                $values[$i] = $to_attribute->attribute->translate->name;
                $values[$i + 1] = $to_attribute->attribute->translate->postfix;
                $values[$i + 2] = implode(';', $to_attribute->values->pluck('translate.value')->toArray());

                $i += 3;
            });
        }


        $length = max(array_keys($values));

        for ($i = 0; $i < $length; $i++) {
            if (!isset($values[$i])) {
                $values[$i] = '';
            }
        }

        ksort($values);

        return $values ?? [];
    }

    public function title(): string
    {
        return 'Export Products Sheet';
    }

    public static function afterSheet(AfterSheet $event)
    {
        $event->sheet->getDelegate()->getStyle('C7:G7')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000');
    }
}