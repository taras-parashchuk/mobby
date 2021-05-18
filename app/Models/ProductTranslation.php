<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    //
    public $timestamps = false;



    protected function setKeysForSaveQuery(Builder $query)
    {
        $query
            ->where('product_id', '=', $this->getAttribute('product_id'))
            ->where('locale', '=', $this->getAttribute('locale'));

        return $query;
    }

    protected $fillable = ['locale', 'name', 'description', 'meta_h1', 'meta_title', 'meta_description', 'meta_keywords', 'warranty'];

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = htmlentities($value);
    }

    public function getDescriptionAttribute($value)
    {
        return html_entity_decode($value);
    }

    public function setMetaKeywordsAttribute($value)
    {
        $this->attributes['meta_keywords'] = substr($value, 0 , 250);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = substr($value, 0 , 200);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
