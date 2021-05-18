<?php

namespace App\Listeners\Syncs\Moysklad;

use App\Events\Syncs\Moysklad\UpdateOrderEvent;
use App\Models\Syncs\ExternalApi;
use App\Services\Moysklad\OrderService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateOrderListener
{

    /**
     * Handle the event.
     *
     * @param  UpdateOrderEvent  $event
     * @return void
     */
    public function handle(UpdateOrderEvent $event)
    {
        $moysklad = ExternalApi::where('status', true)
            ->where('code', config('syncs.moysklad.externalCode'))->first();

        if($moysklad && isset($moysklad->settings[config('syncs.moysklad.dataTypes.order')]['automaticMode']) && $moysklad->settings[config('syncs.moysklad.dataTypes.order')]['automaticMode']){
            OrderService::updateCustomerOrderPosition($event->order);
        }

    }
}
