<?php

namespace App\Mail;

use App\Helpers\HelperFunction;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\NewOrderEvent;

class NewOrder extends Mailable
{
    use Queueable, SerializesModels;

    public $products;

    public $totals;

    public $user_info;

    public $address;

    public $shipping_method;

    public $payment_method;

    public $date_added;

    public $comment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($products, $totals, $user_info, $address, $shipping_method, $payment_method, $date_added, $comment)
    {
        //
        $this->products = $products;
        $this->totals = $totals;
        $this->user_info = $user_info;
        $this->address = $address;
        $this->shipping_method = $shipping_method;
        $this->payment_method = $payment_method;
        $this->date_added = $date_added;
        $this->comment = $comment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->replyTo(Setting::get('sender_email'))
            ->subject(trans('mail.headers.new_order'))
            ->view('mail.new_order');
    }
}
