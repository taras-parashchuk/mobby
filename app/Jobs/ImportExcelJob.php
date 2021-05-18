<?php

namespace App\Jobs;

use App\Helpers\Excel\Imports\ExcelImport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ImportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $sync_id;

    private $file_path;

    public $tries = 250;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(int $syncId, string $file_path) {
        $this->sync_id = $syncId;
        $this->file_path = $file_path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Excel::import(new ExcelImport($this->sync_id),  $this->file_path, 'uploads');
    }

    public function failed(\Exception $exception)
    {
        // Send user notification of failure, etc...
        Log::error('text '.$exception->getMessage());
    }
}