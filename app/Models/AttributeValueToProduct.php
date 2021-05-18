<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AttributeValueToProduct extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['attribute_value_id'];



    public function attribute_value()
    {
        return $this->belongsTo(AttributeValue::class, 'attribute_value_id', 'id');
    }
}
