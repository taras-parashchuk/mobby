<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 2019-12-30
 * Time: 00:25
 */

namespace App\Helpers\Excel\Export;

use App\Models\Category;
use App\Models\Language;
use App\Models\Setting;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;


class Categories implements FromArray, WithTitle, WithHeadings
{

    private $configuration;

    public function __construct($configuration)
    {
        $this->configuration = $configuration->settings;
    }

    public function headings(): array
    {
        return [
            'category_id',
            'name',
            '',
            'parent_id'
        ];
    }

    public function array(): array
    {
        $categories = [];

        Category::all()->each(function($category) use (&$categories){
            $categories[] = [
                0 => $category->extra_1 ?? $category->id,
                1 => $category->translate->name,
                2 => '',
                3 => (string)$category->parent_id
            ];

        });

        return $categories;
    }

    public function title(): string
    {
        return 'Export Groups Sheet';
    }
}