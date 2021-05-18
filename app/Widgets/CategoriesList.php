<?php

namespace App\Widgets;

use App\Helpers\HelperFunction;
use App\Models\Category;
use Arrilot\Widgets\AbstractWidget;
use Cache;
use Illuminate\Support\Facades\Route;

class CategoriesList extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //

        if (Route::currentRouteName() === 'category') {

            $categories = Category::enabled()
                ->select('id', 'slug', 'extra_1')
                ->with([
                    'translates' => function ($q) {
                        $q->select('category_id', 'name', 'locale');
                    }
                ])
                ->where('parent_id', Route::input('id'))
                ->get();

            return [
                'view' => 'widgets.child_categories',
                'data' => [
                    'child_categories' => $categories
                ]
            ];
        }
    }
}
