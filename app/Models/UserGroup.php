<?php

namespace App\Models;

use App\Helpers\ModelHelper;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    //

    public $timestamps = false;

    use ModelHelper;

    protected $casts = [
      'status' => 'boolean'
    ];

    public function translates()
    {
        return $this->hasMany('App\Models\UserGroupTranslation')
            ->whereIn('locale', Language::where('status', 1)->pluck('locale'));
    }

    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function(){
            Product::get()->each(function($product){
                Product::setPrices($product);
            });
        });

        static::deleted(function(){
            Product::get()->each(function($product){
                Product::setPrices($product);
            });
        });
    }
}
