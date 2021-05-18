<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProductToCompareCategory extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['category_id'];



    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
