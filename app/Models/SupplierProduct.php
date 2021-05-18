<?php

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SupplierProduct extends Model
{
    //
    protected $casts = [
        'supplier_uuid' => 'string'
    ];

    protected $fillable = [
        'supplier_uuid',
        'price',
        'rrc_price',
        'quantity',
        'extra_2',
        'extra'
    ];

    public function category()
    {
        return $this->belongsTo(SupplierCategory::class, 'category_id', 'supplier_uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::createFromDate($value)->format('d.m.y H:i');
    }

    public static function boot()
    {
        parent::boot();

        static::saved(function ($model) {

            if ($model->product_id && $model->product->price_source === $model->supplier_code) {

                $model->product->vendor_price = $model->rrc_price;

                $model->product->update();

            }
        });
    }
}
