<?php

use App\Actions\Breadcrumbs\BuildAcceptanceBreadcrumbs;
use App\Http\Controllers\Account;
use App\Http\Controllers\ActionlogController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BulkCategoriesController;
use App\Http\Controllers\BulkCompaniesController;
use App\Http\Controllers\BulkDepartmentsController;
use App\Http\Controllers\BulkDepreciationsController;
use App\Http\Controllers\BulkMaintenanceTypesController;
use App\Http\Controllers\BulkManufacturersController;
use App\Http\Controllers\BulkStatuslabelsController;
use App\Http\Controllers\BulkSuppliersController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DepreciationsController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\LabelsController;
use App\Http\Controllers\MaintenanceTypesController;
use App\Http\Controllers\ManufacturersController;
use App\Http\Controllers\ModalController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\Reports\CustomComponentReportController;
use App\Http\Controllers\Reports\CustomConsumableReportController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ReportTemplatesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\StatuslabelsController;
use App\Http\Controllers\StorageProxyController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\UploadedFilesController;
use App\Http\Controllers\ViewAssetsController;
use App\Livewire\Importer;
use App\Models\MaintenanceType;
use App\Models\ReportTemplate;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
 * Gate Passport's default personal-access-token routes
 * behind the same self.api permission that protects Snipe-IT's own
 * /account/api and /api/v1/account/personal-access-tokens surfaces.
 *
 * These three routes are auto-registered by PassportServiceProvider
 * with only `web, auth:web` middleware, which meant any user with a
 * valid session cookie could POST here and mint a long-lived bearer
 * token even if an admin had revoked their self.api permission.
 *
 * Snipe-IT's routes/web.php loads before Passport's package service
 * provider boots, so these registrations win route resolution over
 * the Passport originals (FIFO). Requests are forwarded to the same
 * PersonalAccessTokenController Passport would have used, so token
 * shape and behavior are unchanged for users who ARE authorized.
 */
Route::middleware(['web', 'auth', 'can:self.api'])->prefix('oauth')->group(function () {
    Route::get('personal-access-tokens', [\Laravel\Passport\Http\Controllers\PersonalAccessTokenController::class, 'forUser'])
        ->name('passport.personal.tokens.index');
    Route::post('personal-access-tokens', [\Laravel\Passport\Http\Controllers\PersonalAccessTokenController::class, 'store'])
        ->name('passport.personal.tokens.store');
    Route::delete('personal-access-tokens/{token_id}', [\Laravel\Passport\Http\Controllers\PersonalAccessTokenController::class, 'destroy'])
        ->name('passport.personal.tokens.destroy');
});

/*
 * Gate Passport's default OAuth-client management routes behind the
 * superuser check that already protects Snipe-IT's own
 * /admin/oauth surfaces (see the /admin group below,
 * middleware ['auth', 'authorize:superuser']).
 *
 * These four routes were auto-registered by PassportServiceProvider
 * with only `web, auth:web` middleware, so any user with a valid
 * session cookie could POST /oauth/clients with an attacker-controlled
 * redirect URI. If a phished admin then approved the resulting
 * consent screen, the attacker exchanged the auth code for a
 * long-lived bearer token carrying the admin's full API permissions.
 *
 * Same FIFO route-precedence trick as the personal-access-tokens
 * override above: routes/web.php loads before the Passport package
 * service provider boots, so these gated registrations win over the
 * package originals.
 */
Route::middleware(['web', 'auth', 'authorize:superuser'])->prefix('oauth')->group(function () {
    Route::get('clients', [\Laravel\Passport\Http\Controllers\ClientController::class, 'forUser'])
        ->name('passport.clients.index');
    Route::post('clients', [\Laravel\Passport\Http\Controllers\ClientController::class, 'store'])
        ->name('passport.clients.store');
    Route::put('clients/{client_id}', [\Laravel\Passport\Http\Controllers\ClientController::class, 'update'])
        ->name('passport.clients.update');
    Route::delete('clients/{client_id}', [\Laravel\Passport\Http\Controllers\ClientController::class, 'destroy'])
        ->name('passport.clients.destroy');
});

