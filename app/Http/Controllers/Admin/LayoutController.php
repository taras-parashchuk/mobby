<?php

namespace App\Http\Controllers\Admin;

use App\Models\Layout;
use App\Models\LayoutModule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LayoutController extends Controller
{
    //
    public function index()
    {
        $layouts = Layout::with(['to_modules' => function($q){
            $q->orderBy('sort_order', 'asc');
        }, 'to_modules.module'])->get();

        $layouts->each(function($layout){

            $modules = [];

            $layout->to_modules->each(function($to_module) use ($layout, &$modules){

                $module = $to_module->module;

                $module->position = $to_module->position;

                $modules[] = $module;
            });

            $layout->modules = $modules;

        });

        return $layouts;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id' => ['exists:layouts,id']
        ]);

        $layout = Layout::find($id);

        $layout->to_modules()->delete();

        $to_modules = [];

        foreach ($request->input('modules') as $sort_order => $module){

            $layout_module = new LayoutModule();

            $layout_module->module_id = $module['id'];
            $layout_module->position = $module['position'];
            $layout_module->sort_order = $sort_order;

            $to_modules[] = $layout_module;

        }

        $layout->to_modules()->saveMany($to_modules);

        return response()->json([
            'id' => $layout->id,
            'text' => trans('form.result.success-updated')
        ]);
    }
}
