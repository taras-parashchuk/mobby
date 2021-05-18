<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Queue\SerializesModels;

class NewOrderEvent
{
    use SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        //
        $this->order = $order;
    }

}
