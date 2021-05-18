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

class UpdateCategoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $category;

    private $hostname;

    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($category)
    {
        $this->category = $category;


    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {



        app(Moysklad::class)->updateCategory($this->category);
    }
}
