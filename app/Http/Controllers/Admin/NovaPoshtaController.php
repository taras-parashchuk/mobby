<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NovaPoshta;

class NovaPoshtaController extends Controller
{
    //
    public function generate()
    {

        ini_set('max_execution_time', 0);
        set_time_limit(0);

        NovaPoshta::generate();

    }
}
