<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AttributeToProduct extends Model
{
    //
    public $timestamps = false;

    protected $casts = [
        'attribute_id' => 'integer',
        'main' => 'boolean',
        'product_id' => 'integer',
        'has_variant' => 'boolean',
    ];

    protected $fillable = ['attribute_id', 'main', 'has_variant'];



    public function to_values()
    {
        return $this->hasMany(AttributeValueToProduct::class, 'attribute_to_product_id', 'id');
    }

    public function values()
    {
        return $this->hasManyThrough(
          AttributeValue::class,
          AttributeValueToProduct::class,
          'attribute_to_product_id',
          'id',
          'id',
          'attribute_value_id'
        );
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
