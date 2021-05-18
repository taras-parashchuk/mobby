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

class UploadCategoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $category_id;

    private $hostname;

    public $tries = 1;

    public function __construct(int $category_id)
    {
        $this->category_id = $category_id;



    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        app(Moysklad::class)->uploadCategory($this->category_id);
    }
}
