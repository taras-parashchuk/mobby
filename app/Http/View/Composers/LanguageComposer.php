<?php

namespace App\Http\View\Composers;

use App\Models\Language;
use Illuminate\View\View;

class LanguageComposer
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
                'current_locale' => app()->getLocale(),
                'languages' => config('settings.languages')
            ]
        );
    }
}