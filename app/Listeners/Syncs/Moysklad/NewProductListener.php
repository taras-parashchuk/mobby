<?php

namespace App\Listeners\Syncs\Moysklad;

use App\Events\NewProductEvent;
use App\Jobs\Syncs\Moysklad\UploadProductJob;
use App\Models\Syncs\ExternalApi;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewProductListener
{
    /**
     * Create the event listener.
     *∑
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(NewProductEvent $event)
    {
        $moysklad = ExternalApi::where('status', true)
            ->where('code', config('syncs.moysklad.externalCode'))->first();

        if($moysklad && isset($moysklad->settings[config('syncs.moysklad.dataTypes.product')]['automaticMode']) && $moysklad->settings[config('syncs.moysklad.dataTypes.product')]['automaticMode']){
            dispatch(new UploadProductJob($event->product->id));
        }

    }
}
