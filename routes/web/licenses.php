<?php

use App\Http\Controllers\Licenses;
use App\Models\License;
use App\Models\LicenseSeat;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

// Licenses
Route::group(['prefix' => 'licenses', 'middleware' => ['auth']], function () {
    Route::get('{licenseId}/clone', [Licenses\LicensesController::class, 'getClone'])->name('clone/license');

    Route::get('{license}/checkout/{seatId?}', [Licenses\LicenseCheckoutController::class, 'create'])
        ->name('licenses.checkout')
        ->breadcrumbs(fn (Trail $trail, License $license) => $trail->parent('licenses.show', $license)
            ->push(trans('general.checkout'), route('licenses.checkout', $license))
        );

    Route::post(
        '{licenseId}/checkout/{seatId?}',
        [Licenses\LicenseCheckoutController::class, 'store']
    )->name('licenses.checkout.save');

    Route::get('{licenseSeat}/checkin/{backto?}', [Licenses\LicenseCheckinController::class, 'create'])
        ->name('licenses.checkin')
        ->breadcrumbs(fn (Trail $trail, LicenseSeat $licenseSeat) => $trail->parent('licenses.show', $licenseSeat->license)
            ->push(trans('general.checkin'), route('licenses.checkin', $licenseSeat))
        );

    Route::post('{licenseId}/checkin/{backto?}',
        [Licenses\LicenseCheckinController::class, 'store']
    )->name('licenses.checkin.save');

    Route::post(
        '{licenseId}/bulkcheckin',
        [Licenses\LicenseCheckinController::class, 'bulkCheckin']
    )->name('licenses.bulkcheckin');

    Route::post(
        'bulkcheckin/selected',
        [Licenses\LicenseCheckinController::class, 'bulkCheckinSelected']
    )->name('licenses.bulkcheckin.selected');

    Route::post(
        '{licenseId}/bulkcheckout',
        [Licenses\LicenseCheckoutController::class, 'bulkCheckout']
    )->name('licenses.bulkcheckout');

    Route::get(
        'export',
        [
            Licenses\LicensesController::class,
            'getExportLicensesCsv',
        ]
    )->name('licenses.export');

    Route::post('bulk/delete', [Licenses\BulkLicensesController::class, 'destroy'])->name('licenses.bulk.delete');
});

Route::resource('licenses', Licenses\LicensesController::class, [
    'middleware' => ['auth'],
]);
