<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CategoryTranslation extends Model
{


    public $timestamps = false;

    protected $fillable = ['name', 'marketplace_name', 'locale', 'description', 'meta_title', 'meta_description', 'meta_keywords'];

    protected function setKeysForSaveQuery(Builder $query)
    {
        $query
            ->where('category_id', '=', $this->getAttribute('category_id'))
            ->where('locale', '=', $this->getAttribute('locale'));

        return $query;
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = htmlentities($value);
    }

    public function getDescriptionAttribute($value)
    {
        return html_entity_decode($value);
    }
}
