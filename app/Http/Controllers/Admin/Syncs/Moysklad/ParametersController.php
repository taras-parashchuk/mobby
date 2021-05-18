<?php

namespace App\Http\Controllers\Admin\Syncs\Moysklad;

use App\Models\Syncs\ExternalApi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ParametersController extends Controller
{
    private $externalCode;

    public function __construct()
    {
        $this->externalCode = config('syncs.moysklad.externalCode');
    }

    public function setParameters(Request $request)
    {
        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings[$request->data_type]['update_parameters'][$request->action] = $request->all();
        $externalApi->settings = json_encode($settings);

        $externalApi->save();

        return response()->json([
            'text' => trans('form.result.success-updated'),
        ]);
    }
}
