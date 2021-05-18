<?php

namespace App\Models\Shippings;


use Illuminate\Database\Eloquent\Model;

class NovaPoshtaArea extends Model
{
    //
    public $timestamps = false;



    protected $primaryKey = 'ref';

    protected $casts = [
        'ref' => 'string'
    ];
}
