<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;

class CurrencyComposer
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
                'user_currency' => currency()->getUserCurrency(),
                'currencies' => config('settings.currencies')
            ]
        );
    }
}