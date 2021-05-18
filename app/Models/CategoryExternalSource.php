<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class CategoryExternalSource extends Model
{
protected $table = 'categories_external_source';

    protected $fillable = [
        'category_id',
        'external_category_id',
        'external_type'
    ];

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }
}
