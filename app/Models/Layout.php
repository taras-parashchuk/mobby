<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Cache;

class Layout extends Model
{
    public $timestamps = false;



    //
    public function modules()
    {
        return $this->hasManyThrough(
            'App\Models\Module',
            'App\Models\LayoutModule',
            'layout_id',
            'id',
            'id',
            'module_id'
        );
    }

    public function to_modules()
    {
        return $this->hasMany(LayoutModule::class);
    }

    public static function getCachedLayouts()
    {
        return Cache::rememberForever('layouts', function() {
            return self::all();
        });
    }

    public static function flushCache()
    {
        Cache::forget('layouts');
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
