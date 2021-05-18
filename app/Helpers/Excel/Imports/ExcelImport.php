<?php

namespace App\Helpers\Excel\Imports;

use App\Models\Setting;
use App\Models\Sync;
use App\Models\SyncConfiguration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class ExcelImport implements WithMultipleSheets, WithEvents
{

    private $sync_id;
    private static $sync_static;
    private $sync;
    private $configuration;


    private $locale;
    private $sheets;

    use RegistersEventListeners;

    public function __construct($sync_id)
    {
        $this->sync_id = $sync_id;

        try {
            $this->sync = Sync::findOrFail($sync_id);

            self::$sync_static = $this->sync;

        } catch (ModelNotFoundException $e) {
            $this->setFatalError("Sync with id $sync_id not found", true);
        }

        try {
            $this->configuration = SyncConfiguration::find($this->sync->configuration_id)->settings;
        } catch (\ErrorException $e) {
            $this->setFatalError(trans('validation.custom.configuration-not-exists'));
        }

        $this->locale = Setting::get('site_language');

        $sheets = [
            'Export Groups Sheet' => new ExcelImportCategories($this->sync, $this->configuration, $this->locale),
            'Export Products Sheet' => new ExcelImportProducts($this->sync, $this->configuration, $this->locale),
        ];

        if($this->sync->type){
            $stop = false;

            foreach ($sheets as $sheet_name => $sheet){
                if(!$stop && $sheet_name !== $this->sync->type){
                    unset($sheets[$sheet_name]);
                }else{
                    $stop = true;
                }
            }
        }

        $this->sheets = $sheets;
    }

    public function sheets(): array
    {
        return $this->sheets;
    }

    public static function afterImport(AfterImport $event)
    {
        //
        self::$sync_static->refresh();

        self::$sync_static->finished = true;

        self::$sync_static->update();
    }

    private function setFatalError($text, $notify_support = false)
    {
        if ($this->sync) {

            $this->sync->fatal_error = true;
            $this->sync->update();

            \Storage::disk('uploads')->append($this->sync->log_file_path, $text);

            $this->break();
        }

        if ($notify_support) $this->notifySupport($text);

        exit();
    }

    private function notifySupport($text)
    {
        \Log::error("SyncId:{$this->sync_id}, message: $text");
    }
}