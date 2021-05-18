<?php

namespace App\Mail;

use App\Helpers\HelperFunction;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewTestimonial extends Mailable
{
    use SerializesModels, Queueable;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
            ->subject(trans('mail.headers.new_testimonial'))
            ->view('mail.new_testimonial');
    }
}
