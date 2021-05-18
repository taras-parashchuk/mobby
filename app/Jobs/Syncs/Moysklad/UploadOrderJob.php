<?php

namespace App\Jobs\Syncs\Moysklad;

use App\Helpers\AuthorizeTenant;
use App\Services\Moysklad;
use Hyn\Tenancy\Environment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UploadOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $order_id;

    public $tries = 1;

    private $hostname;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $order_id)
    {
        $this->order_id = $order_id;


    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {



        app(Moysklad::class)->uploadOrder($this->order_id);
    }
}
