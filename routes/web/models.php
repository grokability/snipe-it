<?php

use App\Http\Controllers\AssetModelsController;
use App\Http\Controllers\BulkAssetModelsController;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

// Asset Model Management


Route::group(['prefix' => 'models', 'middleware' => ['auth']], function () {

    Route::get(
        '{model}/clone',
        [
            AssetModelsController::class, 
            'getClone'
        ]
    )->name('models.clone.create')->withTrashed()
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('models.index')
            ->push(trans('admin/models/table.clone'), route('models.index')));

    Route::post(
        '{model}/clone',
        [
            AssetModelsController::class, 
            'postCreate'
        ]
    )->name('models.clone.store')->withTrashed();

    Route::get(
        '{modelId}/view',
        [
            AssetModelsController::class, 
            'getView'
        ]
    )->name('view/model');

    Route::post(
        '{modelID}/restore',
        [
            AssetModelsController::class, 
            'getRestore'
        ]
    )->name('models.restore.store');

    Route::get(
        '{modelId}/custom_fields',
        [
            AssetModelsController::class, 
            'getCustomFields'
        ]
    )->name('custom_fields/model');

    Route::post(
        'bulkedit',
        [
            BulkAssetModelsController::class, 
            'edit'
        ]
    )->name('models.bulkedit.index');

    Route::post(
        'bulksave',
        [
            BulkAssetModelsController::class, 
            'update'
        ]
    )->name('models.bulkedit.store');

    Route::post(
        'bulkdelete',
        [
            BulkAssetModelsController::class, 
            'destroy'
        ]
    )->name('models.bulkdelete.store');



});

Route::resource('models', AssetModelsController::class, [
    'middleware' => ['auth'],
])->withTrashed();