Route::group(['middleware' => 'auth'], function () {
    /*
    * Calendar (unified view across every HasCalendarEvents source).
    * Companion API endpoint lives at /api/v1/calendar/events.
    */
    Route::get('calendar', [App\Http\Controllers\CalendarEventsController::class, 'index'])
        ->name('calendar.index')
        ->breadcrumbs(fn (Tabuna\Breadcrumbs\Trail $trail) => $trail->parent('home')
            ->push(trans('general.calendar'), route('calendar.index'))
        );

    /*
    * Companies
    */
    Route::resource('companies', CompaniesController::class, [
        'parameters' => ['company' => 'company_id'],
    ]);

    Route::post('companies/bulk/delete', [BulkCompaniesController::class, 'destroy'])->name('companies.bulk.delete');

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

    /*
    * Manufacturers
    */

    Route::group(['prefix' => 'manufacturers', 'middleware' => ['auth']], function () {
        Route::post('{manufacturers_id}/restore', [ManufacturersController::class, 'restore'])->name('restore/manufacturer');
        Route::post('seed', [ManufacturersController::class, 'seed'])->name('manufacturers.seed');

    });

    Route::resource('manufacturers', ManufacturersController::class);

    Route::post('manufacturers/bulk/delete', [BulkManufacturersController::class, 'destroy'])->name('manufacturers.bulk.delete');

    /*
    * Suppliers
    */
    Route::resource('suppliers', SuppliersController::class);

    Route::post('suppliers/bulk/delete', [BulkSuppliersController::class, 'destroy'])->name('suppliers.bulk.delete');

    /*
    * Maintenance Types
    *
    * Expanded from a Route::resource so per-route breadcrumbs can hang
    * off each GET action. The four GET routes get an explicit
    * ->breadcrumbs(); the write routes (POST / PUT / PATCH / DELETE)
    * don't render a page and don't need trails. Route names match what
    * Route::resource('maintenance-types', ...) would have generated so
    * every existing route() lookup keeps working.
    */
    Route::get('maintenance-types', [MaintenanceTypesController::class, 'index'])
        ->name('maintenance-types.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('maintenances.index')
            ->push(trans('admin/maintenance_types/general.maintenance_types'), route('maintenance-types.index'))
        );

    Route::get('maintenance-types/create', [MaintenanceTypesController::class, 'create'])
        ->name('maintenance-types.create')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('maintenance-types.index')
            ->push(trans('admin/maintenance_types/general.create'))
        );

    Route::post('maintenance-types', [MaintenanceTypesController::class, 'store'])
        ->name('maintenance-types.store');

    Route::get('maintenance-types/{maintenance_type}', [MaintenanceTypesController::class, 'show'])
        ->name('maintenance-types.show')
        ->breadcrumbs(fn (Trail $trail, MaintenanceType $maintenanceType) => $trail->parent('maintenance-types.index')
            ->push($maintenanceType->name, route('maintenance-types.show', $maintenanceType))
        );

    Route::get('maintenance-types/{maintenance_type}/edit', [MaintenanceTypesController::class, 'edit'])
        ->name('maintenance-types.edit')
        ->breadcrumbs(fn (Trail $trail, MaintenanceType $maintenanceType) => $trail->parent('maintenance-types.show', $maintenanceType)
            ->push(trans('admin/maintenance_types/general.update'))
        );

    Route::put('maintenance-types/{maintenance_type}', [MaintenanceTypesController::class, 'update'])
        ->name('maintenance-types.update');

    Route::patch('maintenance-types/{maintenance_type}', [MaintenanceTypesController::class, 'update']);

    Route::delete('maintenance-types/{maintenance_type}', [MaintenanceTypesController::class, 'destroy'])
        ->name('maintenance-types.destroy');

    Route::post('maintenance-types/bulk/delete', [BulkMaintenanceTypesController::class, 'destroy'])
        ->name('maintenance-types.bulk.delete');

    /*
    * Depreciations
     */
    Route::resource('depreciations', DepreciationsController::class);

    Route::post('depreciations/bulk/delete', [BulkDepreciationsController::class, 'destroy'])->name('depreciations.bulk.delete');

    /*
    * Status Labels
     */
    Route::resource('statuslabels', StatuslabelsController::class);

    Route::post('statuslabels/bulk/delete', [BulkStatuslabelsController::class, 'destroy'])->name('statuslabels.bulk.delete');

    /*
    * Departments
    */
    Route::resource('departments', DepartmentsController::class);

    Route::post('departments/bulk/delete', [BulkDepartmentsController::class, 'destroy'])->name('departments.bulk.delete');
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
    Route::get('{type}/{itemId?}', [ModalController::class, 'show'])->name('modal.show');
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
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.general_title'), route('settings.general.index')));

    Route::post('settings', [SettingsController::class, 'postSettings'])
        ->name('settings.general.save');

    Route::get('settings/location-scoping-report.csv', [SettingsController::class, 'downloadLocationScopingReport'])
        ->name('settings.general.location_scoping_report');

    Route::get('branding', [SettingsController::class, 'getBranding'])
        ->name('settings.branding.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.branding_title'), route('settings.branding.index')));

    Route::post('branding', [SettingsController::class, 'postBranding'])
        ->name('settings.branding.save');

    Route::get('security', [SettingsController::class, 'getSecurity'])
        ->name('settings.security.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.security_title'), route('settings.security.index')));

    Route::post('security', [SettingsController::class, 'postSecurity'])
        ->name('settings.security.save');

    Route::get('localization', [SettingsController::class, 'getLocalization'])
        ->name('settings.localization.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.localization_title'), route('settings.localization.index')));

    Route::post('localization', [SettingsController::class, 'postLocalization'])
        ->name('settings.localization.save');

    Route::get('notifications', [SettingsController::class, 'getAlerts'])
        ->name('settings.alerts.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.alert_title'), route('settings.alerts.index')));

    Route::post('notifications', [SettingsController::class, 'postAlerts'])
        ->name('settings.alerts.save');

    Route::get('slack', [SettingsController::class, 'getSlack'])
        ->name('settings.slack.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.webhook_title'), route('settings.slack.index')));

    Route::post('slack', [SettingsController::class, 'postSlack'])
        ->name('settings.slack.save');

    Route::get('asset_tags', [SettingsController::class, 'getAssetTags'])
        ->name('settings.asset_tags.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.asset_tag_title'), route('settings.asset_tags.index')));

    Route::post('asset_tags', [SettingsController::class, 'postAssetTags'])
        ->name('settings.asset_tags.save');

    Route::get('labels', [SettingsController::class, 'getLabels'])
        ->name('settings.labels.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.labels_title'), route('settings.labels.index')));
    Route::get('labels/customizer-preview/{labelName}', [LabelsController::class, 'customLabelPreview']
    )->where('labelName', '.*')->name('labels.customizer-preview');

    Route::post('labels', [SettingsController::class, 'postLabels'])
        ->name('settings.labels.save');

    Route::get('/settings/labels/create', [LabelsController::class, 'create'])
        ->name('settings.labels.create');
    Route::get('/settings/labels/{label}/edit', [LabelsController::class, 'edit'])
        ->name('settings.labels.edit');
    Route::post('/settings/labels', [LabelsController::class, 'store'])
        ->name('settings.labels.store');
    Route::put('/settings/labels/{label}', [LabelsController::class, 'update'])
        ->name('settings.labels.update');
    Route::delete('settings/labels/{label}', [LabelsController::class, 'destroy'])
        ->name('settings.labels.destroy');

    Route::get('ldap', [SettingsController::class, 'getLdapSettings'])
        ->name('settings.ldap.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.ldap_ad'), route('settings.ldap.index')));

    Route::post('ldap', [SettingsController::class, 'postLdapSettings'])
        ->name('settings.ldap.save');

    // Livewire wizard version of the LDAP settings screen. Lives at its
    // own URL for now; the legacy form stays in place until the wizard
    // has been tested end-to-end.
    Route::view('ldap-wizard', 'settings.ldap-wizard')
        ->name('settings.ldap.wizard')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.ldap_ad'), route('settings.ldap.wizard')));

    Route::get('phpinfo', [SettingsController::class, 'getPhpInfo'])
        ->name('settings.phpinfo.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.php_info'), route('settings.phpinfo.index')));

    Route::get('oauth', [SettingsController::class, 'api'])
        ->name('settings.oauth.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.oauth'), route('settings.oauth.index')));

    Route::post('oauth/request-filters', [SettingsController::class, 'postApiRequestFilters'])
        ->name('settings.oauth.request_filters.save');

    Route::post('oauth/tokens/{token}/revoke', [SettingsController::class, 'revokePersonalAccessToken'])
        ->name('settings.oauth.tokens.revoke');

    Route::post('oauth/tokens/{token}/unrevoke', [SettingsController::class, 'unrevokePersonalAccessToken'])
        ->name('settings.oauth.tokens.unrevoke');

    Route::post('oauth/clients/{client}/revoke', [SettingsController::class, 'revokeOAuthClient'])
        ->name('settings.oauth.clients.revoke');

    Route::post('oauth/clients/{client}/unrevoke', [SettingsController::class, 'unrevokeOAuthClient'])
        ->name('settings.oauth.clients.unrevoke');

    Route::get('google', [SettingsController::class, 'getGoogleLoginSettings'])
        ->name('settings.google.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.google_login'), route('settings.google.index')));

    Route::post('google', [SettingsController::class, 'postGoogleLoginSettings'])
        ->name('settings.google.save');

    Route::get('purge', [SettingsController::class, 'getPurge'])
        ->name('settings.purge.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.purge'), route('settings.purge.index')));

    Route::post('purge', [SettingsController::class, 'postPurge'])
        ->name('settings.purge.save');

    Route::get('login-attempts', [SettingsController::class, 'getLoginAttempts'])
        ->name('settings.logins.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
            ->push(trans('admin/settings/general.login'), route('settings.logins.index')));

    // SAML
    Route::get('/saml', [SettingsController::class, 'getSamlSettings'])
        ->name('settings.saml.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
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
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('settings.index')
                ->push(trans('admin/settings/general.backups'), route('settings.backups.index')));
    });

    Route::resource('groups', GroupsController::class);

    /**
     * This breadcrumb is repeated for groups in the BreadcrumbServiceProvider, since groups uses resource routes
     * and that servcie provider cannot see the breadcrumbs defined below
     */
    Route::get('/', [SettingsController::class, 'index'])
        ->name('settings.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
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

Route::group(['prefix' => 'import', 'middleware' => ['auth']], function () {

    Route::get('download/{import}',
        [
            UploadedFilesController::class,
            'downloadImport',
        ]
    )->name('imports.download');

    Route::livewire('/', Importer::class)
        ->middleware('auth')
        ->name('imports.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.import'), route('imports.index')));

});

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
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.editprofile'), route('profile')));

    Route::post('profile', [ProfileController::class, 'postIndex'])
        ->name('profile.update');

    Route::get('menu', [ProfileController::class, 'getMenuState'])
        ->name('account.menuprefs');

    Route::get('password', [ProfileController::class, 'password'])
        ->name('account.password.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.profile'), route('account'))
            ->push(trans('general.changepassword'), route('account.password.index')));

    Route::post('password', [ProfileController::class, 'passwordSave'])
        ->name('account.password.update');

    Route::get('api', [ProfileController::class, 'api'])
        ->name('user.api')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.profile'), route('account'))
            ->push(trans('general.manage_api_keys'), route('user.api')));

    // View Assets
    Route::get('view-assets', [ViewAssetsController::class, 'getIndex'])
        ->name('view-assets')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.profile'), route('account'))
            ->push(trans('general.viewassets'), route('view-assets')));

    Route::get('requested', [ViewAssetsController::class, 'getRequestedAssets'])
        ->name('account.requested')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.profile'), route('account'))
            ->push(trans('general.requested_assets_menu'), route('account.requested')));

    Route::get(
        'requestable', [ViewAssetsController::class, 'getRequestableIndex'])
        ->name('account.requestable')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.requestable_items'), route('account.requestable')));

    // Legacy /account/requestable-assets URL. Now covers every
    // requestable item type (accessories, consumables, components,
    // licenses, models), not just assets - route + name moved to
    // /account/requestable. 301 keeps external bookmarks working.
    Route::redirect('requestable-assets', '/account/requestable', 301);

    Route::post('request-asset/{asset}', [ViewAssetsController::class, 'store'])
        ->name('account.request-asset');

    Route::post('request-asset/{asset}/cancel', [ViewAssetsController::class, 'destroy'])
        ->name('account.request-asset.cancel');

    // Accepts POST (from the /account/requestable request-item form)
    // AND DELETE (from the shared dataConfirmModal on /requests
    // where the admin cancel-request button pipes through the same
    // handler as every other delete confirmation flow). Same
    // controller for both - the request itself is idempotent so the
    // verb choice is a wiring convenience.
    //
    // Optional requestingUser segment carries the target user id
    // when an admin is canceling on behalf of someone else.
    // Omitted / equal to auth id → self-cancel. Distinct id →
    // admin-cancel, re-authorized server-side against the caller's
    // admin role. The old cancel_by_admin flag was dropped from the
    // URL since the same information falls out of that comparison
    // (see getRequestItem for the inference).
    Route::match(['post', 'delete'], 'request/{itemType}/{itemId}/{requestingUser?}', [ViewAssetsController::class, 'getRequestItem'])
        ->name('account/request-item')
        ->where('itemType', 'asset|asset_model|accessory|consumable|component|license');

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
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.profile'), route('account'))
            ->push(trans('general.accept_items'), route('account.accept')));

    Route::get('accept/{acceptance}', [Account\AcceptanceController::class, 'create'])
        ->name('account.accept.item')
        ->breadcrumbs(fn (Trail $trail, mixed $acceptance) => BuildAcceptanceBreadcrumbs::forAcceptance($trail, $acceptance));

    Route::post('accept/{acceptance}', [Account\AcceptanceController::class, 'store'])
        ->name('account.store-acceptance');

    Route::get(
        'print',
        [
            ProfileController::class,
            'printInventory',
        ]
    )->name('profile.print');

    Route::post(
        'email',
        [
            ProfileController::class,
            'emailAssetList',
        ]
    )->name('profile.email_assets');

});

