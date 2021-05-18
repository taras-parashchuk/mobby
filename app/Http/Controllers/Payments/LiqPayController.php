<?php

namespace App\Http\Controllers\Payments;

use App\Events\NewOrderEvent;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Payment;

class LiqPayController extends Controller
{
    private $version = 3;
    private $action = 'pay';

    public function info()
    {
        $order = Order::with('totals')->find(\Session::get('checkout.order_id'));

        $payment_settings = json_decode(Payment::where('code', 'liq_pay')->value('settings'));

        $private_key = $payment_settings->private_key;
        $public_key = $payment_settings->public_key;

        $amount = $order->totals->last()->value;
        $currency = $order->currency_code;
        $order_id = $order->id;
        $language = $order->locale;
        $result_url = route('checkout.success');
        $server_url = route('liqpay.callback');

        $send_data = array(
            'version' => $this->version,
            'public_key' => $public_key,
            'amount' => $amount,
            'currency' => $currency,
            'description' => trans('pages.checkout.confirm.online_pay', ['order_id' => $order_id]),
            'order_id' => $order_id,
            'action' => $this->action,
            'language' => $language,
            'server_url' => $server_url,
            'result_url' => $result_url);

        $liqpay_data = base64_encode(json_encode($send_data));
        $liqpay_signature = $this->calculateSignature($liqpay_data, $private_key);

        \Session::forget('checkout.order_id');

        return [
            'liqpay_data' => $liqpay_data,
            'liqpay_signatur' => $liqpay_signature
        ];


    }

    public function callback(\Request $request)
    {
        $data = $request->input('data');

        $payment_settings = json_decode(Payment::where('code', 'liqpay')->value('settings'));

        $private_key = $private_key = $payment_settings->private_key;

        $signature = $this->calculateSignature($data, $private_key);
        $parsed_data = json_decode(base64_decode($data), true);
        $order_id = $parsed_data['order_id'];

        if ($signature == $request->input('signature')) {

            $order = Order::find($order_id);

            $order_history = new OrderHistory();

            $order_history->order_status_id = 1;
            $order_history->notify = 0;
            $order_history->comment = \Session::get('order_comment');
            $order_history->order_id = $order_id;
            $order_history->date_added = now();

            $order_history->save();

            \Cart::clear();

            session()->forget('checkout');

            event(new NewOrderEvent($order));
        }
    }

    private function calculateSignature($data, $private_key)
    {
        return base64_encode(sha1($private_key . $data . $private_key, true));
    }
}