<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRelated extends Model
{
    //
    protected $table = 'product_relateds';

    public $timestamps = false;

    protected $fillable = ['source_id'];

    public function source()
    {
        return $this->belongsTo(Product::class, 'source_id', 'id');
    }
}
