<?php

namespace App\Listeners;

use App\Events\NewOrderEvent;
use App\Mail\NewFastOrder;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;

class NewFastOrderNotifyMailListener
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
        if(!$event->order || !$event->order->fast_order) return;

        $user_telephone = $event->order->telephone;

        $products = $event->order->products;

        $products->each(function($product){
            $product->href = Product::find($product->product_id)->href;
        });

        $products->each->append('priceFormat', 'totalFormat');

        Mail::to(Setting::get('sender_email'))->queue((new NewFastOrder($user_telephone, $products))->onQueue('email'));
    }
}
