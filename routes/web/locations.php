<?php

use App\Http\Controllers\LocationsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'locations', 'middleware' => ['auth']], function () {

    Route::post(
    'bulkdelete',
    [LocationsController::class, 'postBulkDelete']
    )->name('locations.bulkdelete.show');

    Route::post(
    'bulkedit',
    [LocationsController::class, 'postBulkDeleteStore']
    )->name('locations.bulkdelete.store');

    Route::post(
    '{location}/restore',
    [LocationsController::class, 'postRestore']
    )->name('locations.restore');

    Route::get('{locationId}/clone',
    [LocationsController::class, 'getClone']
    )->name('clone/location');

    Route::get(
    '{locationId}/printassigned',
    [LocationsController::class, 'print_assigned']
    )->name('locations.print_assigned');

    Route::get(
    '{locationId}/printallassigned',
    [LocationsController::class, 'print_all_assigned']
    )->name('locations.print_all_assigned');

});

Route::resource('locations', LocationsController::class, [
    'middleware' => ['auth'],
])->withTrashed();
