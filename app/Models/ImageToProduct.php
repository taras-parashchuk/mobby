<?php

namespace App\Models;

use App\Helpers\FileManager;
use App\Helpers\Image;

use Illuminate\Database\Eloquent\Model;
use UniSharp\LaravelFilemanager\Controllers\DeleteController;
use UniSharp\LaravelFilemanager\LfmPath;

class ImageToProduct extends Model
{
    //
    protected $table = 'images_to_product';

    protected $fillable = ['src', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
        'src' => 'string',
        'filemanager_thumb' => 'string'
    ];

    public $timestamps = false;



    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getCatalogThumb(Product $product, ImageToProduct $image = null)
    {
        $id = $this->calculateId($product);

        if(is_null($image)) $image = $this;

        return Image::resize("products/".$id."/$image->src",200, 200);
    }

    public function getMainThumb(Product $product, ImageToProduct $image = null)
    {
        $id = $this->calculateId($product);

        if(is_null($image)) $image = $this;

        return Image::resize("products/".$id."/$image->src",473, 473);
    }

    public function getPopup(Product $product, ImageToProduct $image = null)
    {
        $id = $this->calculateId($product);

        if(is_null($image)) $image = $this;

        return Image::resize("products/".$id."/$image->src",1000, 1000);
    }

    public function getRelatedThumb(Product $product, ImageToProduct $image = null)
    {
        $id = $this->calculateId($product);

        if(is_null($image)) $image = $this;

        return Image::resize("products/".$id."/$image->src",70, 70);
    }

    public function getFilemanagerThumb(Product $product = null, ImageToProduct $image = null)
    {
        $product_id = $product ? $product->id : $this->product_id;

        $src = $image ? $image->src : $this->src;

        if($this->src){
            return Image::resize("products/".$product_id."/$src",473, 473);
        }else{
            return null;
        }
    }

    private function calculateId(Product $product)
    {
        return $product->type === 3 ? $product->main_id : $product->id;
    }
}
