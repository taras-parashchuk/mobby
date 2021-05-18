<?php

namespace App\Http\Controllers\Admin\Syncs\Moysklad;

use App\Http\Requests\Admin\Syncs\Moysklad\CounterpartyDownloadCounterpartiesRequest;
use App\Http\Requests\Admin\Syncs\Moysklad\CounterpartyUploadCounterpartiesRequest;
use App\Jobs\Syncs\Moysklad\DownloadCounterpartyJob;
use App\Jobs\Syncs\Moysklad\UploadCounterpartiesJob;
use App\Jobs\Syncs\Moysklad\UploadCounterpartyJob;
use App\Models\Syncs\ExternalApi;
use App\Http\Controllers\Controller;
use App\Services\Moysklad\Service;

class CounterpartyController extends Controller
{
    private $sync;
    private $externalCode;
    private $type;

    public function __construct()
    {
        $this->externalCode = config('syncs.moysklad.externalCode');
        $this->type = config('syncs.moysklad.dataTypes.user');

        Service::deleteLog($this->type);
        $this->sync = Service::createSync($this->type);
    }

    public function uploadCounterparties(CounterpartyUploadCounterpartiesRequest $request)
    {
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings[$this->type]['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();

        $job = new UploadCounterpartiesJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = UploadCounterpartiesJob::class;
            $this->sync->save();
        }catch (\Exception $exception){

        }

        $this->sync->refresh();

        return response()->json([
            'text' => trans('form.result.success-file-added-to-queue'),
            'sync' => $this->sync->toArray(),
        ]);
    }

    public function downloadCounterparties(CounterpartyDownloadCounterpartiesRequest $request)
    {
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings[$this->type]['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();

        $job = new DownloadCounterpartyJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = DownloadCounterpartyJob::class;
            $this->sync->save();
        }catch (\Exception $exception){

        }

        $this->sync->refresh();

        return response()->json([
            'text' => trans('form.result.success-file-added-to-queue'),
            'sync' => $this->sync->toArray(),
        ]);
    }
}
