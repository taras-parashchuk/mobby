<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class StockStatusTranslation extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['locale', 'title'];


}
