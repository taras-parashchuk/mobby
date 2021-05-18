<?php

namespace App\Models\Syncs;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ExternalApi extends Model
{

    protected $table = 'externals_api';

    protected $fillable = [
        'name',
        'login',
        'password',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getSettingsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setLoginAttribute($value)
    {
        $this->attributes['login'] = Crypt::encryptString($value);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    public function getPasswordAttribute($value)
    {
        try{
            return Crypt::decryptString($value);
        }catch (DecryptException $decryptException){
            return null;
        }

    }

    public function getLoginAttribute($value)
    {
        try{
            return Crypt::decryptString($value);
        }catch (DecryptException $decryptException){
            return null;
        }

    }
}
