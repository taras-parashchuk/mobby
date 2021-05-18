<?php

namespace App\Widgets;

use App\Helpers\HelperFunction;
use App\Models\Category;
use Arrilot\Widgets\AbstractWidget;
use Cache;

class Menu extends AbstractWidget
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

        if ($this->config['type'] === 'horizontal') {
            $categories_list = $this->getCategoriesList($this->config['type']);
        } else {
            $categories_list = collect();
        }

        if ($categories_list->count() || $this->config['type'] === 'vertical') {
            return [
                'view' => 'widgets.menu',
                'data' => [
                    'config' => $this->config,
                    'is_home' => (\Route::currentRouteName() === 'home'),
                    'menu_categories' => $categories_list
                ]
            ];
        }
    }

    public static function getCategoriesList($type = 'vertical')
    {

        if ($type === 'vertical') {
            $categories = Cache::rememberForever( "depth.categories-" . app()->getLocale(), function () {

                $categories = Category::enabled()
                    ->withCount(['descendants' => function ($q) {
                        $q->enabled();
                    }])
                    ->with(['translates' => function ($q) {
                        $q->select('category_id', 'locale', 'name');
                    }])
                    //    ->select('depth','descendants_count', 'id', 'slug', '_lft', '_rgt', 'parent_id')
                    ->withDepth()
                    ->orderBy('sort_order', 'ASC')
                    ->get();

                $categories->each->append('translate');

                return $categories;

            });

            $result = collect();

            $categories_lvl_0 = $categories->where('depth', 0);

            if ($categories_lvl_0->count()) {

                $categories_lvl_1 = $categories->where('depth', 1);

                $result->push($categories_lvl_0);

                if ($categories_lvl_1->count()) {
                    $categories_lvl_2 = $categories->where('depth', 2);

                    $result->push($categories_lvl_1);

                    if ($categories_lvl_2->count()) {
                        $categories_lvl_3 = $categories->where('depth', 3);

                        $result->push($categories_lvl_2);

                        if ($categories_lvl_3->count()) {
                            $result->push($categories_lvl_3);
                        }
                    }
                }
            }

        } elseif ($type === 'horizontal') {
            $result = Category::enabled()
                ->with(['translates' => function ($q) {
                    $q->select('category_id', 'name', 'locale');
                }])
                ->orderBy('sort_order')
                ->select('id', 'slug', 'parent_id', '_lft', '_rgt')
                ->withDepth()
                ->having('depth', '<', 3)
                ->get();
        }

        return $result;

    }
}
