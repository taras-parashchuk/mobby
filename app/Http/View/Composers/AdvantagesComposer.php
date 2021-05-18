<?php

namespace App\Http\View\Composers;

use App\Models\Payment;
use App\Models\Shipping;
use Illuminate\View\View;

class AdvantagesComposer
{


    public function __construct()
    {

    }

    /**
     * Привязка данных к представлению.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {

        $shippings = array_filter(Shipping::where('status', 1)->get('name')->map(function($shipping_names){
            $shipping_names_decoded =  json_decode($shipping_names->name, true);

            if(key_exists(app()->getLocale(), $shipping_names_decoded)){
                return $shipping_names_decoded[app()->getLocale()];
            }
        })->toArray());

        $payments = array_filter(Payment::where('status', 1)->get('name')->map(function($payment_names){
            $payment_names_decoded = json_decode($payment_names->name, true);

            if(key_exists(app()->getLocale(), $payment_names_decoded)){
                return $payment_names_decoded[app()->getLocale()];
            }
        })->toArray());



        $view->with(
            [
                'shippings' => $shippings,
                'payments' => $payments
            ]
        );
    }
}