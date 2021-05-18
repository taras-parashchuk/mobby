<?php

namespace App\Jobs\Syncs\Moysklad;

use App\Helpers\AuthorizeTenant;
use App\Services\Moysklad;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdatePriceQuantityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $sync_id;

    private $hostname;

    public function __construct($sync_id)
    {
        $this->sync_id = $sync_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(Moysklad::class)->updatePriceQuantity($this->sync_id);
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addHours(2);
    }
}
