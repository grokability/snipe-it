<?php

namespace App\Providers;

use App\Listeners\CheckoutableListener;
use App\Listeners\CheckoutablesCheckedOutInBulkListener;
use App\Listeners\FulfillCheckoutRequestListener;
use App\Listeners\LogFailedLogin;
use App\Listeners\LogListener;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\RegenerateIdeHelperAfterMigrate;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
    //     'Illuminate\Auth\Events\Login' => [
    //         LogSuccessfulLogin::class,
    //     ],
    //
    //     'Illuminate\Auth\Events\Failed' => [
    //         LogFailedLogin::class,
    //     ],

        // Dev-env only: regenerate the ide-helper model stub so
        // Larastan + IDE autocomplete track newly-added columns the
        // moment the migration finishes. No-op (via class_exists
        // guard inside the listener) on production boxes that don't
        // have barryvdh/laravel-ide-helper installed.
        // (No longer needed to manually load, since we now have event autoloading enabled)
        // MigrationsEnded::class => [
        //     RegenerateIdeHelperAfterMigrate::class,
        // ],
    ];


    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        // LogListener::class,
        // Runs AFTER LogListener so the checkout Actionlog row it
        // just wrote is available for the checkout_actionlog_id
        // back-link on the CheckoutRequest.
        // FulfillCheckoutRequestListener::class,
        CheckoutableListener::class,
        // CheckoutablesCheckedOutInBulkListener::class,
    ];
}
