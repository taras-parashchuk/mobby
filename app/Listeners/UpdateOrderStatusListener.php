<?php

namespace App\Listeners;

use App\Events\UpdateOrderStatusEvent;
use App\Mail\UpdateOrserStatus;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;

class UpdateOrderStatusListener
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
    public function handle(UpdateOrderStatusEvent $event)
    {
        //
        $order_history = $event->orderHistory;

        $order = $event->orderHistory->order;

        if($order->email) Mail::to($order->email)->queue((new UpdateOrserStatus($order->id, $order_history->comment, $order_history->status->translate->name))->onQueue('email'));

    }
}
