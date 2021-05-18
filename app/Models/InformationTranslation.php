<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class InformationTranslation extends Model
{



    public $timestamps = false;

    //
    protected $fillable = ['name', 'description', 'meta_title', 'meta_description', 'meta_keywords', 'locale'];

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = htmlentities($value);
    }

    public function getDescriptionAttribute($value)
    {
        return html_entity_decode($value);
    }
}
