<?php

namespace App\Listeners;

use App\Events\NewOrderEvent;
use App\Mail\NewOrder;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Shipping;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;

class NewOrderNotifyMailListener
{

    public $products;

    public $totals;

    public $user_info;

    public $user_email;

    public $date_added;

    public $shipping_method;

    public $payment_method;

    public $address;

    public $comment;


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
     * @param  NewOrderEvent $event
     * @return void
     */
    public function handle(NewOrderEvent $event)
    {
        //

        if(!$event->order || $event->order->fast_order) return;

        $order = $event->order;
        $products = $order->products;
        $totals = $order->totals;

        $parts = [];

        if ($order->FullName) array_push($parts, $order->FullName);
        if ($order->telelephone) array_push($parts, $order->telelephone);

        array_push($parts, $order->email);

        $user_info = implode(' ', $parts);

        $date_added = $order->created_at;

        $Shipping = Shipping::where('code', $order->shipping_code)->select('name', 'decode_address_fields')->first();

        $parts = [];

        foreach ($order->decodeAddressInfo as $address_item) {
            $parts[] = $address_item['name'] . ': ' . $address_item['value'];
        }

        $address = implode(', ', $parts);

        if ($Shipping) {
            $shipping_method = json_decode($Shipping->name)->{app()->getLocale()};
        } else {
            $shipping_method = '';
        }

        $Payment = Payment::where('code', $order->payment_code)->value('name');

        if($Payment){
            $payment_method = json_decode($Payment)->{app()->getLocale()};
        }else{
            $payment_method = '';
        }

        $products->each(function ($product) {
            $product->price_total = currency($product->price * $product->quantity, $product->currency_code);
            $product->price = currency($product->price, $product->currency_code);
        });

        $totals->each(function ($total) {
            $total->value = currency_format($total->value);
        });

        if($order->email){
            Mail::to($order->email)->bcc(Setting::get('sender_email'))
                ->queue((new NewOrder($products, $totals, $user_info, $address, $shipping_method, $payment_method, $date_added, $order->comment))->onQueue('email'));
        }else{
            Mail::to(Setting::get('sender_email'))
                ->queue((new NewOrder($products, $totals, $user_info, $address, $shipping_method, $payment_method, $date_added, $order->comment))->onQueue('email'));
        }
    }
}
