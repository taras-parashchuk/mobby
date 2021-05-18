<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{



    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    private static $data = null;

    /**
     * Add a settings value
     *
     * @param $key
     * @param $val
     * @param string $type
     * @return bool
     */
    public static function add($key, $val, $group = 'main', $type = 'string')
    {
        if (self::has($key, $group)) {
            return self::set($key, $val, $group, $type);
        }

        return self::create(['name' => $key, 'value' => $val, 'group' => $group, 'type' => $type]) ? $val : false;
    }

    /**
     * Get a settings value
     *
     * @param $key
     * @param null $default
     * @return bool|int|mixed
     */
    public static function get($key, $default = null, $group = 'main')
    {
        if (self::has($key, $group)) {

            $setting = self::getAllSettings()->where('name', $key)->where('group', $group)->first();

            return self::castValue($setting->value, $setting->type);
        }

        return $default;
    }

    /**
     * Set a value for setting
     *
     * @param $key
     * @param $val
     * @param string $type
     * @return bool
     */
    public static function set($key, $val, $group = 'main', $type = 'string')
    {
        if ($setting = self::getAllSettings()->where('name', $key)->where('group', $group)->first()) {
            return $setting->update([
                'name' => $key,
                'value' => $val,
                'group' => $group,
                'type' => $type]) ? $val : false;
        }

        return self::add($key, $val, $group, $type);
    }

    /**
     * Remove a setting
     *
     * @param $key
     * @return bool
     */
    public static function remove($key)
    {
        if (self::has($key)) {
            return self::whereName($key)->delete();
        }

        return false;
    }

    /**
     * Check if setting exists
     *
     * @param $key
     * @return bool
     */
    public static function has($key, $group = 'main')
    {
        return (boolean)self::getAllSettings()
            ->whereStrict('name', $key)
            ->where('group', $group)
            ->count();
    }

    /**
     * caste value into respective type
     *
     * @param $val
     * @param $castTo
     * @return bool|int
     */
    private static function castValue($val, $castTo)
    {
        switch ($castTo) {
            case 'int':
            case 'integer':
                return intval($val);
                break;

            case 'bool':
            case 'boolean':
                return boolval($val);
                break;

            default:
                return $val;
        }
    }

    /**
     * Get all the settings fields from config
     *
     * @return Collection
     */
    private static function getDefinedSettingFields()
    {
        return collect(config('settings'))->pluck('elements')->flatten(1);
    }

    /**
     * Get the data type of a setting
     *
     * @param $field
     * @return mixed
     */
    public static function getDataType($field)
    {
        $type  = self::getDefinedSettingFields()
            ->pluck('data', 'name')
            ->get($field);

        return is_null($type) ? 'string' : $type;
    }

    /**
     * Get all the settings
     *
     * @return mixed
     */
    public static function getAllSettings()
    {
        if(!self::$data){
            self::$data = \Cache::rememberForever('settings.all', function() {
                return self::all();
            });
        }

        return self::$data;

    }

    public static function flushCache()
    {
        \Cache::forget('settings.all');
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

        static::saving(function($setting){
            if($setting->name == 'header_logo'){
                $setting->value = str_replace(env('APP_URL') . 'uploads/images/', '', $setting->value);
            }else if($setting->name == 'footer_logo'){
                $setting->value = str_replace(env('APP_URL') . 'uploads/images/', '', $setting->value);
            }
        });
    }
}