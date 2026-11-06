<?php

use App\Http\Controllers\CustomFieldsController;
use App\Http\Controllers\CustomFieldsetsController;
use App\Livewire\CustomFieldEditor;
use App\Models\CustomFieldset;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

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
    )->name('fieldsets.edit.show')
        ->breadcrumbs(fn (Trail $trail, CustomFieldset $fieldset) => $trail
            ->parent('fields.index')
            ->push($fieldset->name, route('fieldsets.show', $fieldset))
            ->push(trans('general.update'), route('fieldsets.edit.show', $fieldset))
        );

    Route::get(
        'fieldsets/{fieldset}',
        [CustomFieldsetsController::class, 'show']
    )->name('fieldsets.show')
        ->breadcrumbs(fn (Trail $trail, CustomFieldset $fieldset) => $trail
            ->parent('fields.index')
            ->push($fieldset->name, route('fieldsets.show', $fieldset))
        );

});

Route::group(['middleware' => ['auth']], function () {
    // Create/edit are handled by the CustomFieldEditor Livewire component
    // as full-page routes — mount() applies the create/update policies.
    Route::get('fields/create', CustomFieldEditor::class)->name('fields.create');
    Route::get('fields/{field}/edit', CustomFieldEditor::class)->name('fields.edit');

    // index + destroy remain on the controller (the Livewire component
    // handles saves via wire:submit -> save(), so store/update are gone).
    Route::get('fields', [CustomFieldsController::class, 'index'])
        ->name('fields.index')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->push(trans('admin/custom_fields/general.manage').' '.trans('admin/custom_fields/general.custom_fields'), route('fields.index'))
        );
    Route::delete('fields/{field}', [CustomFieldsController::class, 'destroy'])->name('fields.destroy');
});
