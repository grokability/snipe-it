<?php

use App\Http\Controllers\Consumables;
use App\Http\Controllers\Consumables\BulkConsumablesController;
use App\Models\Consumable;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::group(['prefix' => 'consumables', 'middleware' => ['auth']], function () {
    Route::get(
        '{consumablesID}/checkout',
        [Consumables\ConsumableCheckoutController::class, 'create']
    )->where('consumablesID', '[0-9]+')
        ->name('consumables.checkout.show');

    Route::post(
        '{consumablesID}/checkout',
        [Consumables\ConsumableCheckoutController::class, 'store']
    )->where('consumablesID', '[0-9]+')
        ->name('consumables.checkout.store');

    Route::get('{consumable}/clone',
        [Consumables\ConsumablesController::class, 'clone']
    )->where('consumable', '[0-9]+')
        ->name('consumables.clone.create');

    Route::post('{consumable}/adjust-quantity',
        [Consumables\ConsumablesController::class, 'adjustQuantity']
    )->where('consumable', '[0-9]+')
        ->name('consumables.adjust-quantity');

    // Bulk-fulfill queue for this consumable. See sibling
    // accessories.fulfill-requests.* for the shared per-row shape
    // and controller semantics.
    Route::get('{consumable}/fulfill-requests',
        [Consumables\ConsumableCheckoutController::class, 'bulkFulfillCreate']
    )->where('consumable', '[0-9]+')
        ->name('consumables.fulfill-requests.create')
        ->breadcrumbs(fn (Trail $trail, Consumable $consumable) => $trail
            ->parent('requests.index')
            ->push($consumable->name, route('consumables.show', $consumable))
            ->push(trans('general.checkout'), route('consumables.fulfill-requests.create', $consumable))
        );

    Route::post('{consumable}/fulfill-requests',
        [Consumables\ConsumableCheckoutController::class, 'bulkFulfillStore']
    )->where('consumable', '[0-9]+')
        ->name('consumables.fulfill-requests.store');

    Route::post('{consumableId}/restore',
        [Consumables\ConsumablesController::class, 'postRestore']
    )->where('consumableId', '[0-9]+')
        ->name('consumables.restore');

    Route::post('{consumable}/unarchive',
        [Consumables\ConsumablesController::class, 'postUnarchive']
    )->where('consumable', '[0-9]+')
        ->name('consumables.unarchive');

    // Bulk-actions
    Route::post('bulkedit',
        [BulkConsumablesController::class, 'edit']
    )->name('consumables.bulkedit.show')
        ->breadcrumbs(function (Trail $trail) {
            $label = match (request()->input('bulk_actions')) {
                'edit' => trans('general.bulk_edit'),
                'delete' => trans('general.bulk_delete'),
                default => trans('general.bulk_actions'),
            };

            return $trail->parent('consumables.index')->push($label, route('consumables.index'));
        });

    Route::post('bulksave',
        [BulkConsumablesController::class, 'update']
    )->name('consumables.bulkedit.store');

    Route::post('bulkdelete',
        [BulkConsumablesController::class, 'destroy']
    )->name('consumables.bulkdelete.store');

    // Mark-consumed: closes a single pivot row's lifecycle so it no
    // longer counts against the parent's outstanding total.
    Route::post('{consumable}/consume/{assignment}',
        [Consumables\ConsumableCheckoutController::class, 'consume']
    )->where(['consumable' => '[0-9]+', 'assignment' => '[0-9]+'])
        ->name('consumables.consume');

    Route::post('bulkconsume',
        [Consumables\ConsumableCheckoutController::class, 'bulkConsumeShow']
    )->name('consumables.bulkconsume.show')
        ->breadcrumbs(function (Trail $trail) {
            return $trail->parent('consumables.index')->push(trans('admin/consumables/general.bulk_consume'), route('consumables.index'));
        });

    Route::post('bulkconsume/store',
        [Consumables\ConsumableCheckoutController::class, 'bulkConsumeStore']
    )->name('consumables.bulkconsume.store');

});

Route::resource('consumables', Consumables\ConsumablesController::class, [
    'middleware' => ['auth'],
    'parameters' => ['consumable' => 'consumable_id'],
])->withTrashed(['show']);
