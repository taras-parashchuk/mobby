<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;

class ContactsComposer
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
                //'addresses' => json_decode()
            ]
        );
    }
}