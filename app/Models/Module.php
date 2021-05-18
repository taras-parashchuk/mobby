<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Module extends Model
{
    

    protected $with = [
        'template'
    ];

    protected $appends = [
        'decoded_setting'
    ];

    protected $casts = [
      'status' => 'boolean'
    ];

    public $timestamps = false;

    //
    public function template()
    {
        return $this->belongsTo(ModuleTemplate::class, 'template_module_id');
    }

    public function getDecodedSettingAttribute()
    {
        return json_decode($this->setting);
    }
}
