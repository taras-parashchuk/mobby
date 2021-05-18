<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Review extends Model
{
    //
    use NodeTrait;

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function getDateAddedAttribute()
    {
        if($this->created_at) return $this->created_at->format('d.m.Y');
    }
}
