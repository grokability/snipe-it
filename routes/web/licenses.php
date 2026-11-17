<?php

use App\Http\Controllers\Licenses;
use App\Models\License;
use App\Models\LicenseSeat;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

// Licenses
Route::group(['prefix' => 'licenses', 'middleware' => ['auth']], function () {
    Route::get('{licenseId}/clone', [Licenses\LicensesController::class, 'getClone'])
        ->where('licenseId', '[0-9]+')
        ->name('clone/license');

    Route::get('{license}/checkout/{seatId?}', [Licenses\LicenseCheckoutController::class, 'create'])
        ->where(['license' => '[0-9]+', 'seatId' => '[0-9]+'])
        ->name('licenses.checkout')
        ->breadcrumbs(fn (Trail $trail, License $license) => $trail->parent('licenses.show', $license)
            ->push(trans('general.checkout'), route('licenses.checkout', $license))
        );

    Route::post(
        '{licenseId}/checkout/{seatId?}',
        [Licenses\LicenseCheckoutController::class, 'store']
    )->where(['licenseId' => '[0-9]+', 'seatId' => '[0-9]+'])
        ->name('licenses.checkout.save');

    Route::get('{licenseSeat}/checkin/{backto?}', [Licenses\LicenseCheckinController::class, 'create'])
        ->where('licenseSeat', '[0-9]+')
        ->name('licenses.checkin')
        ->breadcrumbs(fn (Trail $trail, LicenseSeat $licenseSeat) => $trail->parent('licenses.show', $licenseSeat->license)
            ->push(trans('general.checkin'), route('licenses.checkin', $licenseSeat))
        );

    Route::post('{licenseId}/checkin/{backto?}',
        [Licenses\LicenseCheckinController::class, 'store']
    )->where('licenseId', '[0-9]+')
        ->name('licenses.checkin.save');

    Route::post(
        '{licenseId}/bulkcheckin',
        [Licenses\LicenseCheckinController::class, 'bulkCheckin']
    )->where('licenseId', '[0-9]+')
        ->name('licenses.bulkcheckin');

    // Bulk check-in from the hardware or user show page's licenses
    // tab. The `.show` route renders the confirmation interstitial
    // where the admin picks the shared note. `.store` applies the
    // checkin.
    Route::post(
        'bulkcheckin/show',
        [Licenses\LicenseCheckinController::class, 'bulkCheckinShow']
    )->name('licenses.bulkcheckin.show');

    Route::post(
        'bulkcheckin/store',
        [Licenses\LicenseCheckinController::class, 'bulkCheckinStore']
    )->name('licenses.bulkcheckin.store');

    Route::post(
        '{licenseId}/bulkcheckout',
        [Licenses\LicenseCheckoutController::class, 'bulkCheckout']
    )->where('licenseId', '[0-9]+')
        ->name('licenses.bulkcheckout');

    // Bulk-fulfill queue for this license. Distinct from
    // bulkcheckout above (which assigns free seats to every user
    // in the org). See sibling accessories.fulfill-requests.* for
    // the shared per-row shape and controller semantics.
    Route::get('{license}/fulfill-requests',
        [Licenses\LicenseCheckoutController::class, 'bulkFulfillCreate']
    )->where('license', '[0-9]+')
        ->name('licenses.fulfill-requests.create')
        ->breadcrumbs(fn (Trail $trail, License $license) => $trail
            ->parent('requests.index')
            ->push($license->name, route('licenses.show', $license))
            ->push(trans('general.checkout'), route('licenses.fulfill-requests.create', $license))
        );

    Route::post('{license}/fulfill-requests',
        [Licenses\LicenseCheckoutController::class, 'bulkFulfillStore']
    )->where('license', '[0-9]+')
        ->name('licenses.fulfill-requests.store');

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
