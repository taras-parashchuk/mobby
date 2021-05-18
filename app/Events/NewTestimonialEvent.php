<?php

namespace App\Events;

use App\Models\Testimonial;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewTestimonialEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $for_product;

    public $testimonial;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Testimonial $testimonial)
    {
        //
        $this->testimonial = $testimonial;

        $this->for_product = (bool)$testimonial->product_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
