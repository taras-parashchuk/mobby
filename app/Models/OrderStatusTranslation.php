<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class OrderStatusTranslation extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['locale', 'name'];


}
