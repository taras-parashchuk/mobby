<?php

namespace App\Listeners\Syncs\Moysklad;

use App\Events\NewCategoryEvent;
use App\Jobs\Syncs\Moysklad\UploadCategoryJob;
use App\Models\Syncs\ExternalApi;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCategoryListener
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
     * @param  object  $event
     * @return void
     */
    public function handle(NewCategoryEvent $event)
    {
        //
        $moysklad = ExternalApi::where('status', true)
            ->where('code', config('syncs.moysklad.externalCode'))->first();

        if($moysklad && isset($moysklad->settings[config('syncs.moysklad.dataTypes.category')]['automaticMode']) && $moysklad->settings[config('syncs.moysklad.dataTypes.category')]['automaticMode']){
            dispatch(new UploadCategoryJob($event->category->id));
        }
    }
}
