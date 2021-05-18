<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Total extends Model
{
    protected $appends = ['decoded_setting'];

    public $timestamps = false;

    //
    public function getDecodedSettingAttribute()
    {
        return json_decode($this->setting) ?: [];
    }
}
