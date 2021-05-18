<?php

namespace App\Http\View\Composers;

use App\Helpers\Image;
use App\Models\Category;
use App\Models\Information;
use App\Models\Location;
use App\Models\Setting;
use Illuminate\View\View;

class FooterComposer
{
    public function __construct()
    {

    }

    /**
     * Привязка данных к представлению.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        $service_links = collect();

        $service_links->push([
            'href' => route('testimonials'),
            'name' => trans('pages.testimonials.heading')
        ]);

        $service_links = $service_links->merge(Information::with(['translates' => function($q){
            $q->select('information_id', 'name', 'locale');
        }])->enabled()->where('in_bottom', true)->select('id','slug')->get()->map(function($information){
            return [
                'href' => $information->href,
                'name' => $information->translate->name
            ];
        }));

        $service_links->push([
            'href' => route('articles'),
            'name' => trans('pages.blog.heading')
        ]);

        $locations = Location::enabled()
            ->with(['translates' => function($q){
                $q->select('locale', 'location_id', 'address');
            }])
            ->select('longitude', 'id', 'latitude')
            ->get()
            ->each->append('translate');

        $view->with(
            [
                'categories' => Category::enabled()
                    ->with(['translates' => function($q){
                        $q->select('category_id', 'name', 'locale');
                    }])
                    ->where('parent_id', null)
                    ->orderBy('sort_order')
                    ->select('id', 'slug')
                    ->get()
                    ->each
                    ->append('translate'),
                'service_links' => $service_links,
                'locations' => $locations,
                'socials' => config('settings.socials'),
                'logo' => Setting::get('footer_logo') ? Image::compileImageSrc("settings/main/" . Setting::get('footer_logo')) : false,
                'about' => '',
                'schedule' => [],
                'powered' => '',
            ]
        );
    }
}