<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Cache;

class Language extends Model
{
    //
    public $timestamps = false;

    protected $casts = [
        'status' => 'boolean',
        'show_on_site' => 'boolean',
        'index' => 'boolean'
    ];



    public static function getOnlyActive()
    {
        return Cache::rememberForever('active.languages', function() {
            return self::where('status',1)->get();
        });
    }

    public static function flushCache()
    {
        Cache::forget('active.languages');
    }

    protected static function boot()
    {
        parent::boot();

        static::updated(function () {
            self::flushCache();
        });

        static::created(function() {
            self::flushCache();
        });

        static::deleted(function() {
            self::flushCache();
        });
    }
}
