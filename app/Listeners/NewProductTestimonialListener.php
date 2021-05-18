<?php

namespace App\Listeners;

use App\Events\NewTestimonialEvent;
use App\Mail\NewTestimonial;
use App\Models\Setting;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NewProductTestimonialListener
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
     * @param  NewTestimonialEvent  $event
     * @return void
     */
    public function handle(NewTestimonialEvent $event)
    {
        //

        if($event->for_product) Mail::to(Setting::get('sender_email'))->queue((new NewTestimonial())->onQueue('email'));
    }
}
