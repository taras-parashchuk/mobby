<?php

namespace App\Mail;

use App\Helpers\HelperFunction;
use App\Models\Message;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCallback extends Mailable
{
    use SerializesModels;

    public $callback;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        //
        $this->callback = $message;
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
            ->subject(trans('mail.headers.new_callback', ['type' => $this->callback->type]))
            ->view('mail.new_callback');
    }
}
