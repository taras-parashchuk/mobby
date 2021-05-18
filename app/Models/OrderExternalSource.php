<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class OrderExternalSource extends Model
{
    protected $table = 'orders_external_source';

    protected $fillable = [
        'order_id',
        'external_order_id',
        'external_type'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Order');
    }
}