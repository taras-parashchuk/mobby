<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTotal extends Model
{
    public $timestamps = false;
    //
    protected $fillable = [
        'order_id',
        'code',
        'name',
        'value',
        'sort_order',
    ];

    protected $appends = ['valueFormat'];

    public function getValueFormatAttribute()
    {
        return $this->value > 0 ? currency($this->value) : 0;
    }
}
