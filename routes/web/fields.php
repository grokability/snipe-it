<?php

use App\Http\Controllers\CustomFieldsController;
use App\Http\Controllers\CustomFieldsetsController;
use App\Livewire\CustomFieldEditor;
use Illuminate\Support\Facades\Route;

/*
* Custom Fields Routes
*/

Route::group(['prefix' => 'fields', 'middleware' => ['auth']], function () {

    Route::post(
        'required/{fieldset_id}/{field_id}',
        [CustomFieldsetsController::class, 'makeFieldRequired']
    )->where(['fieldset_id' => '[0-9]+', 'field_id' => '[0-9]+'])
        ->name('fields.required');

    Route::post(
        'optional/{fieldset_id}/{field_id}',
        [CustomFieldsetsController::class, 'makeFieldOptional']
    )->where(['fieldset_id' => '[0-9]+', 'field_id' => '[0-9]+'])
        ->name('fields.optional');

    Route::post(
        'group/{fieldset_id}/{field_id}',
        [CustomFieldsetsController::class, 'updateGroup']
    )->where(['fieldset_id' => '[0-9]+', 'field_id' => '[0-9]+'])
        ->name('fields.group');

    Route::post(
        '{field_id}/fieldset/{fieldset_id}/disassociate',
        [CustomFieldsController::class, 'deleteFieldFromFieldset']
    )->where(['field_id' => '[0-9]+', 'fieldset_id' => '[0-9]+'])
        ->name('fields.disassociate');

    Route::post(
        'fieldsets/{id}/associate',
        [CustomFieldsetsController::class, 'associate']
    )->where('id', '[0-9]+')
        ->name('fieldsets.associate');

    Route::resource('fieldsets', CustomFieldsetsController::class, [
        'parameters' => [
            'fieldset' => 'fieldset',
            'field' => 'field_id',
        ],
        'except' => ['show', 'view'],
    ]);

    // This is a shim to handle bootstrap tables
    // @todo: normalize this in the JS
    Route::get(
        'fieldsets/{fieldset}/edit',
        [CustomFieldsetsController::class, 'show']
    )->name('fieldsets.edit.show');

    Route::get(
        'fieldsets/{fieldset}',
        [CustomFieldsetsController::class, 'show']
    )->name('fieldsets.show');

});

Route::group(['middleware' => ['auth']], function () {
    // Create/edit are handled by the CustomFieldEditor Livewire component
    // as full-page routes — mount() applies the create/update policies.
    Route::get('fields/create', CustomFieldEditor::class)->name('fields.create');
    Route::get('fields/{field}/edit', CustomFieldEditor::class)->name('fields.edit');

    // index + destroy remain on the controller (the Livewire component
    // handles saves via wire:submit -> save(), so store/update are gone).
    Route::get('fields', [CustomFieldsController::class, 'index'])->name('fields.index');
    Route::delete('fields/{field}', [CustomFieldsController::class, 'destroy'])->name('fields.destroy');
});
