<?php

namespace App\Listeners;

use App\Events\NewOrderEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyListener
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
     * @param  NewOrderEvent  $event
     * @return void
     */
    public function handle(NewOrderEvent $event)
    {
        //
    }
}
