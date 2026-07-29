<?php

use App\Http\Controllers\Assets\AssetCheckinController;
use App\Http\Controllers\Assets\AssetCheckoutController;
use App\Http\Controllers\Assets\AssetsController;
use App\Http\Controllers\Assets\BulkAssetsController;
use App\Http\Controllers\BulkMaintenancesController;
use App\Http\Controllers\MaintenancesController;
use App\Models\Asset;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

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
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('hardware.index')
                ->push(trans('general.bulkaudit'), route('assets.bulkaudit'))
            );

        Route::get('quickscancheckin', [AssetsController::class, 'quickScanCheckin'])
            ->name('hardware/quickscancheckin')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('hardware.index')
                ->push('Quickscan Checkin', route('hardware/quickscancheckin'))
            );

        Route::get('requested', [AssetsController::class, 'getRequestedIndex'])
            ->name('assets.requested')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('hardware.index')
                ->push(trans('admin/hardware/general.requested'), route('assets.requested'))
            );

        Route::get('audit/due', [AssetsController::class, 'dueForAudit'])
            ->name('assets.audit.due')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('hardware.index')
                ->push(trans_choice('general.audit_due_days', Setting::getSettings()->audit_warning_days, ['days' => Setting::getSettings()->audit_warning_days]), route('assets.audit.due'))
            );

        Route::get('checkins/due',
            [AssetsController::class, 'dueForCheckin']
        )->name('assets.checkins.due')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('hardware.index')
                ->push(trans_choice('general.checkin_due_days', Setting::getSettings()->due_checkin_days, ['days' => Setting::getSettings()->due_checkin_days]), route('assets.audit.due'))
            );

        Route::get('{asset}/audit', [AssetsController::class, 'audit'])
            ->name('asset.audit.create')
            ->breadcrumbs(fn (Trail $trail, Asset $asset) => $trail->parent('hardware.show', $asset)
                ->push(trans('general.audit'))
            );

        Route::post('{asset}/audit',
            [AssetsController::class, 'auditStore']
        )->name('asset.audit.store');

        Route::post('{asset}/forcecheckin',
            [AssetCheckinController::class, 'forceCheckin']
        )->name('asset.checkin.force');

        // Legacy import-history endpoint. The dedicated `/hardware/history`
        // controller + form was folded into the main Livewire importer
        // (import type "assetHistory"). Keep the route name so any external
        // bookmark or deep-link still lands somewhere sane.
        Route::get('history', fn () => redirect()->route('imports.index'))
            ->name('asset.import-history');

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
            ->breadcrumbs(fn (Trail $trail, Asset $asset) => $trail->parent('hardware.show', $asset)
                ->push(trans('admin/hardware/general.checkout'), route('hardware.index'))
            );

        Route::post('{assetId}/checkout',
            [AssetCheckoutController::class, 'store']
        )->name('hardware.checkout.store');

        Route::get('{asset}/checkin/{backto?}',
            [AssetCheckinController::class, 'create']
        )->name('hardware.checkin.create')->withTrashed()
            ->breadcrumbs(fn (Trail $trail, Asset $asset) => $trail->parent('hardware.show', $asset)
                ->push(trans('admin/hardware/general.checkin'), route('hardware.index'))
            );

        Route::post('{assetId}/checkin/{backto?}',
            [AssetCheckinController::class, 'store']
        )->name('hardware.checkin.store');

        // Redirect old legacy /asset_id/view urls to the resource route version
        Route::get('{assetId}/view', function ($assetId) {
            return redirect()->route('hardware.show', $assetId);
        });

        Route::get('{asset}/barcode',
            [AssetsController::class, 'getBarCode']
        )->name('barcode/hardware')->withTrashed();

        Route::post('{asset}/restore',
            [AssetsController::class, 'getRestore']
        )->name('restore/hardware')->withTrashed();

        Route::post(
            'bulkedit',
            [BulkAssetsController::class, 'edit']
        )->name('hardware.bulkedit.show')
            ->breadcrumbs(function (Trail $trail) {
                // Single POST endpoint fans out to several bulk-action
                // confirmation views (edit, delete, restore). Pick the
                // breadcrumb label to match the action the caller
                // submitted so the crumb matches the confirmation heading.
                // Other bulk_actions values on this route (labels renders a
                // PDF; checkout / checkin / maintenance redirect away) never
                // reach a template that renders breadcrumbs.
                $label = match (request()->input('bulk_actions')) {
                    'edit' => trans('general.bulk_edit'),
                    'delete' => trans('general.bulk_delete'),
                    'restore' => trans('general.bulk_restore'),
                    default => trans('general.bulk_actions'),
                };

                return $trail->parent('hardware.index')->push($label, route('hardware.index'));
            });

        Route::post(
            'bulkdelete',
            [BulkAssetsController::class, 'destroy']
        )->name('hardware.bulkdelete.store');

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
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('hardware.index')
                ->push(trans('admin/hardware/general.bulk_checkout'), route('hardware.index'))
            );

        Route::post('bulkcheckout',
            [BulkAssetsController::class, 'storeCheckout']
        )->name('hardware.bulkcheckout.store');

        Route::get('bulkcheckin', [BulkAssetsController::class, 'showCheckin'])
            ->name('hardware.bulkcheckin.show')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('hardware.index')
                ->push(trans('admin/hardware/general.bulk_checkin'), route('hardware.index'))
            );

        Route::post('bulkcheckin',
            [BulkAssetsController::class, 'storeCheckin']
        )->name('hardware.bulkcheckin.store');

        Route::get('bulklabels', [BulkAssetsController::class, 'showLabels'])
            ->name('hardware.bulk_labels.show')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('hardware.index')
                ->push(trans('general.bulk_labels'), route('hardware.index'))
        );

        Route::post('bulklabels',
            [BulkAssetsController::class, 'printLabels']
        )->name('hardware.bulk_labels.print');
    });

Route::resource('hardware',
    AssetsController::class,
    ['middleware' => ['auth'],
    ])->parameters(['hardware' => 'asset'])->withTrashed();

// Asset Maintenances
Route::resource('maintenances',
    MaintenancesController::class,
    ['middleware' => ['auth'],
    ])->parameters(['maintenance' => 'maintenance', 'asset' => 'asset_id']);

Route::post('maintenances/{maintenance}/complete',
    [MaintenancesController::class, 'complete']
)->name('maintenances.complete')->middleware(['auth']);

Route::post('maintenances/bulk',
    [BulkMaintenancesController::class, 'store']
)->name('maintenances.bulk')->middleware(['auth']);

Route::get('ht/{any?}',
    [AssetsController::class, 'getAssetByTag'])
    ->where('any', '.*')
    ->name('ht/assetTag');