Route::group(['middleware' => ['auth']], function () {
    Route::post('notes', [NotesController::class, 'store'])->name('notes.store');
});

// Admin queue of open checkout requests across every requestable
// item type (assets, accessories, consumables, components, licenses,
// models). Lived under /hardware/requested back when only assets
// could be requested; moved here now that the queue is polymorphic.
// The legacy /hardware/requested URL still 301s here for existing
// bookmarks (see routes/web/hardware.php). Controller method still
// lives on AssetsController for now; move to a dedicated
// RequestsController when the surrounding request-workflow refactor
// lands.
Route::group(['middleware' => ['auth']], function () {
    Route::get('requests', [\App\Http\Controllers\Assets\AssetsController::class, 'getRequestedIndex'])
        ->name('requests.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.pending_requests'), route('requests.index'))
        );

    Route::post('requests/bulk-cancel', [\App\Http\Controllers\Assets\AssetsController::class, 'bulkCancelRequests'])
        ->name('requests.bulk-cancel');
});

Route::group(['prefix' => 'reports', 'middleware' => ['auth']], function () {

    Route::get('/', [ReportsController::class, 'index'])
        ->name('reports.index')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.reports'), route('reports.index')));

    Route::get('audit', [ReportsController::class, 'audit'])
        ->name('reports.audit')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.reports'), route('reports.index'))
            ->push(trans('general.audit_report'), route('reports.audit')));

    Route::get(
        'depreciation', [ReportsController::class, 'getDeprecationReport'])
        ->name('reports/depreciation')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.reports'), route('reports.index'))
            ->push(trans('general.depreciation_report'), route('reports/depreciation')));

    // Is this still used??
    Route::get(
        'export/depreciation', [ReportsController::class, 'exportDeprecationReport'])
        ->name('reports/export/depreciation')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.reports'), route('reports.index'))
            ->push(trans('general.depreciation_report'), route('reports.audit')));

    Route::get(
        'maintenances', [ReportsController::class, 'getMaintenancesReport'])
        ->name('ui.reports.maintenances')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.reports'), route('reports.index'))
            ->push(trans('general.asset_maintenance_report'), route('ui.reports.maintenances')));

    // Is this still used?
    Route::get('export/maintenances', [ReportsController::class, 'exportMaintenancesReport'])
        ->name('reports/export/maintenances')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.reports'), route('reports.index'))
            ->push(trans('general.asset_maintenance_report'), route('reports/export/maintenances')));

    Route::get('licenses', [ReportsController::class, 'getLicenseReport'])
        ->name('reports/licenses')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.reports'), route('reports.index'))
            ->push(trans('general.license_report'), route('reports/licenses')));

    // @TODO this should be a GET?
    Route::get('export/licenses', [ReportsController::class, 'exportLicenseReport'])
        ->name('reports/export/licenses');

    Route::get('accessories', [ReportsController::class, 'getAccessoryReport'])
        ->name('reports/accessories')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.reports'), route('reports.index'))
            ->push(trans('general.accessory_report'), route('reports/accessories')));

    Route::get('export/accessories', [ReportsController::class, 'exportAccessoryReport'])
        ->name('reports/export/accessories');

    Route::group(['prefix' => 'custom'], function () {
        Route::get('/', [ReportsController::class, 'getCustomReport'])
            ->name('reports/custom')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
                ->push(trans('general.reports'), route('reports.index'))
                ->push(trans('general.custom_report'), route('reports/custom')));

        Route::post('/', [ReportsController::class, 'postCustom'])
            ->name('reports.post-custom');

        Route::get('component', [CustomComponentReportController::class, 'show'])
            ->name('reports.custom.component')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
                ->push(trans('general.reports'), route('reports.index'))
                ->push(trans('general.custom_component_report'), route('reports.custom.component')));

        Route::post('component', [CustomComponentReportController::class, 'run'])
            ->name('reports.custom.component.run');

        Route::get('consumable', [CustomConsumableReportController::class, 'show'])
            ->name('reports.custom.consumable')
            ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
                ->push(trans('general.reports'), route('reports.index'))
                ->push(trans('general.custom_consumable_report'), route('reports.custom.consumable')));

        Route::post('consumable', [CustomConsumableReportController::class, 'run'])
            ->name('reports.custom.consumable.run');
    });

    Route::prefix('templates')
        ->group(function () {

            Route::post('/', [ReportTemplatesController::class, 'store'])
                ->name('report-templates.store');

            // The breadcrumb on this is a little odd for now since we don't have a template index
            Route::get('/{reportTemplate}', [ReportTemplatesController::class, 'show'])
                ->name('report-templates.show')
                ->breadcrumbs(function (Trail $trail, ReportTemplate $reportTemplate) {
                    // 'asset' folds into the default since it maps to the
                    // same breadcrumb parent. Component / consumable have
                    // their own custom-report parents.
                    $parent = match ($reportTemplate->type) {
                        'component' => 'reports.custom.component',
                        'consumable' => 'reports.custom.consumable',
                        default => 'reports/custom',
                    };

                    return $trail->parent($parent)
                        ->push($reportTemplate->name, null);
                });

            Route::get('/{reportTemplate}/edit', [ReportTemplatesController::class, 'edit'])
                ->name('report-templates.edit')
                ->breadcrumbs(function (Trail $trail, ReportTemplate $reportTemplate) {
                    // 'asset' folds into the default since it maps to the
                    // same breadcrumb parent. Component / consumable have
                    // their own custom-report parents.
                    $parent = match ($reportTemplate->type) {
                        'component' => 'reports.custom.component',
                        'consumable' => 'reports.custom.consumable',
                        default => 'reports/custom',
                    };

                    return $trail->parent($parent)
                        ->push($reportTemplate->name, route('report-templates.show', $reportTemplate))
                        ->push(trans('general.update'), '');
                });

            Route::post('/{reportTemplate}', [ReportTemplatesController::class, 'update'])
                ->name('report-templates.update');

            Route::delete('/{reportTemplate}', [ReportTemplatesController::class, 'destroy'])
                ->name('report-templates.destroy');
        });

    Route::get(
        'activity', [ReportsController::class, 'getActivityReport'])
        ->name('reports.activity')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.reports'), route('reports.index'))
            ->push(trans('general.activity_report'), route('reports.activity')));

    Route::post('activity', [ReportsController::class, 'postActivityReport'])
        ->name('reports.activity.post');

    Route::get('unaccepted_assets/{deleted?}', [ReportsController::class, 'getAssetAcceptanceReport'])
        ->name('reports/unaccepted_assets')
        ->breadcrumbs(fn (Trail $trail) => $trail->parent('home')
            ->push(trans('general.reports'), route('reports.index'))
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
        [SetupController::class, 'getSetupUser']
    )->name('setup.user');

    Route::post(
        'user',
        [SetupController::class, 'postSaveFirstAdmin']
    )->name('setup.user.save');

    Route::post(
        'migrate',
        [SetupController::class, 'SetupMigrate']
    )->name('setup.migrate');

    Route::get(
        'done',
        [SetupController::class, 'getSetupDone']
    )->name('setup.done');

    Route::get(
        'mailtest',
        [SettingsController::class, 'ajaxTestEmail']
    )->name('setup.mailtest');

    Route::get(
        '/',
        [SetupController::class, 'getSetupIndex']
    )->name('setup');
});

