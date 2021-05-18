<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SupplierSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $supplier;

    public $tries = 100000;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($supplier_class)
    {
        //
        $this->supplier = $supplier_class;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //(new $this->supplier())->sync();
    }

    public function retryUntil()
    {
        //return now()->addDay();
    }

}
