<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeToCategory extends Model
{
    public $timestamps = false;

    //
    public function attribute()
    {
        return $this->belongsTo('App\Models\Attribute', 'attribute_id');
    }

    public function values()
    {
        return $this->belongsTo('App\Models\AttributeValue', 'attribute_value_id');
    }
}
