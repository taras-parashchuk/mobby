<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Excel\Export\ExcelExport;
use App\Helpers\Excel\Export\Products;
use App\Helpers\FileManager;
use App\Http\Requests\Admin\ExcelAddToQueueRequest;
use App\Jobs\ImportExcelJob;
use App\Models\Setting;
use App\Models\Sync;
use App\Models\SyncConfiguration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Shared\File;
use Storage;
use \Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelController extends Controller
{
    public function addToQueue(ExcelAddToQueueRequest $request)
    {
        ini_set('max_execution_time', -1);

        $request->validateResolved();

        $configuration = SyncConfiguration::find($request->input('configuration_id'));


        $sync = new Sync();
        $sync->data_type = 'excel';
        $sync->manually = true;
        $sync->configuration_id = $configuration->id;

        $sync->save();

        \Storage::disk('uploads')->putFileAs("files/sync/excel", $request->file('file'), "manually.excel");

        $job = (new ImportExcelJob($sync->id, "files/sync/excel/manually.excel") );//->onConnection('sync');

        $job_id = $this->dispatch($job);

        $sync->job_id = $job_id;

        $sync->save();

        $sync->refresh();


        return response()->json([
            'text' => trans('form.result.success-file-added-to-queue'),
            'queue' => $sync
        ]);

    }

    public function export(Request $request)
    {

        $request->validate([
            'configuration_id' => 'required|exists:sync_configurations,id'
        ]);

        return \Excel::download(new ExcelExport(SyncConfiguration::find($request->input('configuration_id'))), 'export.xlsx');
    }

    public function getQueueStatus()
    {

        ob_start();

        return response()->stream(function(){
            while(true){

                $syncJob = Sync::where('data_type', 'excel')
                    ->where('manually', true)
                    ->orderBy('id', 'desc')
                    ->first();

                if($syncJob) $syncJob = $syncJob->toArray();

                echo 'data:' . json_encode($syncJob ?? []) . "\n\n";
                echo 'id: ' . rand(0, 1000) . "\n\n";

                ob_flush();
                flush();
                usleep(1000000);
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no'
        ]);

    }

    public function breakManual($id)
    {
        $syncJob = Sync::findOrFail($id);

        $syncJob->breaked = true;
        $syncJob->update();

        return response()->json([
            'text' => trans('form.result.success-breaked-import'),
            'queue' => null,
        ]);

    }
}
