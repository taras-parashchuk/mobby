<?php

namespace App\Listeners;

use App\Events\NewCallbackEvent;
use App\Mail\NewCallback;
use App\Models\Setting;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NewCallbackListener
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
     * @param  NewCallbackEvent  $event
     * @return void
     */
    public function handle(NewCallbackEvent $event)
    {
        //
        $callback = $event->callback;

        Mail::to(Setting::get('sender_email'))->sendNow((new NewCallback($callback)));

    }
}
