<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeToSupplierCategory extends Model
{
    //
    public $timestamps = false;

    protected $casts = [
        'category_id' => 'integer',
        'attribute_id' => 'integer'
    ];
}
