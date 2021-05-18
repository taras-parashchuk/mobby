<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    //

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'sku',
        'name',
        'specification',
        'quantity',
        'price',
        'special',
    ];

    protected $casts = [
        'quantity' => 'integer'
    ];

    protected $appends = ['priceFormat', 'specialFormat', 'total_format'];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function getPriceFormatAttribute()
    {
        return currency($this->price);
    }

    public function getSpecialFormatAttribute()
    {
        return currency($this->special) ?? false;
    }

    public function getTotalFormatAttribute()
    {
        return currency(($this->special ?: $this->price) * $this->quantity);
    }

    public function getSpecificationAttribute($value)
    {
        return json_decode($value);
    }

    public function externalSource()
    {
        return $this->hasMany('App\Models\OrderProductExternalSource', 'order_products_id', 'id');
    }

    public static function boot()
    {
        parent::boot();

        static::created(function($model){
            $model->product->quantity -= $model->quantity;

            $model->product->save();
        });

    }
}