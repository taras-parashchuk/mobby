<?php

namespace App\Console\Commands;

use App\Helpers\AuthorizeTenant;
use App\Jobs\Syncs\Moysklad\UpdateCurrenciesRateJob;
use App\Jobs\Syncs\Moysklad\UpdatePriceQuantityJob;
use App\Models\Sync;
use App\Models\Syncs\ExternalApi;
use App\Services\Moysklad\Service;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Console\Command;
use Hyn\Tenancy\Database\Connection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class HourlyModeJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hourlyModeJob:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run all jobs for which hourly mode is set true';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $externalApi = ExternalApi::where('code', config('syncs.moysklad.externalCode'))->first();
        $settings = $externalApi->settings;

        if (!is_null($settings)) {
            foreach ($settings as $key => $item) {
                if (array_key_exists('hourly_mode', $item) && $item['hourly_mode']['status'] == true && isset($item['hourly_mode']['time']) &&
                    $item['hourly_mode']['time'] > now()->subMinutes(5)->format('H:i') && $item['hourly_mode']['time'] < now()->addMinutes(5)->format('H:i')
                ){

                    $activeSync = Sync::where('type', $key)->where('data_type', config('syncs.moysklad.externalCode'))->where('finished', 0)->where(function ($q) {
                        $q->where('stopped', '!=', 1)
                            ->where('fatal_error','!=', 1);
                    })->first();

                    $acceptDataType = [
                        config('syncs.moysklad.dataTypes.prices_quantities'),
                        config('syncs.moysklad.dataTypes.rates'),
                    ];

                    if (is_null($activeSync) && !(array_search($key, $acceptDataType) === false)) {

                        Service::deleteLog($key);
                        $sync = Service::createSync($key);
                        $sync->save();
                        $sync->refresh();

                        $jobs = [
                            config('syncs.moysklad.dataTypes.prices_quantities') => new UpdatePriceQuantityJob($sync->id),
                            config('syncs.moysklad.dataTypes.rates') => new UpdateCurrenciesRateJob($sync->id),
                        ];

                        $job_id = Queue::push($jobs[$key]);

                        $sync->job_id = $job_id;
                        $sync->update();

                        $settings[$key]['sync']['id'] = $sync->id;

                    }
                }
            }

            $externalApi->settings = json_encode($settings);
            $externalApi->update();

        }
    }
}
