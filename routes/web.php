<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\ActionlogController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BulkCategoriesController;
use App\Http\Controllers\BulkManufacturersController;
use App\Http\Controllers\BulkSuppliersController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DepreciationsController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\LabelsController;
use App\Http\Controllers\UploadedFilesController;
use App\Http\Controllers\ManufacturersController;
use App\Http\Controllers\ModalController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportTemplatesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatuslabelsController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\ViewAssetsController;
use App\Livewire\Importer;
use App\Models\ReportTemplate;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::group(['middleware' => 'auth'], function () {
    /*
    * Companies
    */
    Route::resource('companies', CompaniesController::class, [
        'parameters' => ['company' => 'company_id'],
    ]);

    /*
    * Categories
    */
    Route::resource('categories', CategoriesController::class, [
        'parameters' => ['category' => 'category_id'],
    ]);

    Route::post('categories/bulk/delete', [BulkCategoriesController::class, 'destroy'])->name('categories.bulk.delete');
  
    /*
    * Labels
    */
    Route::get(
        'labels/{labelName}',
        [LabelsController::class, 'show']
    )->where('labelName', '.*')->name('labels.show');

    Route::get('/test-email', function () {
        $mailable = new \App\Mail\CheckoutComponentMail(

        );
        return $mailable->render(); // dumps HTML
    });
    /*
    * Manufacturers
    */

    Route::group(['prefix' => 'manufacturers', 'middleware' => ['auth']], function () {
        Route::post('{manufacturers_id}/restore', [ManufacturersController::class, 'restore'] )->name('restore/manufacturer');
        Route::post('seed', [ManufacturersController::class, 'seed'] )->name('manufacturers.seed');


    });

    Route::resource('manufacturers', ManufacturersController::class);

    Route::post('manufacturers/bulk/delete', [BulkManufacturersController::class, 'destroy'])->name('manufacturers.bulk.delete');

    /*
    * Suppliers
    */
    Route::resource('suppliers', SuppliersController::class);

    Route::post('suppliers/bulk/delete', [BulkSuppliersController::class, 'destroy'])->name('suppliers.bulk.delete');

    /*
    * Depreciations
     */
    Route::resource('depreciations', DepreciationsController::class);

    /*
    * Status Labels
     */
    Route::resource('statuslabels', StatuslabelsController::class);

    /*
    * Departments
    */
    Route::resource('departments', DepartmentsController::class);
});

/*
|
|--------------------------------------------------------------------------
| Re-Usable Modal Dialog routes.
|--------------------------------------------------------------------------
|
| Routes for various modal dialogs to interstitially create various things
|
*/

Route::group(['middleware' => 'auth', 'prefix' => 'modals'], function () {
    Route::get('{type}/{itemId?}', [ModalController::class, 'show'] )->name('modal.show');
});

/*
|--------------------------------------------------------------------------
| Log Routes
|--------------------------------------------------------------------------
|
| Register all the admin routes.
|
*/

