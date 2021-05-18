<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CustomPasswordBrokerManager;
use Illuminate\Auth\Passwords\PasswordResetServiceProvider;

class CustomPasswordResetServiceProvider extends PasswordResetServiceProvider{
    protected $defer = true;

    public function register()
    {
        $this->registerPasswordBrokerManager();
    }

    protected function registerPasswordBrokerManager()
    {
        $this->app->singleton('auth.password', function ($app) {
            return new CustomPasswordBrokerManager($app);
        });
    }

    public function provides()
    {
        return ['auth.password'];
    }
}
