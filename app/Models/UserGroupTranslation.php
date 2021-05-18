<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserGroupTranslation extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['name', 'summary', 'locale'];


}
