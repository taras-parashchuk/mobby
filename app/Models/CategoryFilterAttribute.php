<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryFilterAttribute extends Model
{
    public $timestamps = false;

    protected $casts = [
        'category_id' => 'integer',
        'attribute_id' => 'integer',
    ];

    protected $fillable = ['category_id', 'attribute_id'];

    //
    public function attribute()
    {
        return $this->belongsTo('App\Models\Attribute');
    }
}
