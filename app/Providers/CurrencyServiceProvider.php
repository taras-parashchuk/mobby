<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Torann\Currency\Console\Cleanup;
use Torann\Currency\Console\Manage;
use Torann\Currency\Console\Update;
use Torann\Currency\Currency;

class CurrencyServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCurrency();
    }

    /**
     * Register currency provider.
     *
     * @return void
     */
    public function registerCurrency()
    {
        $this->app->singleton('currency', function ($app) {
            return new \App\Services\Currency(
                $app->config->get('currency', []),
                $app['cache']
            );
        });
    }

    /**
     * Register currency resources.
     *
     * @return void
     */
    public function registerResources()
    {
        $this->publishes([
            __DIR__ . '/../config/currency.php' => config_path('currency.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/currency.php', 'currency'
        );

        $this->publishes([
            __DIR__ . '/../database/migrations' => base_path('/database/migrations'),
        ], 'migrations');
    }

    /**
     * Register currency commands.
     *
     * @return void
     */
    public function registerCurrencyCommands()
    {
        $this->commands([
            Cleanup::class,
            Manage::class,
            Update::class,
        ]);
    }

    public function boot()
    {

    }
}