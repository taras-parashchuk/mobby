<?php

namespace App\Http\Controllers\Admin\Syncs\Moysklad;

use App\Http\Requests\Admin\Syncs\Moysklad\MoyskladAuthorizationRequest;
use App\Http\Requests\Admin\Syncs\Moysklad\MoyskladAutomaticModeRequest;
use App\Http\Requests\Admin\Syncs\Moysklad\MoyskladUpdateRequest;
use App\Models\Sync;
use App\Models\Syncs\ExternalApi;
use App\Services\Moysklad\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp;
use Illuminate\Support\Facades\Crypt;

class MoyskladController extends Controller
{
    public $externalCode;
    private $acceptDataType = [];

    public function __construct()
    {
        $this->externalCode = config('syncs.moysklad.externalCode');
        $this->acceptDataType = config('syncs.moysklad.dataTypes');
    }

    /**
     * @param MoyskladAutomaticModeRequest $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function automaticMode(MoyskladAutomaticModeRequest $request)
    {
        if (array_search($request->data_type, $this->acceptDataType) === false) {
            return 'false';
        }

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings[$request->data_type]['automaticMode'] = $request->input('status');
        $externalApi->settings = json_encode($settings);

        $externalApi->save();

        return response()->json([
            'text' => trans('form.result.success-updated'),
        ]);
    }

    public function hourlyMode(MoyskladAutomaticModeRequest $request)
    {
        $time = Carbon::parse($request->input('time', '03:00'))->format('H:i');

        if (array_search($request->data_type, $this->acceptDataType) === false) {
            return 'false';
        }

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings[$request->data_type]['hourly_mode'] = [
            'status' => $request->input('status'),
            'time' => $time
        ];

        $externalApi->settings = json_encode($settings);

        $externalApi->save();

        return response()->json([
            'text' => trans('form.result.success-updated'),
        ]);
    }

    public function syncInfo(Request $request)
    {
        $selectColumns = [
            'id',
            'current',
            'total',
            'warnings_count',
            'fatal_error',
            'finished',
            'breaked',
            'paused',
            'stopped'
        ];

        $info = Sync::select($selectColumns)->findOrFail($request->sync_id);

        return response()->json(
            [
                'info' => $info
            ]
        );
    }

    public function stopSync(Request $request)
    {
        $sync = Sync::findOrFail($request->sync_id);

        $sync->stopped = 1;
        $sync->update();

        Service::break($sync->job_id);

        return response()->json([
            'text' => trans('form.result.success-sync-stop'),
            'sync' => $sync,
        ]);
    }

    public function pauseSync(Request $request)
    {
        $sync = Sync::findOrFail($request->sync_id);

        $sync->paused = 1;
        $sync->update();

        Service::break($sync->job_id);

        return response()->json([
            'text' => trans('form.result.success-sync-pause'),
            'sync' => $sync,
        ]);
    }

    public function resumeSync(Request $request)
    {
        $sync = Sync::findOrFail($request->sync_id);

        $sync->paused = 0;

        $job = new $sync->job_name($sync->id);
        $job_id = \Queue::push($job);
        $sync->job_id = $job_id;

        $sync->update();

        return response()->json([
            'text' => trans('form.result.success-sync-resume'),
            'sync' => $sync,
        ]);
    }


    public function authorization(MoyskladAuthorizationRequest $request)
    {
        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $externalApi->login = $request->input('login');
        $externalApi->password = $request->input('password');

        $externalApi->save();
    }

    public function show()
    {
        $data = ExternalApi::where('code', $this->externalCode)->first();

        return $data;
    }

    public function update(MoyskladUpdateRequest $request)
    {
        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $externalApi->status = $request->input('status');
        $externalApi->update();

        return response()->json([
            'text' => trans('form.result.success-updated'),
        ]);
    }

    public function downloadLog(Request $request)
    {
        $path =  "/files/" . config('syncs.moysklad.externalCode') . '/'.
            $request->data_type . '.log' ;
        $exists = \Storage::disk(config('lfm.disk'))->exists($path);

        if ($exists) {
            return \Storage::disk(config('lfm.disk'))->download($path);
        } else {
            return response()->json([
                'text' => trans('moy-sklad.errors.file_not_exist'),
            ]);
        }
    }
}
