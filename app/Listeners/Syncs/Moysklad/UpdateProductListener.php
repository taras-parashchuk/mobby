<?php

namespace App\Listeners\Syncs\Moysklad;

use App\Events\UpdateProductEvent;
use App\Jobs\Syncs\Moysklad\UpdateProductJob;
use App\Models\Syncs\ExternalApi;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateProductListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UpdateProductEvent  $event
     * @return void
     */
    public function handle(UpdateProductEvent $event)
    {
        $moysklad = ExternalApi::where('status', true)
            ->where('code', config('syncs.moysklad.externalCode'))->first();

        if($moysklad && isset($moysklad->settings[config('syncs.moysklad.dataTypes.product')]['automaticMode']) && $moysklad->settings[config('syncs.moysklad.dataTypes.product')]['automaticMode']){
            dispatch(new UpdateProductJob($event->product));
        }
    }
}
