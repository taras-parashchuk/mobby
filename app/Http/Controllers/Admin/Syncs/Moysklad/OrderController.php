<?php

namespace App\Http\Controllers\Admin\Syncs\Moysklad;

use App\Http\Requests\Admin\Syncs\Moysklad\OrderDownloadOrdersRequest;
use App\Http\Requests\Admin\Syncs\Moysklad\OrderUploadOrdersRequest;
use App\Jobs\Syncs\Moysklad\DownloadOrderJob;
use App\Jobs\Syncs\Moysklad\UploadOrdersJob;
use App\Models\Syncs\ExternalApi;
use App\Http\Controllers\Controller;
use App\Services\Moysklad\Service;

class OrderController extends Controller
{
    private $sync;
    private $externalCode;
    private $type;

    public function __construct()
    {
        $this->externalCode = config('syncs.moysklad.externalCode');
        $this->type = config('syncs.moysklad.dataTypes.order');

        Service::deleteLog($this->type);
        $this->sync = Service::createSync($this->type);
    }

    public function uploadOrders(OrderUploadOrdersRequest $request)
    {
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings[$this->type]['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();

        $job = new UploadOrdersJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = UploadOrdersJob::class;
            $this->sync->save();
        }catch (\Exception $exception){

        }

        $this->sync->refresh();

        return response()->json([
            'text' => trans('form.result.success-file-added-to-queue'),
            'sync' => $this->sync->toArray(),
        ]);
    }

    public function downloadOrders(OrderDownloadOrdersRequest $request)
    {
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings[$this->type]['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();

        $job = new DownloadOrderJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = DownloadOrderJob::class;
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
