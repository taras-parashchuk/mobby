<?php

namespace App\Listeners\Syncs\Moysklad;

use App\Events\UpdateUserEvent;
use App\Jobs\Syncs\Moysklad\UpdateUserJob;
use App\Models\Syncs\ExternalApi;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateUserListener
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
     * @param  UpdateUserEvent  $event
     * @return void
     */
    public function handle(UpdateUserEvent $event)
    {
        $moysklad = ExternalApi::where('status', true)
            ->where('code', config('syncs.moysklad.externalCode'))->first();

        if($moysklad && isset($moysklad->settings[config('syncs.moysklad.dataTypes.user')]['automaticMode']) && $moysklad->settings[config('syncs.moysklad.dataTypes.user')]['automaticMode']){
            dispatch(new UpdateUserJob($event->user));
        }
    }
}
