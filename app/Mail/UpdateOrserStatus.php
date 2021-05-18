<?php

namespace App\Mail;

use App\Helpers\HelperFunction;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateOrserStatus extends Mailable
{
    use Queueable, SerializesModels;

    public $order_id;

    public $comment;

    public $status;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(int $order_id, string $comment, string $status)
    {
        //
        $this->order_id = $order_id;
        $this->comment = $comment;
        $this->status = $status;
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
            ->subject(trans('mail.headers.update_order', ['order_id' => $this->order_id]))
            ->view('mail.update_order_status');
    }
}
