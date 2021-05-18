<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class LocationTranslation extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['locale', 'name', 'address', 'schedule'];

    protected $casts = [
        'schedule' => 'string'
    ];


}
