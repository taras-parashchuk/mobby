<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AttributeValueTranslation extends Model
{
    //
    protected $fillable = ['locale', 'value'];

    public $timestamps = false;



    public function attribute_value()
    {
        return $this->belongsTo('App\Models\AttributeValue', 'attribute_value_id', 'id');
    }
}
