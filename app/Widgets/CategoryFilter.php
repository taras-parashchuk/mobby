<?php

namespace App\Widgets;

use App\Helpers\HelperFunction;
use App\Models\Product;
use Arrilot\Widgets\AbstractWidget;
use Route;
use Request;

class CategoryFilter extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->config['api_get_info_link'] = route('category.filters');
        $this->config['price_diagram'] = true;//from module setting
    }

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //
        if (Route::currentRouteName() === 'category' && $category_id = request()->route()->parameter('id')) {
            if (Product::enabled()->whereHas('to_categories', function ($query) use ($category_id) {
                $query->select('category_id');
                $query->where('category_id', $category_id);
            })->count()) {

                return [
                    'view' => 'widgets.category_filter',
                    'data' => [
                        'config' => $this->config
                    ]
                ];
            };
        }
    }
}
