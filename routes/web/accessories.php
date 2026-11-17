<?php

use App\Http\Controllers\Accessories;
use App\Http\Controllers\Accessories\BulkAccessoriesController;
use App\Models\Accessory;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
* Accessories
 */
Route::group(['prefix' => 'accessories', 'middleware' => ['auth']], function () {
    Route::get(
        '{accessory}/checkout',
        [Accessories\AccessoryCheckoutController::class, 'create']
    )->name('accessories.checkout.show');

    Route::post(
        '{accessory}/checkout',
        [Accessories\AccessoryCheckoutController::class, 'store']
    )->name('accessories.checkout.store');

    // accessoryID is a numeric auto-increment PK. Constrain at the router
    // so non-numeric garbage (pen-test scanners) 404s here instead of
    // reaching the breadcrumb closure at BreadcrumbsServiceProvider:128
    // which is typed `int $accessoryID` and throws TypeError.
    Route::get(
        '{accessoryID}/checkin/{backto?}',
        [Accessories\AccessoryCheckinController::class, 'create']
    )->where('accessoryID', '[0-9]+')
        ->name('accessories.checkin.show');

    Route::post(
        '{accessoryID}/checkin/{backto?}',
        [Accessories\AccessoryCheckinController::class, 'store']
    )->where('accessoryID', '[0-9]+')
        ->name('accessories.checkin.store');

    // Bulk check-in from the accessory's checked-out tab. The `.show`
    // route renders the confirmation interstitial where the admin
    // picks the shared note; `.store` applies the checkin.
    Route::post(
        'bulkcheckin/show',
        [Accessories\AccessoryCheckinController::class, 'bulkCheckinShow']
    )->name('accessories.bulkcheckin.show');

    Route::post(
        'bulkcheckin/store',
        [Accessories\AccessoryCheckinController::class, 'bulkCheckinStore']
    )->name('accessories.bulkcheckin.store');

    Route::get('{accessory}/clone',
        [Accessories\AccessoriesController::class, 'getClone']
    )->name('clone/accessories');

    Route::post('{accessory}/clone',
        [Accessories\AccessoriesController::class, 'postCreate']
    );

    Route::post('{accessory}/adjust-quantity',
        [Accessories\AccessoriesController::class, 'adjustQuantity']
    )->name('accessories.adjust-quantity');

    // Bulk-fulfill: process the accessory's pending-request queue
    // in one pass. Reached from the "Fulfill Multiple" button on
    // the /requests admin queue when ≥2 pending requests exist for
    // the same accessory. See AccessoryCheckoutController for the
    // per-row iteration semantics.
    Route::get('{accessory}/fulfill-requests',
        [Accessories\AccessoryCheckoutController::class, 'bulkFulfillCreate']
    )->name('accessories.fulfill-requests.create')
        ->breadcrumbs(fn (Trail $trail, Accessory $accessory) => $trail
            // Bulk-fulfill always starts from the /requests queue -
            // root there so the trail reads as the natural flow.
            ->parent('requests.index')
            ->push($accessory->name, route('accessories.show', $accessory))
            ->push(trans('general.checkout'), route('accessories.fulfill-requests.create', $accessory))
        );

    Route::post('{accessory}/fulfill-requests',
        [Accessories\AccessoryCheckoutController::class, 'bulkFulfillStore']
    )->name('accessories.fulfill-requests.store');

    Route::post('{accessoryId}/restore',
        [Accessories\AccessoriesController::class, 'postRestore']
    )->where('accessoryId', '[0-9]+')
        ->name('accessories.restore');

    Route::post('{accessory}/unarchive',
        [Accessories\AccessoriesController::class, 'postUnarchive']
    )->where('accessory', '[0-9]+')
        ->name('accessories.unarchive');

    // Bulk-actions
    Route::post('bulkedit',
        [BulkAccessoriesController::class, 'edit']
    )->name('accessories.bulkedit.show')
        ->breadcrumbs(function (Trail $trail) {
            $label = match (request()->input('bulk_actions')) {
                'edit' => trans('general.bulk_edit'),
                'delete' => trans('general.bulk_delete'),
                default => trans('general.bulk_actions'),
            };

            return $trail->parent('accessories.index')->push($label, route('accessories.index'));
        });

    Route::post('bulksave',
        [BulkAccessoriesController::class, 'update']
    )->name('accessories.bulkedit.store');

    Route::post('bulkdelete',
        [BulkAccessoriesController::class, 'destroy']
    )->name('accessories.bulkdelete.store');

});

Route::resource('accessories', Accessories\AccessoriesController::class, [
    'middleware' => ['auth'],
])->withTrashed(['show']);

Route::post('accessories/bulk/delete', [BulkAccessoriesController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('accessories.bulk.delete');
