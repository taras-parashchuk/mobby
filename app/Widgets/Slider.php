<?php

namespace App\Widgets;

use App\Models\BannerSlide;
use App\Models\Setting;
use Arrilot\Widgets\AbstractWidget;

//use App\Helpers\Image;


class Slider extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    private static $slides = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //

        if($this->config['title']){
            if(isset($this->config['title']->{\App::getLocale()})){
                $this->config['title'] = $this->config['title']->{\App::getLocale()};
            }elseif(isset($this->config['title']->{Setting::get('site_language')})){
                $this->config['title'] = $this->config['title']->{Setting::get('site_language')};
            }else{
                $this->config['title'] = '';
            }
        }else{
            $this->config['title'] = '';
        }

        if(!isset(self::$slides[$this->config['banner_id']])){
            self::$slides[$this->config['banner_id']] = BannerSlide::whereHas('banner', function($q){
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
        }

        $slides = self::$slides[$this->config['banner_id']];

        foreach ($slides as $k => $item) {
            $slides[$k]['compiled_image'] = \App\Helpers\Image::resize("banners/{$item['banner_id']}/{$item['image']}", $this->config['width'], $this->config['height']);
        }

        if($slides->count()){
            return [
                'view' => 'widgets.slider',
                'data' => [
                    'has_container' => true,
                    'config' => (object)$this->config,
                    'slides' => $slides
                ]
            ];
        }
    }
}
