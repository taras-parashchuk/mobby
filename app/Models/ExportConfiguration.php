<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ExportConfiguration extends Model
{
    //
    public $timestamps = false;



    protected $casts = [
        'products_list_id' => 'integer'
    ];

    public function setSettingsAttribute($value)
    {
        $this->attributes['settings'] = json_encode($value);
    }

    public function getSettingsAttribute($value)
    {
        return json_decode($value);
    }

    public function products_list()
    {
        return $this->hasOne(ExportProductsList::class,  'id', 'products_list_id');
    }

    public function getLinkAttribute()
    {
        return route('export-products', ['id' => $this->id]);
    }
}
