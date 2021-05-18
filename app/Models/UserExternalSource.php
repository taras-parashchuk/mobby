<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class UserExternalSource extends Model
{
protected $table = 'users_external_source';

    protected $fillable = [
        'user_id',
        'external_user_id',
        'external_type'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
