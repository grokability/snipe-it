<?php

use App\Http\Controllers\PredefinedFilterController;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::middleware(['auth'])->group(function () {
    Route::get('predefined-filters', [PredefinedFilterController::class, 'index'])->name('predefined-filters.index');
    Route::get('predefined-filters/{filter}', [PredefinedFilterController::class,'view'])
          ->name('predefined-filters.view');
    Route::delete('predefined-filters/{id}', [PredefinedFilterController::class, 'destroy'])->name('predefined-filters.destroy');
});
