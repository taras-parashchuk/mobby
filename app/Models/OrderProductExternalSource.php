<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class OrderProductExternalSource extends Model
{
protected $table = 'order_products_external_source';

    protected $fillable = [
        'order_products_id',
        'external_order_product_id',
        'external_type'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\OrderProduct');
    }
}
