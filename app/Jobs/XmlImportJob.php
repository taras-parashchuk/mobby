<?php

namespace App\Jobs;

use App\Helpers\Xml\XmlImport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class XmlImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 120;
    public $timeout = 120;

    private $sync_id;
    private $configuration;
    private $file_path;

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
        (new XmlImport($this->sync_id, $this->file_path))->execute();
    }

    public function failed(\Exception $exception)
    {
        // Send user notification of failure, etc...
    }
}
