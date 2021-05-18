<?php

namespace App\Listeners\Syncs\Moysklad;

use App\Jobs\Syncs\Moysklad\UploadCounterpartyJob;
use App\Models\Syncs\ExternalApi;
use App\Models\UserExternalSource;
use App\Services\Moysklad\CounterpartyService;
use Illuminate\Auth\Events\Registered;

class NewCounterpartyListener
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $moysklad = ExternalApi::where('status', true)
            ->where('code', config('syncs.moysklad.externalCode'))->first();

        if($moysklad && isset($moysklad->settings[config('syncs.moysklad.dataTypes.user')]['automaticMode']) && $moysklad->settings[config('syncs.moysklad.dataTypes.user')]['automaticMode']){
            dispatch(new UploadCounterpartyJob($event->user->id));
        }
    }
}
