<?php

namespace App\Http\Controllers\Admin\Syncs\Moysklad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Syncs\Moysklad\ProductDownloadPricesQuantitiesRequest;
use App\Http\Requests\Admin\Syncs\Moysklad\ProductDownloadProductsRequest;
use App\Http\Requests\Admin\Syncs\Moysklad\ProductUploadProductsRequest;
use App\Jobs\Syncs\Moysklad\DownloadProductJob;
use App\Jobs\Syncs\Moysklad\UpdatePriceQuantityJob;
use App\Jobs\Syncs\Moysklad\UploadProductsJob;
use App\Models\Syncs\ExternalApi;
use App\Services\Moysklad\Service;

class ProductController extends Controller
{
    private $sync;
    private $externalCode;
    private $type;

    public function __construct()
    {
        $this->externalCode = config('syncs.moysklad.externalCode');
        $this->type = config('syncs.moysklad.dataTypes.product');

        Service::deleteLog($this->type);
        $this->sync = Service::createSync($this->type);
    }

    public function uploadProducts()
    {
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings['products']['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();

        $job = new UploadProductsJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = UploadProductsJob::class;
            $this->sync->save();
        }catch (QueryException $exception){

        }

        $this->sync->refresh();

        return response()->json([
            'text' => trans('form.result.success-file-added-to-queue'),
            'sync' => $this->sync->toArray(),
        ]);
    }

    public function downloadProducts(ProductDownloadProductsRequest $request)
    {
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings['products']['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();
        $job = new DownloadProductJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = DownloadProductJob::class;
            $this->sync->save();
        }catch (QueryException $exception){
	    \Log::error($exception->getMessage());
        }

        $this->sync->refresh();

        return response()->json([
            'text' => trans('form.result.success-file-added-to-queue'),
            'sync' => $this->sync->toArray(),
        ]);
    }

    public function downloadPricesQuantities(ProductDownloadPricesQuantitiesRequest $request)
    {
        Service::deleteLog(config('syncs.moysklad.dataTypes.prices_quantities'));
        $this->sync->type = config('syncs.moysklad.dataTypes.prices_quantities');
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings[config('syncs.moysklad.dataTypes.prices_quantities')]['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();
        $job = new UpdatePriceQuantityJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = UpdatePriceQuantityJob::class;
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
