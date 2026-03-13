<?php

use App\Http\Controllers\Kits;
use App\Models\PredefinedKit;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::group(['prefix' => 'kits/{kit}', 'middleware' => ['auth']], function () {

    // Route::get('licenses',
    //     [Kits\PredefinedKitsController::class, 'indexLicenses']
    // )->name('kits.licenses.index');

    Route::put('licenses',
        [Kits\PredefinedKitsController::class, 'storeLicense']
    )->name('kits.licenses.store');

    Route::put('licenses/{license_id}',
        [Kits\PredefinedKitsController::class, 'updateLicense']
    )->name('kits.licenses.update');

    Route::get('licenses/{license_id}/edit', [Kits\PredefinedKitsController::class, 'editLicense'])
        ->name('kits.licenses.edit')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.backups'), route('kits.licenses.edit')));

    Route::delete('licenses/{license_id}',
        [Kits\PredefinedKitsController::class, 'detachLicense']
    )->name('kits.licenses.detach');

    // Models

    Route::put('models/{model_id}',
        [Kits\PredefinedKitsController::class, 'updateModel']
    )->name('kits.models.update');

    Route::get('models/{model_id}/edit',
        [Kits\PredefinedKitsController::class, 'editModel']
    )->name('kits.models.edit');

    Route::delete('models/{model_id}',
        [Kits\PredefinedKitsController::class, 'detachModel']
    )->name('kits.models.detach');

    // Consumables
    Route::put('consumables/{consumable_id}',
        [Kits\PredefinedKitsController::class, 'updateConsumable']
    )/*->parameters([2 => 'kit_id', 1 => 'consumable_id'])*/->name('kits.consumables.update');

    Route::get('consumables/{consumable_id}/edit',
        [Kits\PredefinedKitsController::class, 'editConsumable']
    )->name('kits.consumables.edit');

    Route::delete('consumables/{consumable_id}',
        [Kits\PredefinedKitsController::class, 'detachConsumable']
    )->name('kits.consumables.detach');

    // Accessories
    Route::put('accessories/{accessory_id}',
        [Kits\PredefinedKitsController::class, 'updateAccessory']
    )/*->parameters([2 => 'kit_id', 1 => 'accessory_id'])*/->name('kits.accessories.update');

    Route::get('accessories/{accessory_id}/edit', [Kits\PredefinedKitsController::class, 'editAccessory'])
        ->name('kits.accessories.edit');

    Route::delete('accessories/{accessory_id}', [Kits\PredefinedKitsController::class, 'detachAccessory'])
        ->name('kits.accessories.detach');

    Route::get('checkout', [Kits\CheckoutKitController::class, 'showCheckout'])
        ->name('kits.checkout.show')
        ->breadcrumbs(fn (Trail $trail, PredefinedKit $kit) =>
        $trail->parent('kits.show', $kit)
            ->push(trans('general.checkout'), route('kits.checkout.show', $kit)));

    Route::post('checkout', [Kits\CheckoutKitController::class, 'store'])
        ->name('kits.checkout.store');
}); // kits

// Predefined Kit Management
Route::resource('kits', Kits\PredefinedKitsController::class, [
    'middleware' => ['auth'],
]);

