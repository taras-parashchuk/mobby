<?php

namespace App\Widgets;

use App\Models\BannerSlide;
use Arrilot\Widgets\AbstractWidget;

//use App\Helpers\Image;


class Slideshow extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //

        $slides = BannerSlide::whereHas('banner', function($q){
            $q->select('id');
            $q->enabled();
        })->where('banner_id', $this->config['banner_id'])
            ->where([
                ['locale', app()->getLocale()],
                ['image', '<>', '']
            ])->whereNotNull('image')
            ->orderBy('sort_order', 'asc')
            ->select('title', 'link', 'image', 'banner_id')
            ->get();

        foreach ($slides as $k => $item) {
            $slides[$k]['compiled_image'] = \App\Helpers\Image::resize("banners/{$item['banner_id']}/{$item['image']}", $this->config['width'], $this->config['height']);
        }

        if($slides->count()){
            return [
                'view' => 'widgets.slideshow',
                'data' => [
                    'config' => (object)$this->config,
                    'slides' => $slides
                ]
            ];
        }
    }
}