Route::group(['middleware' => 'auth'], function () {
    Route::get(
        'display-sig/{filename}',
        [ActionlogController::class, 'displaySig']
    )->name('log.signature.view');
    Route::get(
        'stored-eula-file/{filename}',
        [ActionlogController::class, 'getStoredEula']
    )->name('log.storedeula.download');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Register all the admin routes.
|
*/

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'authorize:superuser']], function () {

    Route::get('settings', [SettingsController::class, 'getSettings'])
        ->name('settings.general.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.general_title'), route('settings.general.index')));

    Route::post('settings', [SettingsController::class, 'postSettings'])
        ->name('settings.general.save');

    Route::get('branding', [SettingsController::class, 'getBranding'])
        ->name('settings.branding.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.branding_title'), route('settings.branding.index')));

    Route::post('branding', [SettingsController::class, 'postBranding'])
        ->name('settings.branding.save');

    Route::get('security', [SettingsController::class, 'getSecurity'])
        ->name('settings.security.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.security_title'), route('settings.security.index')));

    Route::post('security', [SettingsController::class, 'postSecurity'])
        ->name('settings.security.save');

    Route::get('localization', [SettingsController::class, 'getLocalization'])
        ->name('settings.localization.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.localization_title'), route('settings.localization.index')));

    Route::post('localization', [SettingsController::class, 'postLocalization'])
        ->name('settings.localization.save');

    Route::get('notifications', [SettingsController::class, 'getAlerts'])
        ->name('settings.alerts.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.alert_title'), route('settings.alerts.index')));

    Route::post('notifications', [SettingsController::class, 'postAlerts'])
        ->name('settings.alerts.save');

    Route::get('slack', [SettingsController::class, 'getSlack'])
        ->name('settings.slack.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.webhook_title'), route('settings.slack.index')));

    Route::post('slack', [SettingsController::class, 'postSlack'])
        ->name('settings.slack.save');

    Route::get('asset_tags', [SettingsController::class, 'getAssetTags'])
        ->name('settings.asset_tags.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.asset_tag_title'), route('settings.asset_tags.index')));

    Route::post('asset_tags', [SettingsController::class, 'postAssetTags'])
        ->name('settings.asset_tags.save');

    Route::get('labels', [SettingsController::class, 'getLabels'])
        ->name('settings.labels.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.labels_title'), route('settings.labels.index')));

    Route::post('labels', [SettingsController::class, 'postLabels'])
        ->name('settings.labels.save');

    Route::get('ldap', [SettingsController::class, 'getLdapSettings'])
        ->name('settings.ldap.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.ldap_ad'), route('settings.ldap.index')));

    Route::post('ldap', [SettingsController::class, 'postLdapSettings'])
        ->name('settings.ldap.save');

    Route::get('phpinfo', [SettingsController::class, 'getPhpInfo'])
        ->name('settings.phpinfo.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.php_info'), route('settings.phpinfo.index')));

    Route::get('oauth', [SettingsController::class, 'api'])
        ->name('settings.oauth.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.oauth'), route('settings.oauth.index')));

    Route::get('google', [SettingsController::class, 'getGoogleLoginSettings'])
        ->name('settings.google.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.google_login'), route('settings.google.index')));

    Route::post('google', [SettingsController::class, 'postGoogleLoginSettings'])
        ->name('settings.google.save');

    Route::get('purge', [SettingsController::class, 'getPurge'])
        ->name('settings.purge.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.purge'), route('settings.purge.index')));

    Route::post('purge', [SettingsController::class, 'postPurge'])
        ->name('settings.purge.save');

    Route::get('login-attempts', [SettingsController::class, 'getLoginAttempts'])
        ->name('settings.logins.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.login'), route('settings.logins.index')));


    // SAML
    Route::get('/saml', [SettingsController::class, 'getSamlSettings'])
        ->name('settings.saml.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('settings.index')
            ->push(trans('admin/settings/general.saml_title'), route('settings.saml.index')));

    Route::post('/saml', [SettingsController::class, 'postSamlSettings'])
        ->name('settings.saml.save');




    // Backups
    Route::group(['prefix' => 'backups', 'middleware' => 'auth'], function () {
        Route::get('download/{filename}',
            [SettingsController::class, 'downloadFile'])->name('settings.backups.download');

        Route::delete('delete/{filename}',
            [SettingsController::class, 'deleteFile'])->name('settings.backups.destroy');

        Route::post('/', 
            [SettingsController::class, 'postBackups']
        )->name('settings.backups.create');

        Route::post('/restore/{filename}', 
            [SettingsController::class, 'postRestore']
        )->name('settings.backups.restore');

        Route::post('/upload', 
            [SettingsController::class, 'postUploadBackup']
        )->name('settings.backups.upload');

        // Handle redirect from after POST request from backup restore
        Route::get('/restore/{filename?}', function () {
            return redirect(route('settings.backups.index'));
        });

        Route::get('/', [SettingsController::class, 'getBackups'])
            ->name('settings.backups.index')
            ->breadcrumbs(fn (Trail $trail) =>
            $trail->parent('settings.index')
                ->push(trans('admin/settings/general.backups'), route('settings.backups.index')));
    });

    Route::resource('groups', GroupsController::class);


    /**
     * This breadcrumb is repeated for groups in the BreadcrumbServiceProvider, since groups uses resource routes
     * and that servcie provider cannot see the breadcrumbs defined below
     */
    Route::get('/', [SettingsController::class, 'index'])
        ->name('settings.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.admin'), route('settings.index')));
});

