<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ExportProductsList extends Model
{


    public $timestamps = false;

    //
    public function products()
    {
        return $this->hasManyThrough(
          Product::class,
          ProductToExportProductsList::class,
            'products_list_id',
            'id',
            'id',
            'product_id'

        );
    }

    public function to_products()
    {
        return $this->hasMany(ProductToExportProductsList::class, 'products_list_id', 'id');
    }
}
