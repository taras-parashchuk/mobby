<?php

namespace App\Mail;

use App\Helpers\HelperFunction;
use App\Helpers\Image;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewFastOrder extends Mailable
{
    use Queueable, SerializesModels;

    public $telephone;
    public $products;
    public $route_home;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($telephone, $products)
    {
        //
        $this->telephone = $telephone;
        $this->products = $products;
        $this->route_home = route('home');
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
            ->subject(trans('mail.headers.received_fast_order', ['telephone' => $this->telephone]))
            ->view('mail.new_fast_order')
            ->with([
                'store_name' => Setting::get('company_name'),
                'logo' => Image::compileImageSrc(Setting::get('header_logo'))
            ]);
    }
}
