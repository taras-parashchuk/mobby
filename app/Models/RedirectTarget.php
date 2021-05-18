<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedirectTarget extends Model
{
    //

    public $timestamps = false;

    public function sources()
    {
        return $this->hasMany(RedirectSource::class, 'target_id', 'id');
    }
}
