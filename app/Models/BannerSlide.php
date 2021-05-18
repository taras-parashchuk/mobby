<?php

namespace App\Models;

use App\Helpers\Image;
use App\Helpers\ModelHelper;

use Illuminate\Database\Eloquent\Model;

class BannerSlide extends Model
{
    //



    public $timestamps = false;

    protected $fillable = ['title', 'link', 'image'];

    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }

    public function getFilemanagerThumbAttribute()
    {
        if ($this->image) {
            return Image::getFileManagerThumb('banners', $this->banner_id, $this->image) ?? null;
        }else{
            return null;
        }
    }
}
