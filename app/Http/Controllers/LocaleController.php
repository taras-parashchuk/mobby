<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;

class LocaleController extends Controller
{
    //
    public function handle(Request $request, $lang)
    {
        $referer = Redirect::back()->getTargetUrl();
        $parse_url = parse_url($referer, PHP_URL_PATH);

        //разбиваем на массив по разделителю
        $segments = explode('/', $parse_url);

        if (in_array($segments[1], config('languages')['languages'])) {
            unset($segments[1]); //удаляем метку
        }

        if ($lang != config('languages')['mainLanguage']) {
            array_splice($segments, 1, 0, $lang);
        }

        $url = $request->root() . implode("/", $segments);

        //если были еще GET-параметры - добавляем их
        if (parse_url($referer, PHP_URL_QUERY)) {
            $url = $url . '?' . parse_url($referer, PHP_URL_QUERY);
        }
        return redirect($url);
    }
}
