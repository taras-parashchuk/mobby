<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class ProductExternalSource extends Model
{
protected $table = 'products_external_source';

    protected $fillable = [
        'product_id',
        'external_product_id',
        'external_type'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