/*
|--------------------------------------------------------------------------
| Importer Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::get('/import', Importer::class)
    ->middleware('auth')
    ->name('imports.index')
    ->breadcrumbs(fn (Trail $trail) =>
    $trail->parent('home')
        ->push(trans('general.import'), route('imports.index')));

/*
|--------------------------------------------------------------------------
| Account Routes
|--------------------------------------------------------------------------
|
|
|
*/
Route::group(['prefix' => 'account', 'middleware' => ['auth']], function () {

    // Profile
    Route::get('profile', [ProfileController::class, 'getIndex'])
        ->name('profile')
        ->breadcrumbs(fn (Trail $trail) =>
                $trail->parent('home')
            ->push(trans('general.editprofile'), route('profile')));

    Route::post('profile', [ProfileController::class, 'postIndex'])
        ->name('profile.update');

    Route::get('menu', [ProfileController::class, 'getMenuState'])
        ->name('account.menuprefs');

    Route::get('password', [ProfileController::class, 'password'])
        ->name('account.password.index')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.changepassword'), route('account.password.index')));

    Route::post('password', [ProfileController::class, 'passwordSave'])
        ->name('account.password.update');

    Route::get('api', [ProfileController::class, 'api'])
        ->name('user.api')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.manage_api_keys'), route('user.api')));

    // View Assets
    Route::get('view-assets', [ViewAssetsController::class, 'getIndex'])
        ->name('view-assets')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.viewassets'), route('view-assets')));

    Route::get('requested', [ViewAssetsController::class, 'getRequestedAssets'])
        ->name('account.requested')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.requested_assets_menu'), route('account.requested')));

    Route::get(
        'requestable-assets', [ViewAssetsController::class, 'getRequestableIndex'])
        ->name('requestable-assets')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.requestable_items'), route('requestable-assets')));


    Route::post('request-asset/{asset}', [ViewAssetsController::class, 'store'])
        ->name('account.request-asset');

    Route::post('request-asset/{asset}/cancel', [ViewAssetsController::class, 'destroy'])
        ->name('account.request-asset.cancel');

    Route::post('request/{itemType}/{itemId}/{cancel_by_admin?}/{requestingUser?}', [ViewAssetsController::class, 'getRequestItem'])
        ->name('account/request-item');

    Route::get(
        'display-sig/{filename}',
        [ProfileController::class, 'displaySig']
    )->name('profile.signature.view');
    
    Route::get(
        'stored-eula-file/{filename}',
        [ProfileController::class, 'getStoredEula']
    )->name('profile.storedeula.download');

    // Account Dashboard
    Route::get('/', [ViewAssetsController::class, 'getIndex'])
        ->name('account');

    Route::get('accept', [Account\AcceptanceController::class, 'index'])
        ->name('account.accept')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.accept_assets_menu'), route('account.accept')));

    Route::get('accept/{id}', [Account\AcceptanceController::class, 'create'])
        ->name('account.accept.item')
        ->breadcrumbs(fn (Trail $trail, $id) =>
        $trail->parent('account.accept')
            ->push(trans('general.accept_item'), route('account.accept.item', $id)));

    Route::post('accept/{id}', [Account\AcceptanceController::class, 'store'])
        ->name('account.store-acceptance');

    Route::get(
        'print',
        [
            ProfileController::class,
            'printInventory'
        ]
    )->name('profile.print');

    Route::post(
        'email',
        [
            ProfileController::class,
            'emailAssetList'
        ]
    )->name('profile.email_assets');

});

Route::group(['middleware' => ['auth']], function () {
    Route::post('notes', [NotesController::class, 'store'])->name('notes.store');
});

