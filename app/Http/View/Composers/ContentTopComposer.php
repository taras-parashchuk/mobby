<?php

namespace App\Http\View\Composers;

use App\Widgets\Menu;
use Illuminate\View\View;

use App\Models\Layout;
use App\Models\LayoutModule;
use Illuminate\Support\Facades\Route;


class ContentTopComposer
{

    protected $modules = [];

    public function __construct()
    {
        $routeName = Route::currentRouteName();

        if ($routeName) {

            $layoutRoute = Layout::getCachedLayouts()->where('route_name', $routeName)->first();

            if($layoutRoute){
                $layoutRoute->load(['modules' => function ($q) {
                    $q->select('modules.id', 'modules.setting', 'modules.template_module_id');
                    $q->where('modules.status', true);
                    $q->where('layout_modules.position', 'content_top');
                    $q->orderBy('layout_modules.sort_order', 'asc');
                }]);

                $this->modules = $layoutRoute->modules;
            }
        }
    }

    /**
     * Привязка данных к представлению.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {

        $views = [];

        foreach ($this->modules as $module) {

            $setting = (array)(json_decode($module->setting) ?? []);

            $class = 'App\Widgets\\' . $module->template->name;

            $setting['module_id'] = $module->id;

            $views[] = (new $class($setting))->run();
        }

        $views = array_filter($views);

        if (array_filter($views)) {
            $view->with(
                ['modules' => $views]
            );
        }
    }
}