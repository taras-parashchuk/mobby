<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            'App\Listeners\Syncs\Moysklad\NewCounterpartyListener'
        ],
        'App\Events\NewOrderEvent' => [
            'App\Listeners\NewOrderNotifyMailListener',
            'App\Listeners\NewFastOrderNotifyMailListener',
            'App\Listeners\Syncs\Moysklad\NewOrderCreateMoyskladListener',
        ],
        'App\Events\NewCallbackEvent' => [
            'App\Listeners\NewCallbackListener',
        ],
        'App\Events\NewTestimonialEvent' => [
            'App\Listeners\NewSiteTestimonialListener',
            'App\Listeners\NewProductTestimonialListener',
        ],
        'App\Events\UpdateOrderStatusEvent' => [
            'App\Listeners\UpdateOrderStatusListener',
        ],
        'App\Events\Syncs\Moysklad\UpdateOrderEvent' => [
            'App\Listeners\Syncs\Moysklad\UpdateOrderListener'
        ],
        'App\Events\NewProductEvent' => [
            'App\Listeners\Syncs\Moysklad\NewProductListener'
        ],
        'App\Events\NewCategoryEvent' => [
            'App\Listeners\Syncs\Moysklad\NewCategoryListener'
        ],
        'App\Events\UpdateCategoryEvent' => [
            'App\Listeners\Syncs\Moysklad\UpdateCategoryListener'
        ],
        'App\Events\UpdateProductEvent' => [
            'App\Listeners\Syncs\Moysklad\UpdateProductListener'
        ],
        'App\Events\UpdateUserEvent' => [
            'App\Listeners\Syncs\Moysklad\UpdateUserListener'
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
