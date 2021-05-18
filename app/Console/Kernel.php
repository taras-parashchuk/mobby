<?php

namespace App\Console;

use App\Console\Commands\GenerateExportXml;
use App\Console\Commands\SeedDataNewTenant;
use App\Helpers\FileManager;
use App\Jobs\SupplierSync;
use App\Jobs\XmlImportJob;
use App\Models\Sync;
use App\Services\Supplier\SupplierBrain;
use App\Services\Supplier\SupplierCifroteh;
use App\Services\Supplier\SupplierElko;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        GenerateExportXml::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $time_now = Carbon::now()->format('H:i');

        $syncs = Sync::where(function ($q) use ($time_now) {

            $q->where(function ($q) use ($time_now) {
                $q->where('time_1', $time_now)->orWhere('time_2', $time_now);
            });

        })->where('status', true)->get()->toArray();

        foreach ($syncs as $sync) {

            if ($sync['data_type'] === 'xml') {

                $commandClass = XmlImportJob::class;

                $schedule->job(new $commandClass($sync['id'], "uploads/files/sync/xml/auto/{$sync['id']}.xml"))
                    ->dailyAt($time_now)
                    ->runInBackground()
                    ->withoutOverlapping();

            }
        }

        if($this->app->runningInConsole()){

            $this->scheduleGenerateExportXml($schedule);

            $this->scheduleSupplierSync($schedule);

            $schedule->command('hourlyModeJob:run')->everyMinute();

        }



    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    private function scheduleGenerateExportXml(Schedule $schedule)
    {
        $schedule->command(GenerateExportXml::class)
            ->withoutOverlapping()
            ->dailyAt(Carbon::createFromFormat('H:i', '04:00')->format('H:i'))
            ->runInBackground();

    }

    private function scheduleSupplierSync(Schedule $schedule)
    {
        $supplies = [
            SupplierElko::class,
            SupplierBrain::class,
            SupplierCifroteh::class
        ];

        foreach ($supplies as $supplier_class){
            $schedule->job(new SupplierSync($supplier_class))
                ->withoutOverlapping()
                ->twiceDaily(22, 12);
        }
    }
}
