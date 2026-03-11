<?php

use App\Custom\Http\Controllers\DashboardController;
use App\Custom\Http\Controllers\PurchaseRequestController;
use App\Custom\Http\Controllers\ShiftHandoverController;
use App\Custom\Http\Controllers\SparePartController;
use App\Custom\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FOM Web Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /fom and use web middleware + auth.
|
*/

Route::prefix('fom')->middleware(['web', 'auth'])->group(function () {

    // ── Dashboard ──
    Route::get('/', [DashboardController::class, 'index'])->name('fom.dashboard');

    // ── Work Orders ──
    Route::prefix('work-orders')->name('fom.work-orders.')->group(function () {
        Route::get('/',        [WorkOrderController::class, 'index'])->name('index');
        Route::get('/create',  [WorkOrderController::class, 'create'])->name('create');
        Route::get('/board',   [WorkOrderController::class, 'board'])->name('board');
        Route::post('/',       [WorkOrderController::class, 'store'])->name('store');
        Route::get('/{id}',    [WorkOrderController::class, 'show'])->name('show');
        Route::patch('/{id}',  [WorkOrderController::class, 'update'])->name('update');
    });

    // ── Shift Handover ──
    Route::prefix('shift')->name('fom.shift.')->group(function () {
        Route::get('/history',       [ShiftHandoverController::class, 'index'])->name('history');
        Route::get('/handover',      [ShiftHandoverController::class, 'create'])->name('handover');
        Route::post('/handover',     [ShiftHandoverController::class, 'store'])->name('store');
        Route::get('/accept/{id}',   [ShiftHandoverController::class, 'accept'])->name('accept');
        Route::patch('/accept/{id}', [ShiftHandoverController::class, 'update'])->name('update');
    });

    // ── Spare Parts ──
    Route::prefix('spare-parts')->name('fom.spare-parts.')->group(function () {
        Route::get('/',                  [SparePartController::class, 'index'])->name('index');
        Route::get('/create',            [SparePartController::class, 'create'])->name('create');
        Route::get('/low-stock',         [SparePartController::class, 'lowStock'])->name('low-stock');
        Route::post('/',                 [SparePartController::class, 'store'])->name('store');
        Route::get('/{id}',             [SparePartController::class, 'show'])->name('show');
        Route::get('/{id}/stock-in',    [SparePartController::class, 'stockIn'])->name('stock-in');
        Route::post('/{id}/stock-in',   [SparePartController::class, 'storeStockIn'])->name('stock-in.store');
    });

    // ── Purchase Requests ──
    Route::prefix('purchase-requests')->name('fom.purchase-requests.')->group(function () {
        Route::get('/',                  [PurchaseRequestController::class, 'index'])->name('index');
        Route::get('/{id}',             [PurchaseRequestController::class, 'show'])->name('show');
        Route::patch('/{id}',           [PurchaseRequestController::class, 'update'])->name('update');
        Route::post('/{id}/receive',    [PurchaseRequestController::class, 'receive'])->name('receive');
    });
});
