<?php

namespace App\Http;

use App\Http\Middleware\CanCreateOrder;
use App\Http\Middleware\CheckCustomRedirect;
use App\Http\Middleware\CheckRequestTypeInAdmin;
use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Session\Middleware\StartSession::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            //\Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            //\Torann\Currency\Middleware\CurrencyMiddleware::class,
            \Laravel\Passport\Http\Middleware\CreateFreshApiToken::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Torann\Currency\Middleware\CurrencyMiddleware::class,
            LocaleMiddleware::class,
            CheckCustomRedirect::class,
        ],
        'api' => [
            \App\Http\Middleware\EncryptCookies::class,
            'throttle:60,1',
            'bindings',
        ],
        'admin' => [
            //\Illuminate\Session\Middleware\StartSession::class,
            //'web',
            'admin_locale',
            'check_is_admin',
        ]
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'can_create_order' => CanCreateOrder::class,
        'admin_locale' => \App\Http\Middleware\AdminLocale::class,
        'check_is_admin' => \App\Http\Middleware\CheckRequestTypeInAdmin::class,
        'external_api_auth' => \App\Http\Middleware\ExternalApiAuth::class,
        'check_external_api_status' => \App\Http\Middleware\CheckExternalApiStatus::class
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
        'admin_locale',
        'check_is_admin',
    ];
}
