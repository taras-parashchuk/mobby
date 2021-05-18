<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ExportConfigurationSave;
use App\Models\ExportConfiguration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExportConfigurationController extends Controller
{
    //
    private $request;

    public function index()
    {
        return ExportConfiguration::all()->each->append('link');
    }

    public function store(ExportConfigurationSave $request)
    {
        $this->request = $request;

        $export_configuration = new ExportConfiguration;

        $this->save($export_configuration);

        return response()->json([
            'id' => $export_configuration->id,
            'text' => trans('form.result.success-created')
        ]);
    }

    public function update($id, ExportConfigurationSave $request)
    {
        $this->request  = $request;

        $export_configuration = ExportConfiguration::find($id);

        $this->save($export_configuration);

        return response()->json([
            'id' => $export_configuration->id,
            'text' => trans('form.result.success-updated')
        ]);
    }

    public function save(&$model)
    {
        $model->name = $this->request->input('name');
        $model->products_list_id = $this->request->input('products_list_id');
        $model->settings = $this->request->input('settings');

        $model->save();

    }

    public function destroy(Request $request, $id)
    {
        $request->merge([
            'id' => $id
        ])->validate([
            'id' => 'exists:export_configurations,id'
        ]);

        ExportConfiguration::find($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);

    }
}
