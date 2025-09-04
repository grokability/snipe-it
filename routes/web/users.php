<?php

use App\Http\Controllers\Users;
use Illuminate\Support\Facades\Route;

// User Management

Route::group(['prefix' => 'users', 'middleware' => ['auth']], function () {

    Route::get(
        'ldap',
        [
            Users\LDAPImportController::class, 
            'create'
        ]
    )->name('ldap/user');

    Route::post(
        'ldap',
        [
            Users\LDAPImportController::class, 
            'store'
        ]
    );

    Route::get(
        'export',
        [
            Users\UsersController::class, 
            'getExportUserCsv'
        ]
    )->name('users.export');

    Route::get(
        '{user}/clone',
        [
            Users\UsersController::class, 
            'getClone'
        ]
    )->name('users.clone.show')->withTrashed();

    Route::post(
        '{user}/clone',
        [
            Users\UsersController::class, 
            'postCreate'
        ]
    )->name('users.clone.store')->withTrashed();

    Route::post(
        '{userId}/restore',
        [
            Users\UsersController::class, 
            'getRestore'
        ]
    )->name('users.restore.store');

    Route::get(
        '{userId}/unsuspend',
        [
            Users\UsersController::class, 
            'getUnsuspend'
        ]
    )->name('unsuspend/user');

    Route::post(
        '{userId}/password',
        [
            Users\UsersController::class, 
            'sendPasswordReset'
        ]
    )->name('users.password');

    Route::get(
        '{userId}/print',
        [
            Users\UsersController::class, 
            'printInventory'
        ]
    )->name('users.print');

    Route::post(
        '{userId}/email',
        [
            Users\UsersController::class,
            'emailAssetList'
        ]
    )->name('users.email');

    Route::post(
        'bulkedit',
        [
            Users\BulkUsersController::class, 
            'edit'
        ]
    )->name('users/bulkedit');

    Route::post(
        'merge',
        [
            Users\BulkUsersController::class,
            'merge'
        ]
    )->name('users.merge.save');


    Route::post(
        'bulksave',
        [
            Users\BulkUsersController::class, 
            'destroy'
        ]
    )->name('users/bulksave');

    Route::post(
        'bulkeditsave',
        [
            Users\BulkUsersController::class, 
            'update'
        ]
    )->name('users/bulkeditsave');

});

Route::resource('users', Users\UsersController::class, [
    'middleware' => ['auth']
])->withTrashed();
