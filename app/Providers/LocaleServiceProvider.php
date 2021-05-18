<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Route;
use App\Http\Middleware\LocaleMiddleware;
use Config;
use Redirect;
use Request;

class LocaleServiceProvider extends RouteServiceProvider
{
    public function boot()
    {
        parent::boot();

        /*
         * Cоздаем префикс для всех маршрутов и устанавливаем посредника
         * Для корректной работы префикса, класс наследуется от RouteServiceProvider
         */

        if (!$this->app->runningInConsole() ) {

            $settings = Setting::getAllSettings();

            $mainLanguageLocale = $settings->first(function ($item) {
                return $item['name'] === 'site_language';
            })->value;

            Config::set('languages.mainLanguage', $mainLanguageLocale ?? Config::get('languages.mainLanguage'));

            //Переключение языков

            $language = LocaleMiddleware::getLocale();

            if ($language) {
                Config::set('app.locale', $language);
            } else {
                Config::set('app.locale', $mainLanguageLocale);
            }
        }
    }
}
