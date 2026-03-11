<?php

namespace App\Custom\Providers;

use App\Custom\Events\StockBelowMinimum;
use App\Custom\Events\WorkOrderCreated;
use App\Custom\Listeners\StockDepletedListener;
use App\Custom\Listeners\WorkOrderCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class FomEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        WorkOrderCreated::class => [
            WorkOrderCreatedListener::class,
        ],
        StockBelowMinimum::class => [
            StockDepletedListener::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
