<?php

namespace App\Http\Controllers\Admin\Syncs\Moysklad;

use App\Http\Requests\Admin\Syncs\Moysklad\CurrencyDownloadCurrenciesRequest;
use App\Http\Requests\Admin\Syncs\Moysklad\CurrencyUploadCurrenciesRequest;
use App\Http\Requests\Admin\Syncs\Moysklad\CurrencyDownloadCurrenciesRateRequest;
use App\Jobs\Syncs\Moysklad\DownloadCurrenciesJob;
use App\Jobs\Syncs\Moysklad\UpdateCurrenciesRateJob;
use App\Jobs\Syncs\Moysklad\UploadCurrenciesJob;
use App\Models\Syncs\ExternalApi;
use App\Http\Controllers\Controller;
use App\Services\Moysklad\Service;

class CurrencyController extends Controller
{
    private $sync;
    private $externalCode;

    public function __construct()
    {
        $this->externalCode = config('syncs.moysklad.externalCode');
        $this->sync = Service::createSync();
    }

    public function downloadCurrenciesRate(CurrencyDownloadCurrenciesRateRequest $request)
    {
        $type = config('syncs.moysklad.dataTypes.rates');
        Service::deleteLog($type);
        $this->sync->type = $type;
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings[config('syncs.moysklad.dataTypes.rates')]['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();
        $job = new UpdateCurrenciesRateJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = UpdateCurrenciesRateJob::class;
            $this->sync->save();
        }catch (QueryException $exception){

        }

        $this->sync->refresh();

        return response()->json([
            'text' => trans('form.result.success-file-added-to-queue'),
            'sync' => $this->sync->toArray(),
        ]);
    }

    public function uploadCurrencies(CurrencyUploadCurrenciesRequest $request)
    {
        $type = config('syncs.moysklad.dataTypes.currency');
        Service::deleteLog($type);
        $this->sync->type = $type;
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings[config('syncs.moysklad.dataTypes.currency')]['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();
        $job = new UploadCurrenciesJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = UploadCurrenciesJob::class;
            $this->sync->save();
        }catch (QueryException $exception){

        }

        $this->sync->refresh();

        return response()->json([
            'text' => trans('form.result.success-file-added-to-queue'),
            'sync' => $this->sync->toArray(),
        ]);
    }

    public function downloadCurrencies(CurrencyDownloadCurrenciesRequest $request)
    {
        $type = config('syncs.moysklad.dataTypes.currency');
        Service::deleteLog($type);
        $this->sync->type = $type;
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings[config('syncs.moysklad.dataTypes.currency')]['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();
        $job = new DownloadCurrenciesJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = DownloadCurrenciesJob::class;
            $this->sync->save();
        }catch (QueryException $exception){

        }

        $this->sync->refresh();

        return response()->json([
            'text' => trans('form.result.success-file-added-to-queue'),
            'sync' => $this->sync->toArray(),
        ]);
    }
}
