<?php


namespace App\Listeners\Syncs\Moysklad;

use App\Events\NewOrderEvent;
use App\Jobs\Syncs\Moysklad\UploadOrderJob;
use App\Models\Syncs\ExternalApi;
use App\Models\UserExternalSource;
use App\Services\Moysklad\CounterpartyService;
use App\Services\Moysklad\OrderService;
use Illuminate\Support\Facades\Auth;

class NewOrderCreateMoyskladListener
{
    /**
     * Handle the event.
     *
     * @param NewOrderEvent $event
     * @return void
     */
    public function handle(NewOrderEvent $event)
    {

        $moysklad = ExternalApi::where('status', true)
            ->where('code', config('syncs.moysklad.externalCode'))->first();

        if($moysklad && isset($moysklad->settings[config('syncs.moysklad.dataTypes.order')]['automaticMode']) && $moysklad->settings[config('syncs.moysklad.dataTypes.order')]['automaticMode']){
            dispatch(new UploadOrderJob($event->order->id));
        }

    }
}
