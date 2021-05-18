<?php

namespace App\Http\View\Composers;

use App\Helpers\Image;
use App\Models\Information;
use App\Models\Location;
use App\Models\Setting;
use Illuminate\View\View;

class HeaderComposer
{
    public function __construct()
    {

    }

    /**
     * Привязка данных к представлению.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with(
            [
                'logo' => Setting::get('header_logo') ? Image::resize("settings/main/".Setting::get('header_logo'), 168, 72) : false,
                'company_name' => Setting::get('company_name'),
                'compare_count' => 0,
                'wishlistCount' => 0,
                'cartCount' => \Cart::getTotalQuantity(),
                'informations' => Information::enabled()->where('in_top', 1)
                    ->with(['translates' => function($q){
                        $q->select('information_id', 'locale', 'name');
                    }])
                    ->select('id', 'slug')
                    ->get(),
            ]
        );
    }
}