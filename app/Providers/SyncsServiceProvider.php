<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SyncsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('moysklad', function ($app) {
            return new \App\Services\Moysklad();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