Route::group(['middleware' => 'web'], function () {

    Route::get(
        'login',
        [LoginController::class, 'showLoginForm']
    )->name('login');

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
    )->middleware('throttle:two_factor');

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
     * QR Code routes
     */
    Route::get('{object_type}/{id}/qr_code',
        [QrCodeController::class, 'show']
    )->name('qr_code/common')
        ->where(['object_type' => 'accessories|assets|hardware|licenses|locations|models|companies|components|consumables|users']);

    /**
     * Uploaded files API routes
     */

    // Get a file
    Route::get('{object_type}/{id}/files/{file_id}',
        [
            UploadedFilesController::class,
            'show',
        ]
    )->name('ui.files.show')
        ->where(['object_type' => 'assets|audits|maintenances|hardware|models|users|locations|accessories|consumables|licenses|suppliers|components|companies|departments']);

    // Upload files(s)
    Route::post('{object_type}/{id}/files',
        [
            UploadedFilesController::class,
            'store',
        ]
    )->name('ui.files.store')
        ->where(['object_type' => 'assets|audits|maintenances|hardware|models|users|locations|accessories|consumables|licenses|suppliers|components|companies|departments']);

    // Delete files(s)
    Route::delete('{object_type}/{id}/files/{file_id}/delete',
        [
            UploadedFilesController::class,
            'destroy',
        ]
    )->name('ui.files.destroy')
        ->where(['object_type' => 'assets|maintenances|hardware|models|users|locations|accessories|consumables|licenses|suppliers|components|companies|departments']);
});

/*
|--------------------------------------------------------------------------
| Storage Proxy Route
|--------------------------------------------------------------------------
|
| When PUBLIC_S3_PROXY=true, public uploads (images, logos, avatars) are
| served through the application instead of directly from S3. This allows
| using a fully private S3 bucket for all storage.
|
*/
Route::get('storage-proxy/{path}', [StorageProxyController::class, 'show'])
    ->where('path', '.*')
    ->name('storage-proxy');

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
    ->breadcrumbs(fn (Trail $trail) => $trail->push('Home', route('home'))
    );
