<?php

namespace App\Models;

use App\Events\NewOrderEvent;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    public $timestamps = false;

    //
    protected $fillable = [
        'order_id',
        'order_status_id',
        'notify',
        'comment',
        'date_added',
    ];

    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\OrderStatus', 'order_status_id', 'id');
    }
}
