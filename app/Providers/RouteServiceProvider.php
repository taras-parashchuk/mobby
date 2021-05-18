<?php

namespace App\Providers;

use App\Http\Middleware\CheckRequestTypeInAdmin;
use App\Models\Alias;
use http\Env\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //
        parent::boot();

        Route::pattern('id', '[0-9]+');

        Route::pattern('slug', '[a-zA-Z0-9А-ЩЬЮЯҐЄІЇа-щьюяґєії\-\_]+');
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapAdminRoutes();

        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::get('products', 'App\Http\Controllers\ProductController@getCategoryProducts')->name('api.products');

        Route::middleware('web')->namespace('App\Http\Controllers')->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace('')
            ->group(base_path('routes/api.php'));
    }

    protected function mapAdminRoutes()
    {
        Route::middleware(['admin', 'web'])
            ->namespace('App\Http\Controllers\Admin')
            ->prefix('admin')
            ->group(base_path('routes/admin.php'));
    }
}
