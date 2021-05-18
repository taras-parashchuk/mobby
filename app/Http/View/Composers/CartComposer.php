<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;

class CartComposer
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
                'Cart' => [
                    'items' => 0,
                    'products' => []
                ]
            ]
        );
    }
}