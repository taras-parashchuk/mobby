<?php

namespace App\Listeners\Syncs\Moysklad;

use App\Events\UpdateCategoryEvent;
use App\Jobs\Syncs\Moysklad\UpdateCategoryJob;
use App\Models\Syncs\ExternalApi;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateCategoryListener
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
     * @param  UpdateCategoryEvent  $event
     * @return void
     */
    public function handle(UpdateCategoryEvent $event)
    {
        $moysklad = ExternalApi::where('status', true)
            ->where('code', config('syncs.moysklad.externalCode'))->first();

        if($moysklad && isset($moysklad->settings[config('syncs.moysklad.dataTypes.category')]['automaticMode']) && $moysklad->settings[config('syncs.moysklad.dataTypes.category')]['automaticMode']){
            dispatch(new UpdateCategoryJob($event->category));
        }
    }
}
