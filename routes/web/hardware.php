<?php

use App\Http\Controllers\MaintenancesController;
use App\Http\Controllers\Assets\AssetsController;
use App\Http\Controllers\Assets\BulkAssetsController;
use App\Http\Controllers\Assets\AssetCheckoutController;
use App\Http\Controllers\Assets\AssetCheckinController;
use App\Models\Setting;
use Tabuna\Breadcrumbs\Trail;
use Illuminate\Support\Facades\Route;
use App\Models\Asset;

/*
|--------------------------------------------------------------------------
| Asset Routes
|--------------------------------------------------------------------------
|
| Register all the asset routes.
|
*/
Route::group(
    [
        'prefix' => 'hardware',
        'middleware' => ['auth'], 
    ],
    
    function () {
        
        Route::get('bulkaudit', [AssetsController::class, 'quickScan'])
            ->name('assets.bulkaudit')
            ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('hardware.index')
                ->push(trans('general.bulkaudit'), route('asset.import-history'))
            );

        Route::get('quickscancheckin', [AssetsController::class, 'quickScanCheckin'])
            ->name('hardware/quickscancheckin')
            ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('hardware.index')
                ->push('Quickscan Checkin', route('hardware/quickscancheckin'))
            );

        Route::get('requested', [AssetsController::class, 'getRequestedIndex'])
            ->name('assets.requested')
            ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('hardware.index')
                ->push(trans('admin/hardware/general.requested'), route('assets.requested'))
            );

        Route::get('audit/due', [AssetsController::class, 'dueForAudit'])
            ->name('assets.audit.due')
            ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('hardware.index')
                ->push(trans_choice('general.audit_due_days', Setting::getSettings()->audit_warning_days, ['days' => Setting::getSettings()->audit_warning_days]), route('assets.audit.due'))
            );

        Route::get('checkins/due',
            [AssetsController::class, 'dueForCheckin']
        )->name('assets.checkins.due')
            ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('hardware.index')
                ->push(trans_choice('general.checkin_due_days', Setting::getSettings()->due_checkin_days, ['days' => Setting::getSettings()->due_checkin_days]), route('assets.audit.due'))
            );
        
        Route::get('{asset}/audit', [AssetsController::class, 'audit'])
            ->name('asset.audit.create')
            ->breadcrumbs(fn (Trail $trail, Asset $asset) =>
            $trail->parent('hardware.show', $asset)
                ->push(trans('general.audit'))
            );

        Route::post('{asset}/audit',
            [AssetsController::class, 'auditStore']
        )->name('asset.audit.store');

        Route::get('history', [AssetsController::class, 'getImportHistory'])
            ->name('asset.import-history')
            ->breadcrumbs(fn (Trail $trail) =>
                $trail->parent('hardware.index')
                ->push(trans('general.import-history'), route('asset.import-history'))
            );

        Route::post('history',
            [AssetsController::class, 'postImportHistory']
        )->name('asset.process-import-history');

        Route::get('bytag/{any?}',
            [AssetsController::class, 'getAssetByTag']
        )->where('any', '.*')->name('findbytag/hardware');

        Route::get('byserial/{any?}',
            [AssetsController::class, 'getAssetBySerial']
        )->where('any', '.*')->name('findbyserial/hardware');

        Route::get('{asset}/clone',
            [AssetsController::class, 'getClone']
        )->name('clone/hardware')->withTrashed();

        Route::get('{assetId}/label',
            [AssetsController::class, 'getLabel']
        )->name('label/hardware');

        Route::get('{asset}/checkout', [AssetCheckoutController::class, 'create'])
            ->name('hardware.checkout.create')
            ->breadcrumbs(fn (Trail $trail, Asset $asset) =>
            $trail->parent('hardware.show', $asset)
                ->push(trans('admin/hardware/general.checkout'), route('hardware.index'))
            );

        Route::post('{assetId}/checkout',
            [AssetCheckoutController::class, 'store']
        )->name('hardware.checkout.store');

        Route::get('{asset}/checkin/{backto?}',
            [AssetCheckinController::class, 'create']
        )->name('hardware.checkin.create')
        ->breadcrumbs(fn (Trail $trail, Asset $asset) =>
        $trail->parent('hardware.show', $asset)
            ->push(trans('admin/hardware/general.checkin'), route('hardware.index'))
        );

        Route::post('{assetId}/checkin/{backto?}',
            [AssetCheckinController::class, 'store']
        )->name('hardware.checkin.store');

        // Redirect old legacy /asset_id/view urls to the resource route version
        Route::get('{assetId}/view', function ($assetId) {
            return redirect()->route('hardware.show', $assetId);
        });

        Route::get('{asset}/qr_code',
            [AssetsController::class, 'getQrCode']
        )->name('qr_code/hardware')->withTrashed();

        Route::get('{asset}/barcode',
            [AssetsController::class, 'getBarCode']
        )->name('barcode/hardware')->withTrashed();

        Route::post('{asset}/restore',
            [AssetsController::class, 'getRestore']
        )->name('restore/hardware')->withTrashed();

        Route::post(
            'bulkedit',
            [BulkAssetsController::class, 'edit']
        )->name('hardware/bulkedit');

        Route::post(
            'bulkdelete',
            [BulkAssetsController::class, 'destroy']
        )->name('hardware/bulkdelete');

        Route::post(
            'bulkrestore',
            [BulkAssetsController::class, 'restore']
        )->name('hardware/bulkrestore');

        Route::post(
            'bulksave',
            [BulkAssetsController::class, 'update']
        )->name('hardware/bulksave');

        // Bulk checkout / checkin
        Route::get('bulkcheckout', [BulkAssetsController::class, 'showCheckout'])
            ->name('hardware.bulkcheckout.show')
            ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('hardware.index')
                ->push(trans('admin/hardware/general.bulk_checkout'), route('hardware.index'))
            );

        Route::post('bulkcheckout',
            [BulkAssetsController::class, 'storeCheckout']
        )->name('hardware.bulkcheckout.store');

    });

Route::resource('hardware',
        AssetsController::class,
        ['middleware' => ['auth']
])->parameters(['hardware' => 'asset'])->withTrashed();


// Asset Maintenances
Route::resource('maintenances',
    MaintenancesController::class, [
        'parameters' => ['maintenance' => 'maintenance', 'asset' => 'asset_id'],
    ]);

Route::get('ht/{any?}',
    [AssetsController::class, 'getAssetByTag']
)->where('any', '.*')->name('ht/assetTag');
