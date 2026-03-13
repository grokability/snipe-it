<?php

use App\Http\Controllers\Components;
use Illuminate\Support\Facades\Route;

// Components
Route::group(['prefix' => 'components', 'middleware' => ['auth']], function () {
    Route::get(
        '{componentID}/checkout',
        [Components\ComponentCheckoutController::class, 'create']
    )->name('components.checkout.show');

    Route::post(
        '{componentID}/checkout',
        [Components\ComponentCheckoutController::class, 'store']
    )->name('components.checkout.store');

    Route::get(
        '{componentID}/checkin/{backto?}',
        [Components\ComponentCheckinController::class, 'create']
    )->name('components.checkin.show');

    Route::post(
        '{componentID}/checkin/{backto?}',
        [Components\ComponentCheckinController::class, 'store']
    )->name('components.checkin.store');

    Route::get('{component}/clone',
        [Components\ComponentsController::class, 'getClone']
    )->name('components.clone.create');



});

Route::resource('components', Components\ComponentsController::class, [
    'middleware' => ['auth'],
    'parameters' => ['component' => 'component_id'],
]);
