<?php

namespace App\Providers;

use App\Models\Language;
use App\Models\Location;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Config;
use Cookie;
use Crypt;
use Artesaos\SEOTools\Facades\SEOMeta;
use Klisl\Locale\LocaleServiceProvider;
use App;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (!$this->app->runningInConsole() ) {

            $settings = Setting::getAllSettings();

            $mainLanguageLocale = $settings->first(function ($item) {
                return $item['name'] === 'site_language';
            })->value;

            $mainCurrencyCode = $settings->first(function ($item) {
                return $item['name'] === 'currency';
            })->value;

            Config::set('settings.languages', Language::getOnlyActive()->where('show_on_site', true));

            Config::set('settings.currencies', currency()->getActiveCurrencies());

            config('settings.languages')->each(function ($language) {
                if (!$language->index && $language->locale == app()->getLocale()) {
                    SEOMeta::addMeta('robots', 'noindex, nofollow', 'name');
                }
            });

            $languages = config('settings.languages')->pluck('locale')->toArray();

            Config::set('languages.mainLanguage', $mainLanguageLocale ?? Config::get('languages.mainLanguage'));

            Config::set('languages.languages', $languages ?? Config::get('languages.languages'));

            Config::set('settings.main_currency', $mainCurrencyCode);

            Config::set('settings.main_location', $settings->firstWhere('name', 'location')->value ?? null);

            Config::set('settings.emails', explode(',', $settings->firstWhere('name', 'emails')->value ?? ''));

            Config::set('hit_products_ids', App\Models\Product::orderBy('viewed', 'DESC')->limit(15)->pluck('id'));


            if (!request()->ajax()) {

                $socials = Config::get('settings.socials.default');

                if($socials){
                    if ($f_href = $settings->firstWhere('name', 'link_facebook')->value ?? null) {
                        $socials['facebook']['href'] = $f_href;
                    } else {
                        unset($socials['facebook']);
                    }

                    if ($f_href = $settings->firstWhere('name', 'link_instagram')->value ?? null) {
                        $socials['instagram']['href'] = $f_href;
                    } else {
                        unset($socials['instagram']);
                    }
                }

                Config::set('settings.socials', $socials ?? []);

                Config::set('mail.from.name', $settings->firstWhere('name', 'company_name')->value ?? '');
                Config::set('mail.from.address', $settings->firstWhere('name', 'sender_email')->value);

                $oauth_google = json_decode(Setting::get('google', '', 'oauth'));
                $oauth_facebook = json_decode(Setting::get('facebook', '', 'oauth'));

                if ($oauth_facebook) {
                    Config::set('services.facebook.client_id', $oauth_facebook->id);
                    Config::set('services.facebook.client_secret', $oauth_facebook->key);
                }

                if ($oauth_google) {
                    Config::set('services.google.client_id', $oauth_google->id);
                    Config::set('services.google.client_secret', $oauth_google->key);
                }
            }

        }
    }
}
