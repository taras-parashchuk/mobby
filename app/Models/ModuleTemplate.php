<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ModuleTemplate extends Model
{
    //


    public $timestamps = false;

    public function modules()
    {
        return $this->hasMany(Module::class, 'template_module_id');
    }
}
