<?php

namespace App\Services\Moysklad;

use App\Helpers\Image;
use App\Models\ImageToProduct;
use App\Models\Syncs\ExternalApi;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp;

class ImageService
{
    /**
     * Store product(variant) images
     *
     * @param $externalProduct
     * @param $dbProduct
     */
    public static function storeImages($externalProduct, $dbProduct)
    {
        $client = new Client(1.2);
        $path = $externalProduct->meta->type . '/' . $externalProduct->id . '/images';
        $imagesToProduct = [];

        $res = $client->getGuzzle($path);

        $images = json_decode($res->getBody()->getContents())->rows;

        foreach ($images as $key => $image) {
            $idForPath = $dbProduct->main_id ?? $dbProduct->id;
            $imagePath = "images/" . $dbProduct::MODEL_TYPE . "/{$idForPath}/{$image->filename}";
            $downloadImageUrl = explode('?', $image->miniature->href)[0];
            $content = ImageService::getImage($downloadImageUrl, $dbProduct);

            if ($content) {
                if ($key == 0) {
                    $dbProduct->image = $image->filename;
                    $dbProduct->update();
                } else {
                    $imagesToProduct[] = new ImageToProduct([
                        'src' => $image->filename
                    ]);
                }

                Storage::disk('uploads')->put($imagePath, $content);
            }
        }

        $dbProduct->images()->delete();
        $dbProduct->images()->saveMany($imagesToProduct);
    }

    /**
     * Download image from moy-sklad
     *
     * @param $url
     * @param null $sync
     * @return string
     */
    public static function getImage($url, $dbProduct)
    {
        $externalApi = ExternalApi::where('code', config('syncs.moysklad.externalCode'))->first();

        $client = new GuzzleHttp\Client([
            'auth' => [$externalApi->login, $externalApi->password]
        ]);

        try {
            $res = $client->get($url);
        } catch (\Exception $exception) {
            Service::writeLog(config('syncs.moysklad.dataTypes.product'), trans('moy-sklad.errors.product.images_not_downloaded', [
                'name' => $dbProduct->translate->name
            ]));
            Service::exception($exception);
        }

        return $res->getBody()->getContents();
    }

    /**
     * Get images for uploading to moy-sklad
     *
     * @param $model
     * @return array
     */
    public static function getImages($model)
    {
        $images = [];

        if (!is_null($model->image)) {
            $mainImage = Image::getContent($model);
            if ($mainImage) {
                $imageBase64 = base64_encode($mainImage);

                $images[] = (object)[
                    'filename' => $model->image,
                    'content' => $imageBase64,
                ];
            }
        }

        if (($model->images->count())) {
            foreach ($model->images as $item) {
                if (!is_null($item->src)) {
                    $image = Image::getContent($model, $item->src);
                    if ($image) {
                        $imageBase64 = base64_encode($image);

                        $images[] = (object)[
                            'filename' => $item->src,
                            'content' => $imageBase64,
                        ];
                    }
                }
            }
        }

        return $images;
    }

    public static function uploadProductImages($product, $type = 'product')
    {
        $images = array_slice(ImageService::getImages($product), 0, 10);

        $data = [];
        if (!empty($images)) {
            $data = $images;
        }

        $client = new Client(1.2);
        $externalSource = $product->externalSource()->where('external_type', config('syncs.moysklad.externalCode'))->first();
        $path = $type . '/' . $externalSource->external_product_id . '/images';

        try {
            $client->postGuzzle($path, [
                'json' => $data
            ]);
        } catch (\Exception $exception){
            Service::writeLog(config('syncs.moysklad.dataTypes.product'), trans('moy-sklad.errors.product.images_not_uploaded', [
                'name' => $product->translate->name
            ]));
            Service::exception($exception);
        }
    }
}
