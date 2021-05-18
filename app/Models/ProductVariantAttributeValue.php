<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProductVariantAttributeValue extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['attribute_value_id', 'main_id', 'attribute_id'];

    protected $casts = [
        'attribute_value_id' => 'integer',
        'attribute_id' => 'integer'
    ];



    public function attribute_value()
    {
        return $this->belongsTo(AttributeValue::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
