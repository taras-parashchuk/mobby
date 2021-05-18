<?php

namespace App\Http\Controllers\Admin\Syncs\Moysklad;

use App\Http\Requests\Admin\Syncs\Moysklad\CategoryDownloadCategoriesRequest;
use App\Http\Requests\Admin\Syncs\Moysklad\CategoryUploadCategoriesRequest;
use App\Jobs\Syncs\Moysklad\DownloadCategoryJob;
use App\Jobs\Syncs\Moysklad\UploadCategoriesJob;
use App\Models\Syncs\ExternalApi;
use App\Http\Controllers\Controller;
use App\Services\Moysklad\Service;

class CategoryController extends Controller
{
    private $sync;
    private $externalCode;
    private $type;

    public function __construct()
    {
        $this->externalCode = config('syncs.moysklad.externalCode');
        $this->type = config('syncs.moysklad.dataTypes.category');

        Service::deleteLog($this->type);
        $this->sync = Service::createSync($this->type);
    }

    public function uploadCategories(CategoryUploadCategoriesRequest $request)
    {
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings['categories']['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();
        $job = new UploadCategoriesJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = UploadCategoriesJob::class;
            $this->sync->save();
        }catch (QueryException $exception){

        }

        $this->sync->refresh();

        return response()->json([
            'text' => trans('form.result.success-file-added-to-queue'),
            'sync' => $this->sync->toArray(),
        ]);
    }

    public function downloadCategories(CategoryDownloadCategoriesRequest $request)
    {
        $this->sync->save();

        $externalApi = ExternalApi::where('code', $this->externalCode)->first();
        $settings = $externalApi->settings;

        $settings['categories']['sync']['id'] = $this->sync->id;
        $externalApi->settings = json_encode($settings);

        $externalApi->save();

        $job = new DownloadCategoryJob($this->sync->id);

        $job_id = $this->dispatch($job);

        try{
            $this->sync->job_id = $job_id;
            $this->sync->job_name = DownloadCategoryJob::class;
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
