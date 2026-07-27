<?php

use App\Http\Controllers\Users;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

// User Management

Route::group(['prefix' => 'users', 'middleware' => ['auth']], function () {

    Route::get(
        'ldap',
        [
            Users\LDAPImportController::class,
            'create',
        ]
    )->name('ldap/user')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('users.index')
            ->push(trans('general.ldap_user_sync'), route('ldap/user')));

    Route::post(
        'ldap',
        [
            Users\LDAPImportController::class,
            'store',
        ]
    );

    Route::get(
        'export',
        [
            Users\UsersController::class,
            'getExportUserCsv',
        ]
    )->name('users.export');

    Route::get(
        '{user}/clone',
        [
            Users\UsersController::class,
            'getClone',
        ]
    )->name('users.clone.show')->withTrashed();

    Route::post(
        '{user}/clone',
        [
            Users\UsersController::class,
            'postCreate',
        ]
    )->name('users.clone.store')->withTrashed();

    Route::post(
        '{user}/restore',
        [
            Users\UsersController::class,
            'getRestore',
        ]
    )->name('users.restore.store')->withTrashed();

    Route::post(
        '{userId}/password',
        [
            Users\UsersController::class,
            'sendPasswordReset',
        ]
    )->name('users.password');

    Route::get(
        '{userId}/print',
        [
            Users\UsersController::class,
            'printInventory',
        ]
    )->name('users.print');

    Route::post(
        '{userId}/email',
        [
            Users\UsersController::class,
            'emailAssetList',
        ]
    )->name('users.email');

    Route::post(
        '{user}/acceptance-reminder',
        [
            Users\UsersController::class,
            'resendAcceptanceReminder',
        ]
    )->name('users.acceptance_reminder')->withTrashed();

    Route::post(
        '{user}/impersonate',
        [
            Users\ImpersonateController::class,
            'start',
        ]
    )->name('users.impersonate.start');

    Route::post(
        'impersonate/stop',
        [
            Users\ImpersonateController::class,
            'stop',
        ]
    )->name('users.impersonate.stop');

    Route::post(
        '{user}/two-factor-reset',
        [
            Users\UsersController::class,
            'twoFactorReset',
        ]
    )->name('users.two_factor_reset');

    Route::post(
        'bulkedit',
        [
            Users\BulkUsersController::class,
            'edit',
        ]
    )->name('users/bulkedit')
        ->breadcrumbs(function (Trail $trail) {
            // Single POST endpoint fans out to several bulk-action confirmation
            // views (edit, delete, merge, print). Pick the breadcrumb label to
            // match the action the caller submitted so the user sees the same
            // wording on the confirmation page and in the crumb.
            $label = match (request()->input('bulk_actions')) {
                'edit' => trans('general.bulk_edit'),
                'delete' => trans('general.bulk_checkin_delete'),
                'merge' => trans('general.merge_users'),
                'print' => trans('admin/users/general.print_assigned'),
                default => trans('general.bulk_actions'),
            };

            return $trail->parent('users.index')->push($label, route('users.index'));
        });

    Route::post(
        'merge',
        [
            Users\BulkUsersController::class,
            'merge',
        ]
    )->name('users.merge.save');

    Route::post(
        'bulksave',
        [
            Users\BulkUsersController::class,
            'destroy',
        ]
    )->name('users/bulksave');

    Route::post(
        'bulkeditsave',
        [
            Users\BulkUsersController::class,
            'update',
        ]
    )->name('users/bulkeditsave');

    Route::get(
        '{user}/transfer',
        [Users\UserItemTransferController::class, 'show'],
    )->name('users.transfer.show')
        ->breadcrumbs(fn (Trail $trail, $user) => $trail
            ->parent('users.show', $user)
            ->push(trans('admin/users/general.transfer.title'), route('users.transfer.show', $user)));

    Route::post(
        '{user}/transfer',
        [Users\UserItemTransferController::class, 'store'],
    )->name('users.transfer.store');

});

Route::resource('users', Users\UsersController::class, [
    'middleware' => ['auth'],
])->withTrashed();
