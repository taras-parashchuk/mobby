<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProductToCategory extends Model
{
    //


    public $timestamps = false;

    protected $fillable = ['category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
