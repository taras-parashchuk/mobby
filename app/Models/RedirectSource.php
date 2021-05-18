<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RedirectSource extends Model
{
    //
    public $timestamps = false;

    public function target()
    {
        return $this->belongsTo(RedirectTarget::class);
    }
}