Route::group(['prefix' => 'reports', 'middleware' => ['auth']], function () {

    Route::get('audit', [ReportsController::class, 'audit'])
        ->name('reports.audit')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.audit_report'), route('reports.audit')));

    Route::get(
        'depreciation', [ReportsController::class, 'getDeprecationReport'])
        ->name('reports/depreciation')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.depreciation_report'), route('reports/depreciation')));


    // Is this still used??
    Route::get(
        'export/depreciation', [ReportsController::class, 'exportDeprecationReport'])
        ->name('reports/export/depreciation')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.depreciation_report'), route('reports.audit')));

    Route::get(
        'maintenances', [ReportsController::class, 'getMaintenancesReport'])
        ->name('ui.reports.maintenances')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.asset_maintenance_report'), route('ui.reports.maintenances')));

    // Is this still used?
    Route::get('export/maintenances', [ReportsController::class, 'exportMaintenancesReport'])
        ->name('reports/export/maintenances')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.asset_maintenance_report'), route('reports/export/maintenances')));

    Route::get('licenses', [ReportsController::class, 'getLicenseReport'])
        ->name('reports/licenses')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.license_report'), route('reports/licenses')));

    Route::get('export/licenses', [ReportsController::class, 'exportLicenseReport'])
        ->name('reports/export/licenses');

    Route::get('accessories', [ReportsController::class, 'getAccessoryReport'])
        ->name('reports/accessories');

    Route::get('export/accessories', [ReportsController::class, 'exportAccessoryReport'])
        ->name('reports/export/accessories');

    Route::get('custom', [ReportsController::class, 'getCustomReport'])
        ->name('reports/custom')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.custom_report'), route('reports/custom')));

    Route::post('custom', [ReportsController::class, 'postCustom'])
        ->name('reports.post-custom');


    Route::prefix('templates')
        ->group(function () {

            Route::post('/', [ReportTemplatesController::class, 'store'])
                ->name('report-templates.store');

            // The breadcrumb on this is a little odd for now since we don't have a template index
            Route::get('/{reportTemplate}', [ReportTemplatesController::class, 'show'])
                ->name('report-templates.show')
                ->breadcrumbs(fn (Trail $trail, ReportTemplate $reportTemplate) =>
                $trail->parent('reports/custom')
                    ->push($reportTemplate->name, null)
                    ->push(trans('general.customize_report'), ''));

            Route::get('/{reportTemplate}/edit', [ReportTemplatesController::class, 'edit'])
                ->name('report-templates.edit')
                ->breadcrumbs(fn (Trail $trail, ReportTemplate $reportTemplate) =>
                $trail->parent('reports/custom')
                    ->push($reportTemplate->name, route('report-templates.show', $reportTemplate))
                    ->push(trans('general.customize_report'), ''));


            Route::post('/{reportTemplate}', [ReportTemplatesController::class, 'update'])
                ->name('report-templates.update');

            Route::delete('/{reportTemplate}', [ReportTemplatesController::class, 'destroy'])
                ->name('report-templates.destroy');
    });



    Route::get(
        'activity', [ReportsController::class, 'getActivityReport'])
        ->name('reports.activity')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.activity_report'), route('reports.activity')));

    Route::post('activity', [ReportsController::class, 'postActivityReport'])
        ->name('reports.activity.post');

    Route::get('unaccepted_assets/{deleted?}', [ReportsController::class, 'getAssetAcceptanceReport'])
        ->name('reports/unaccepted_assets')
        ->breadcrumbs(fn (Trail $trail) =>
        $trail->parent('home')
            ->push(trans('general.unaccepted_asset_report'), route('reports/unaccepted_assets')));

    Route::post('unaccepted_assets/sent_reminder', [ReportsController::class, 'sentAssetAcceptanceReminder'])
        ->name('reports/unaccepted_assets_sent_reminder');

    Route::delete('unaccepted_assets/{acceptanceId}/delete', [ReportsController::class, 'deleteAssetAcceptance'])
        ->name('reports/unaccepted_assets_delete');

    Route::post(
        'unaccepted_assets/{deleted?}', [ReportsController::class, 'postAssetAcceptanceReport'])
        ->name('reports/export/unaccepted_assets');

});


Route::get(
    'auth/signin',
    [LoginController::class, 'legacyAuthRedirect']
);


