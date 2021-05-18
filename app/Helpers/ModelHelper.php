<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 2019-10-05
 * Time: 16:26
 */

namespace App\Helpers;

use App\Models\Setting;

trait ModelHelper
{
    public function getTranslateAttribute()
    {
        $translates  = $this->translates;

        $translate = $translates->firstWhere('locale', app()->getLocale());

        if(!$translate) $translate = $translates->firstWhere('locale', Setting::get('site_language'));

        return $translate;
    }

    public function getTranslateForAdminAttribute()
    {
        $translates  = $this->translates;

        $translate = $translates->firstWhere('locale', Setting::get('admin_language'));

        if(!$translate) $translate = $translates->firstWhere('locale', Setting::get('site_language'));

        return $translate;
    }

    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    public function getFilemanagerThumbAttribute()
    {
        if (isset($this->image) && $this->image) {
            return Image::getFileManagerThumb($this->getTable(),$this->id, $this->image) ?? null;
        }else{
            return null;
        }
    }

    public function resizeImage($width, $height)
    {
        return Image::resize("$this->table/".$this->id."/$this->image", $width, $height);
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function ($model){
            Image::deleteFolder( $model->getTable(), $model->id);
        });
    }
}