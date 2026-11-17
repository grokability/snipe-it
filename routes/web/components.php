<?php

use App\Http\Controllers\Components;
use App\Http\Controllers\Components\BulkComponentsController;
use App\Models\Component;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

// Components
Route::group(['prefix' => 'components', 'middleware' => ['auth']], function () {
    // Component / componentAsset ids are always numeric auto-increment PKs,
    // so constrain the route parameters at the router. Without this,
    // non-numeric garbage (pen-test scanners hitting things like
    // components/2||(SELECT...) reach the breadcrumb closure which is
    // typed `int $componentID` and throws TypeError, spamming Rollbar.
    // Constraining at the router 404s the request before any controller
    // or breadcrumb runs.
    Route::get(
        '{componentID}/checkout',
        [Components\ComponentCheckoutController::class, 'create']
    )->where('componentID', '[0-9]+')
        ->name('components.checkout.show');

    Route::post(
        '{componentID}/checkout',
        [Components\ComponentCheckoutController::class, 'store']
    )->where('componentID', '[0-9]+')
        ->name('components.checkout.store');

    Route::get(
        '{componentID}/checkin/{backto?}',
        [Components\ComponentCheckinController::class, 'create']
    )->where('componentID', '[0-9]+')
        ->name('components.checkin.show');

    Route::post(
        '{componentID}/checkin/{backto?}',
        [Components\ComponentCheckinController::class, 'store']
    )->where('componentID', '[0-9]+')
        ->name('components.checkin.store');

    // Bulk check-in from the component's assigned tab. The `.show`
    // route renders the confirmation interstitial where the admin
    // picks the shared note. `.store` applies the checkin.
    Route::post(
        'bulkcheckin/show',
        [Components\ComponentCheckinController::class, 'bulkCheckinShow']
    )->name('components.bulkcheckin.show');

    Route::post(
        'bulkcheckin/store',
        [Components\ComponentCheckinController::class, 'bulkCheckinStore']
    )->name('components.bulkcheckin.store');

    Route::get('{component}/clone',
        [Components\ComponentsController::class, 'getClone']
    )->where('component', '[0-9]+')
        ->name('components.clone.create')
        ->withTrashed();

    Route::post('{component}/adjust-quantity',
        [Components\ComponentsController::class, 'adjustQuantity']
    )->where('component', '[0-9]+')
        ->name('components.adjust-quantity');

    Route::post('{componentID}/restore',
        [Components\ComponentsController::class, 'postRestore']
    )->where('componentID', '[0-9]+')
        ->name('components.restore');

    // Bulk-fulfill queue for this component. Distinct from the
    // sibling user-target bulk-fulfill routes (accessories/
    // consumables/licenses) because component checkouts target an
    // Asset - the per-row picker filters to the requester's
    // assigned assets.
    Route::get('{component}/fulfill-requests',
        [Components\ComponentCheckoutController::class, 'bulkFulfillCreate']
    )->where('component', '[0-9]+')
        ->name('components.fulfill-requests.create')
        ->breadcrumbs(fn (Trail $trail, Component $component) => $trail
            ->parent('requests.index')
            ->push($component->name, route('components.show', $component))
            ->push(trans('general.checkout'), route('components.fulfill-requests.create', $component))
        );

    Route::post('{component}/fulfill-requests',
        [Components\ComponentCheckoutController::class, 'bulkFulfillStore']
    )->where('component', '[0-9]+')
        ->name('components.fulfill-requests.store');

    // Bulk-select
    Route::post('bulkedit',
        [BulkComponentsController::class, 'edit']
    )->name('components.bulkedit.show')
        ->breadcrumbs(function (Trail $trail) {
            $label = match (request()->input('bulk_actions')) {
                'edit' => trans('general.bulk_edit'),
                'delete' => trans('general.bulk_delete'),
                default => trans('general.bulk_actions'),
            };

            return $trail->parent('components.index')->push($label, route('components.index'));
        });

    Route::post('bulksave',
        [BulkComponentsController::class, 'update']
    )->name('components.bulkedit.store');

    Route::post('bulkdelete',
        [BulkComponentsController::class, 'destroy']
    )->name('components.bulkdelete.store');

});

Route::resource('components', Components\ComponentsController::class, [
    'middleware' => ['auth'],
    'parameters' => ['component' => 'component_id'],
])->withTrashed(['show']);
