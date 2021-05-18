<?php

namespace App\Helpers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use \Illuminate\Support\Facades\URL;
use Gregwar\Image\Image as ImageLibrary;
use Intervention\Image\Facades\Image as LfmImage;

class Image
{
    private static $placeholder = "core-images/placeholder.png";

    public static function compileImageSrc($path = '')
    {
        $catalogPath = public_path("uploads/images/");

        if (!is_file($catalogPath . $path) || substr(str_replace('\\', '/', realpath($catalogPath . $path)), 0, strlen($catalogPath)) != $catalogPath) {
            return URL::to(self::$placeholder);
        } else {
            return URL::to("uploads/images/$path");
        }
    }

    public static function getFileManagerThumb($type, $id, string $name ): string
    {
        return self::resize("$type/$id/$name", config('lfm.thumb_img_width'), config('lfm.thumb_img_height'));
    }

    public static function resize($path, $width, $height)
    {
        $catalog_path = "images/";

        $img_path = Storage::disk(config('lfm.disk'))->has($catalog_path.$path);

        if ( $path && $img_path ) {
            return ImageLibrary::open(Storage::disk(config('lfm.disk'))->path($catalog_path.$path))
                ->resize($width, $height)
                ->setCacheDir(url('cache/images'))
                ->setActualCacheDir(public_path('cache/images'))
                ->guess(80);
        } else {
            return ImageLibrary::open(self::$placeholder)
                ->resize($width, $height)
                ->setCacheDir(url('cache/images'))
                ->setActualCacheDir(public_path('cache/images'))
                ->guess(80);
        }
    }

    public static function createFolderIfNotExist($model, $type, $id)
    {
        $root_dir = "/images/";

        if (!Storage::disk(config('lfm.disk'))->exists($root_dir . "$type/$id")) {

            Storage::disk(config('lfm.disk'))->makeDirectory($root_dir . "$type/$id");

            Storage::disk(config('lfm.disk'))->makeDirectory($root_dir . "$type/$id/thumbs");
        }

        $tmp_file_path = "/tmp/{$model->image}";

        if (!Storage::disk(config('lfm.disk'))->exists($root_dir . "$type/$id/{$model->image}") && Storage::disk('public')->exists($tmp_file_path)) {

            $content = Storage::disk('public')->get($tmp_file_path);

            Storage::disk(config('lfm.disk'))->put($root_dir . "$type/$id/{$model->image}", $content);

            LfmImage::make(Storage::disk(config('lfm.disk'))->get($root_dir . "$type/$id/{$model->image}"))
                ->fit(config('lfm.thumb_img_width', 200), config('lfm.thumb_img_height', 200))->save(config('filesystems.disks.uploads.root') . $root_dir . "$type/$id/thumbs/{$model->image}");

            Storage::disk('public')->delete($tmp_file_path);
        }
    }

    public static function deleteFolder($type, $id)
    {
        Storage::disk(config('lfm.disk'))->deleteDirectory("images/$type/$id");
    }

    public static function downloadAndSaveByImagePath($image_url, Model $model): string
    {
        $image_url = str_replace(' ', '%20', $image_url);

        $container_directory = $model->getTable();

        $model_id = ($container_directory === 'products' && $model->type === 3) ? $model->main_id : $model->id;

        $newSrc = str_replace(array('https://wwww.', 'https://wwww.', 'https://', 'https://'), '', $image_url);

        $directories = explode('/', $newSrc);

        $image_name_with_ext = trim(array_pop($directories));

        list($image_name, $image_ext) = explode('.', $image_name_with_ext);

        $new_image_name = md5($image_name) . '.' . $image_ext;

        if ($new_image_name) {

            if (!file_exists(\Storage::disk(config('lfm.disk'))->exists("images/$container_directory/$model_id/$new_image_name"))) {


                $ch = curl_init(trim($image_url));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0');

                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                $exImg = curl_exec($ch);

                if ($exImg) {
                    \Storage::disk(config('lfm.disk'))->put("images/$container_directory/$model_id/$new_image_name", $exImg);
                }

                curl_close($ch);

            };
        }

        $image_url = $new_image_name;

        return $image_url;
    }

    public static function getContent($model, $name = null)
    {
        if (is_null($name)) {
            $name = $model->image;
        }

        $id = $model->main_id ?? $model->id;
        $path =  "/images/" . $model::MODEL_TYPE . '/' . $id . '/'. $name;

        try{
            return Storage::disk(config('lfm.disk'))->get($path);
        }catch (FileNotFoundException $exception){
            return null;
        }
    }

}