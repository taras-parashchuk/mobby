<?php

namespace App\Providers;

use App\DemoAccess;
use App\Helpers\HelperFunction;
use App\Helpers\Image;
use App\Http\Middleware\CheckDemoCode;
use App\Http\Middleware\CheckRequestTypeInAdmin;
use App\Models\Category;
use App\Models\Language;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductPrice;
use App\Models\ProductSpecial;
use App\Models\Setting;
use App\Models\UserGroup;
use App\Widgets\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Arr;
use Gregwar\Cache;
use Artesaos\SEOTools\Facades\SEOMeta;
use Validator;
use Request;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */

    public function register()
    {
        if (! $this->app->environment('production')) {
            //$this->app->register(\JKocik\Laravel\Profiler\ServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    
	if (env('APP_ENV') === 'production') {
	        \Illuminate\Support\Facades\URL::forceScheme('https');
	}
    
        if (!$this->app->runningInConsole() && !request()->ajax()) {

            \Config::set('breadcrumbs.view', config('breadcrumbs.view'));

            View::composer('common.header', 'App\Http\View\Composers\HeaderComposer');
            View::composer('common.footer', 'App\Http\View\Composers\FooterComposer');
            View::composer('common.currency', 'App\Http\View\Composers\CurrencyComposer');
            View::composer('common.language', 'App\Http\View\Composers\LanguageComposer');
            View::composer('common.cart', 'App\Http\View\Composers\CartComposer');
            View::composer('common.left_column', 'App\Http\View\Composers\LeftColumnComposer');
            View::composer('common.bellow_header', 'App\Http\View\Composers\BellowHeaderComposer');
            View::composer('common.content_top', 'App\Http\View\Composers\ContentTopComposer');
            View::composer('common.content_bottom', 'App\Http\View\Composers\ContentBottomComposer');
            View::composer('pages.contacts', 'App\Http\View\Composers\ContactsComposer');
            View::composer('components.advantages', 'App\Http\View\Composers\AdvantagesComposer');

            View::composer('layouts.standart', function ($view) {
                $view->with([
                    'favicon' => asset(Image::compileImageSrc('settings/main/'.Setting::get('favicon_src'))) ?: ''
                ]);
            });

            View::composer('form.login', function ($view) {
                $view->with([
                    'error_login' => trans('validation.custom.login.format'),
                    'error_password' => trans('validation.min.string', ['attribute' => trans('validation.attributes.password'), 'min' => 4]),
                ]);
            });

            View::share(
                [
                    'styles' => [],
                    'scripts' => [],
                    'drop_menu' => '',
                    'script_tags' => [
                        'head' => htmlspecialchars_decode(Setting::get('script_tags.head', '', 'html')),
                        'start_body' => htmlspecialchars_decode(Setting::get('script_tags.start_body', '', 'html')),
                        'end_body' => htmlspecialchars_decode(Setting::get('script_tags.end_body', '', 'html')),
                    ],
                    'oauth' => \Config::get('services.google.client_id', false) || \Config::get('services.facebook.client_id', false)
                ]
            );

            View::share(
                ['menu_groups_categories' => Menu::getCategoriesList('vertical')]
            );

            $location = Location::where('id',config('settings.main_location'))->enabled()->first();

            if ($location) {

                $location->append('translate', 'TelephonesWithOperators');

                $location->messangers = collect();

            }

            View::share('main_location', $location ?? []);

            view()->composer('*', function ($view) {
                $view->with('view_type', \Session::get('view.type'));
                $view->with('with_list_depth_categories', Menu::getCategoriesList());
            });

            \view()->composer('admin', function ($view) {
                $view->with('languages', Language::getOnlyActive());
            });

            /*
            Validator::extend('not_exists', function($attribute, $value, $parameters)
            {
                return DB::table($parameters[0])
                        ->where($parameters[1], $value)
                        ->count()<1;
            });

            Validator::replacer('not_exists', function ($message, $attribute, $rule, $parameters) {
                return 'error text';
            });
            */


            $this->updateMinMaxPrices();
        }

    }

    private function updateMinMaxPrices()
    {

        $product_ids = DB::table('product_specials')
            ->select('product_id')
        ->whereNotNull('date_end')->get()->toArray();


        $product_ids = array_merge($product_ids, DB::table('product_discounts')
            ->select('product_id')
            ->whereNotNull('date_end')->get()->toArray());

        if (count($product_ids)) {

            $products = Product::with(['discounts' => function($q){
                $q->whereNotNull('date_end')->where('date_end', '<', now());
            }, 'specials' => function($q){
                $q->whereNotNull('date_end')->where('date_end', '<', now());
            }])->find(array_column($product_ids, 'product_id'));

            $user_groups = UserGroup::select('id')->get();

            $empty_users_group = new UserGroup();
            $empty_users_group->id = null;

            $user_groups->push($empty_users_group);

            $products->each(function ($product) {

                if($product->discounts->count()) $product->discounts()->delete();

                if($product->specials->count()) $product->specials()->delete();

                Product::setPrices($product);

            });
        }

    }
}