/*
|--------------------------------------------------------------------------
| Setup Routes
|--------------------------------------------------------------------------
|
|
|
*/
Route::group(['prefix' => 'setup', 'middleware' => 'web'], function () {
    Route::get(
        'user',
        [SettingsController::class, 'getSetupUser']
    )->name('setup.user');

    Route::post(
        'user',
        [SettingsController::class, 'postSaveFirstAdmin']
    )->name('setup.user.save');


    Route::get(
        'migrate',
        [SettingsController::class, 'getSetupMigrate']
    )->name('setup.migrate');

    Route::get(
        'done',
        [SettingsController::class, 'getSetupDone']
    )->name('setup.done');

    Route::get(
        'mailtest',
        [SettingsController::class, 'ajaxTestEmail']
    )->name('setup.mailtest');

    Route::get(
        '/',
        [SettingsController::class, 'getSetupIndex']
    )->name('setup');
});





Route::group(['middleware' => 'web'], function () {

    Route::get(
        'login',
        [LoginController::class, 'showLoginForm']
    )->name("login");

    Route::post(
        'login',
        [LoginController::class, 'login']
    );

    Route::get(
        'two-factor-enroll',
        [LoginController::class, 'getTwoFactorEnroll']
    )->name('two-factor-enroll');

    Route::get(
        'two-factor',
        [LoginController::class, 'getTwoFactorAuth']
    )->name('two-factor');

    Route::post(
        'two-factor',
        [LoginController::class, 'postTwoFactorAuth']
    );

    Route::post(
        'password/email',
        [ForgotPasswordController::class, 'sendResetLinkEmail']
    )->name('password.email')->middleware('throttle:forgotten_password');

    Route::get(
        'password/reset',
        [ForgotPasswordController::class, 'showLinkRequestForm']
    )->name('password.request')->middleware('throttle:forgotten_password');


    Route::post(
        'password/reset',
        [ResetPasswordController::class, 'reset']
    )->name('password.update')->middleware('throttle:forgotten_password');

    Route::get(
        'password/reset/{token}',
        [ResetPasswordController::class, 'showResetForm']
    )->name('password.reset');


    Route::post(
        'password/email',
        [ForgotPasswordController::class, 'sendResetLinkEmail']
    )->name('password.email')->middleware('throttle:forgotten_password');


     // Socialite Google login
    Route::get('google', 'App\Http\Controllers\GoogleAuthController@redirectToGoogle')->name('google.redirect');
    Route::get('google/callback', 'App\Http\Controllers\GoogleAuthController@handleGoogleCallback')->name('google.callback');


    // need to keep GET /logout for SAML SLO
    Route::get(
        'logout',
        [LoginController::class, 'logout']
    )->name('logout.get');

    Route::post(
        'logout',
        [LoginController::class, 'logout']
    )->name('logout.post');



    /**
     * Uploaded files API routes
     */

    // Get a file
    Route::get('{object_type}/{id}/files/{file_id}',
        [
            UploadedFilesController::class,
            'show'
        ]
    )->name('ui.files.show')
        ->where(['object_type' => 'assets|audits|maintenances|hardware|models|users|locations|accessories|consumables|licenses|components']);

    // Upload files(s)
    Route::post('{object_type}/{id}/files',
        [
            UploadedFilesController::class,
            'store'
        ]
    )->name('ui.files.store')
        ->where(['object_type' => 'assets|audits|maintenances|hardware|models|users|locations|accessories|consumables|licenses|components']);

    // Delete files(s)
    Route::delete('{object_type}/{id}/files/{file_id}/delete',
        [
            UploadedFilesController::class,
            'destroy'
        ]
    )->name('ui.files.destroy')
        ->where(['object_type' => 'assets|maintenances|hardware|models|users|locations|accessories|consumables|licenses|components']);
});


/**
 * Health check route - skip middleware
 */
Route::withoutMiddleware(['web'])->get(
    '/health',
    [HealthController::class, 'get']
)->name('health');


Route::middleware(['auth'])->get(
    '/',
    [DashboardController::class, 'index']
)->name('home')
    ->breadcrumbs(fn (Trail $trail) =>
    $trail->push('Home', route('home'))
    );
