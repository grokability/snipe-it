<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FOM API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api/v1/fom and use Passport auth.
| These are placeholder routes — API controllers will be added as each
| sub-module is implemented.
|
*/

Route::prefix('api/v1/fom')->middleware(['api', 'auth:api'])->group(function () {

    // ── Work Orders ──
    Route::prefix('work-orders')->group(function () {
        Route::get('/',                    function () { return response()->json(['status' => 'ok', 'module' => 'work-orders', 'message' => 'Yapım aşamasında']); })->name('api.fom.work-orders.index');
        Route::post('/',                   function () { return response()->json(['status' => 'ok', 'message' => 'Placeholder']); })->name('api.fom.work-orders.store');
        Route::get('/{id}',                function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.work-orders.show');
        Route::patch('/{id}',              function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.work-orders.update');
        Route::post('/{id}/parts',         function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.work-orders.parts');
        Route::get('/{id}/timeline',       function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.work-orders.timeline');
        Route::post('/scan/{asset_tag}',   function ($asset_tag) { return response()->json(['status' => 'ok', 'asset_tag' => $asset_tag]); })->name('api.fom.work-orders.scan');
    });

    // ── Shift Handovers ──
    Route::prefix('shift-handovers')->group(function () {
        Route::get('/',               function () { return response()->json(['status' => 'ok', 'module' => 'shift-handovers']); })->name('api.fom.shift-handovers.index');
        Route::post('/',              function () { return response()->json(['status' => 'ok']); })->name('api.fom.shift-handovers.store');
        Route::get('/current',        function () { return response()->json(['status' => 'ok']); })->name('api.fom.shift-handovers.current');
        Route::get('/pending',        function () { return response()->json(['status' => 'ok']); })->name('api.fom.shift-handovers.pending');
        Route::get('/{id}',           function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.shift-handovers.show');
        Route::patch('/{id}/accept',  function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.shift-handovers.accept');
    });

    // ── Spare Parts ──
    Route::prefix('spare-parts')->group(function () {
        Route::get('/',                       function () { return response()->json(['status' => 'ok', 'module' => 'spare-parts']); })->name('api.fom.spare-parts.index');
        Route::post('/',                      function () { return response()->json(['status' => 'ok']); })->name('api.fom.spare-parts.store');
        Route::get('/low-stock',              function () { return response()->json(['status' => 'ok']); })->name('api.fom.spare-parts.low-stock');
        Route::get('/for-model/{model_id}',   function ($model_id) { return response()->json(['status' => 'ok', 'model_id' => $model_id]); })->name('api.fom.spare-parts.for-model');
        Route::get('/{id}',                   function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.spare-parts.show');
        Route::patch('/{id}',                 function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.spare-parts.update');
        Route::post('/{id}/stock-in',         function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.spare-parts.stock-in');
        Route::post('/{id}/stock-out',        function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.spare-parts.stock-out');
        Route::get('/{id}/movements',         function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.spare-parts.movements');
    });

    // ── Purchase Requests ──
    Route::prefix('purchase-requests')->group(function () {
        Route::get('/',                function () { return response()->json(['status' => 'ok', 'module' => 'purchase-requests']); })->name('api.fom.purchase-requests.index');
        Route::post('/',               function () { return response()->json(['status' => 'ok']); })->name('api.fom.purchase-requests.store');
        Route::patch('/{id}',          function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.purchase-requests.update');
        Route::post('/{id}/receive',   function ($id) { return response()->json(['status' => 'ok', 'id' => $id]); })->name('api.fom.purchase-requests.receive');
    });

    // ── Dashboard ──
    Route::prefix('dashboard')->group(function () {
        Route::get('/summary',       function () { return response()->json(['status' => 'ok', 'module' => 'dashboard']); })->name('api.fom.dashboard.summary');
        Route::get('/mttr',          function () { return response()->json(['status' => 'ok']); })->name('api.fom.dashboard.mttr');
        Route::get('/shift-status',  function () { return response()->json(['status' => 'ok']); })->name('api.fom.dashboard.shift-status');
    });
});
