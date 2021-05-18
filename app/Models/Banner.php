<?php

namespace App\Models;

use App\Helpers\ModelHelper;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use ModelHelper;

    protected $casts = [
        'status' => 'boolean'
    ];

    //
    public $timestamps = false;

    public function slides()
    {
        return $this->hasMany('App\Models\BannerSlide', 'banner_id', 'id');
    }
}
