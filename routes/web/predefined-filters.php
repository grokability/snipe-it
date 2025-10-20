<?php

use App\Http\Controllers\PredefinedFilterController;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::group([ 'prefix' => 'predefined_filters','middleware' => ['auth'] ], function () {
    Route::get('predefined-filters', [PredefinedFilterController::class, 'index'])->name('predefined-filters.index');
    Route::get('predefined-filters/{filter}', [PredefinedFilterController::class,'view'])
          ->name('predefined-filters.view');
    Route::delete('predefined-filters/{id}', [PredefinedFilterController::class, 'destroy'])->name('predefined-filters.destroy');
});

Route::resource('predefined-filters', PredefinedFilterController::class,
    [
        'middleware' => ['auth'],
        'except' => ['show']
    ]
);


