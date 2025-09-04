<?php

use App\Http\Controllers\CustomFieldsController;
use App\Http\Controllers\CustomFieldsetsController;
use Illuminate\Support\Facades\Route;

/*
* Custom Fields Routes
*/



Route::group([ 'prefix' => 'fields','middleware' => ['auth'] ], function () {

    Route::post(
        'required/{fieldset_id}/{field_id}',
        [CustomFieldsetsController::class, 'makeFieldRequired']
    )->name('fields.required');

    Route::post(
        'optional/{fieldset_id}/{field_id}',
        [CustomFieldsetsController::class, 'makeFieldOptional']
    )->name('fields.optional');

    Route::post(
        '{field_id}/fieldset/{fieldset_id}/disassociate',
        [CustomFieldsController::class, 'deleteFieldFromFieldset']
    )->name('fields.disassociate');

    Route::post(
        'fieldsets/{id}/associate',
        [CustomFieldsetsController::class, 'associate']
    )->name('fieldsets.associate');

    Route::resource('fieldsets', CustomFieldsetsController::class, [
        'parameters' => [
            'fieldset' => 'fieldset',
            'field' => 'field_id'
        ]
    ]);


});

Route::resource('fields', CustomFieldsController::class,
    ['middleware' => ['auth'],
        'except' => ['show']
    ]);

