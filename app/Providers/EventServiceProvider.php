<?php

namespace App\Providers;

use App\Listeners\CheckoutableListener;
use App\Listeners\CheckoutablesCheckedOutInBulkListener;
use App\Listeners\LogFailedLogin;
use App\Listeners\LogListener;
use App\Listeners\LogSuccessfulLogin;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Auth\Events\Login' => [
            LogSuccessfulLogin::class,
        ],

        'Illuminate\Auth\Events\Failed' => [
            LogFailedLogin::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        LogListener::class,
        CheckoutableListener::class,
        CheckoutablesCheckedOutInBulkListener::class,
    ];
}
