<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SyncConfiguration extends Model
{
    //
    public $timestamps = false;



    public function setSettingsAttribute($value)
    {
        $this->attributes['settings'] = json_encode($value);
    }

    public function getSettingsAttribute($value)
    {
        return json_decode($value);
    }
}
