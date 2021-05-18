<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SyncConfigurationRequest;
use App\Models\Sync;
use App\Models\SyncConfiguration;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SyncConfigurationController extends Controller
{
    //
    public function index(Request $request)
    {
        return SyncConfiguration::where('type', $request->input('type'))->get();
    }

    public function store(SyncConfigurationRequest $request)
    {
        $configuration = new SyncConfiguration();

        $configuration->name = $request->input('name');
        $configuration->type = $request->input('type');
        $configuration->settings = $request->input('settings');

        $configuration->save();

        return response()->json([
            'id' => $configuration->id,
            'text' => trans('form.result.success-created')
        ]);

    }

    public function update(SyncConfigurationRequest $request, $id)
    {
        $configuration = SyncConfiguration::findOrFail($id);

        $configuration->name = $request->input('name');
        $configuration->settings = $request->input('settings');

        $configuration->update();

        return response()->json([
            'id' => $configuration->id,
            'text' => trans('form.result.success-updated')
        ]);

    }

    public function destroy($id)
    {
        $validator = \Validator::make([], []);

        $validator->after(function ($validator) use ($id) {
            if (Sync::where('configuration_id', $id)->where(function ($q) {
                $q->where('manually', false)->orWhere(function($q){
                    $q->where('finished', false)->where('fatal_error', false)->where('breaked', false);
                });
            })->count()) $validator->errors()->add('', trans('validation.custom.can-not-delete-configuration-if-used'));
        });

        $validator->validate();

        SyncConfiguration::findOrFail($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);

    }
}
