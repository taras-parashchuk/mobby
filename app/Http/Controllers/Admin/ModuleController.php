<?php

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ModuleTemplate;

class ModuleController extends Controller
{
    //
    public function index(Request $request)
    {
        if($request->has('autocomplete')){
            return Module::all();
        }else{
            return ModuleTemplate::with('modules')->get();
        }
    }

    public function destroy($id)
    {
        Module::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
          'template_module_id' => ['exists:module_templates,id'],
          'status' => ['boolean'],
          'name' => ['between:4,100']
        ]);

        $module_template = ModuleTemplate::find($request->input('template_module_id'));

        $module = new Module();

        $module->status = $request->input('status');
        $module->name = $request->input('name');
        $module->setting = json_encode($request->input('decoded_setting'), JSON_UNESCAPED_UNICODE);

        $module_template->modules()->save($module);

        return response()->json([
            'id' => $module->id,
            'text' => trans('form.result.success-created')
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id' => ['exists:modules'],
            'template_module_id' => ['exists:module_templates,id'],
            'status' => ['boolean'],
            'name' => ['between:4,100']
        ]);

        $module = Module::find($id);

        $module->status = $request->input('status');
        $module->name = $request->input('name');
        $module->setting = json_encode($request->input('decoded_setting'), JSON_UNESCAPED_UNICODE);

        $module->update();

        return response()->json([
            'id' => $module->id,
            'text' => trans('form.result.success-updated')
        ]);
    }
}
