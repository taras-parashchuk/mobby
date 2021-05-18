<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Currency extends Model
{

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'format',
        'exchange_rate',
        'active',
    ];

    protected $casts = [
        'active' => 'integer',
        'id' => 'integer'
    ];

    //
    protected static function boot()
    {
        parent::boot();

        /*static::saving(function ($model) {
            if ($model->isDirty('exchange_rate')) {
                Product::refreshPrices($model->code, $model->exchange_rate);
            }
        });
        */
    }
}
