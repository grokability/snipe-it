<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'api-throttle:api']], function () {


    Route::get('/', function () {
        return response()->json(
            [
                'status' => 'error',
                'message' => '404 endpoint not found. This is the base URL for the API and does not return anything itself. Please check the API reference at https://snipe-it.readme.io/reference to find a valid API endpoint.',
                'payload' => null,
            ], 404);
    });


    /**
     * Account routes
     */
    Route::group(['prefix' => 'account'], function () {

        Route::get('requests',
            [
                Api\ProfileController::class, 
                'requestedAssets'
            ]
        )->name('api.assets.requested');

        Route::get('eulas',
            [
                Api\ProfileController::class,
                'eulas'
            ]
        )->name('api.self.eulas');

        Route::post('request/{asset}', [Api\CheckoutRequest::class, 'store'])->name('api.assets.requests.store');
        Route::post('request/{asset}/cancel', [Api\CheckoutRequest::class, 'destroy'])->name('api.assets.requests.destroy');

        Route::get('requestable/hardware',
            [
                Api\AssetsController::class, 
                'requestable'
            ]
        )->name('api.assets.requestable');

        Route::post('personal-access-tokens',
            [
                Api\ProfileController::class,
                'createApiToken'
            ]
        )->name('api.personal-access-token.create');

        Route::get('personal-access-tokens',
            [
                Api\ProfileController::class,
                'showApiTokens'
            ]
        )->name('api.personal-access-token.index');

        Route::delete('personal-access-tokens/{tokenId}',
            [
                Api\ProfileController::class,
                'deleteApiToken'
            ]
        )->name('api.personal-access-token.delete');



     }); // end account group


     /**
     * Accessories routes
     */
    Route::group(['prefix' => 'accessories'], function () {

        Route::get('{accessory}/checkedout',
            [
                Api\AccessoriesController::class, 
                'checkedout'
            ]
        )->name('api.accessories.checkedout');

        Route::post('{accessory}/checkout',
            [
                Api\AccessoriesController::class, 
                'checkout'
            ]
        )->name('api.accessories.checkout');


        Route::post('{accessory}/checkin',
            [
                Api\AccessoriesController::class, 
                'checkin'
            ]
        )->name('api.accessories.checkin');

        Route::get('selectlist',
            [
                Api\AccessoriesController::class, 
                'selectlist'
            ]
        )->name('api.accessories.selectlist');



     }); // end accessories group

    Route::resource('accessories',
        Api\AccessoriesController::class,
        ['names' => [
                'index' => 'api.accessories.index',
                'show' => 'api.accessories.show',
                'update' => 'api.accessories.update',
                'store' => 'api.accessories.store',
                'destroy' => 'api.accessories.destroy',
            ],
            'except' => ['create', 'edit'],
            'parameters' => ['accessory' => 'accessory_id'],
        ]
    );

     
     /**
      * Categories API routes
      */
      Route::group(['prefix' => 'categories'], function () {
        
        Route::get('{item_type}/selectlist',
            [
                Api\CategoriesController::class, 
                'selectlist'
            ]
        )->name('api.categories.selectlist');

      });

    Route::resource('categories', 
        Api\CategoriesController::class,
        ['names' => [
                'index' => 'api.categories.index',
                'show' => 'api.categories.show',
                'update' => 'api.categories.update',
                'store' => 'api.categories.store',
                'destroy' => 'api.categories.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['category' => 'category_id'],
        ]
    ); // end category API routes

     /**
      * Companies API routes
      */
      Route::group(['prefix' => 'companies'], function () {
        
        Route::get('selectlist',
            [
                Api\CompaniesController::class, 
                'selectlist'
            ]
        )->name('api.companies.selectlist');

      }); 

      Route::resource('companies', 
        Api\CompaniesController::class,
        ['names' => [
                'index' => 'api.companies.index',
                'show' => 'api.companies.show',
                'update' => 'api.companies.update',
                'store' => 'api.companies.store',
                'destroy' => 'api.companies.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['company' => 'company_id'],
        ]
    ); // end companies API routes


    /**
      * Departments API routes
      */
      Route::group(['prefix' => 'departments'], function () {
        
        Route::get('selectlist',
            [
                Api\DepartmentsController::class, 
                'selectlist'
            ]
        )->name('api.departments.selectlist');

      }); 

      Route::resource('departments', 
        Api\DepartmentsController::class,
        ['names' => [
                'index' => 'api.departments.index',
                'show' => 'api.departments.show',
                'update' => 'api.departments.update',
                'store' => 'api.departments.store',
                'destroy' => 'api.departments.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['department' => 'department_id'],
        ]
    ); // end departments API routes


      /**
      * Components API routes
      */
      Route::group(['prefix' => 'components'], function () {
        
        Route::get('selectlist',
            [
                Api\ComponentsController::class, 
                'selectlist'
            ]
        )->name('api.components.selectlist');

        Route::get('{component}/assets',
        [
            Api\ComponentsController::class, 
            'getAssets'
        ]
        )->name('api.components.assets');

      });
    Route::post('components/{id}/checkin',
        [
            Api\ComponentsController::class,
            'checkin'
        ]
    )->name('api.components.checkin');

    Route::post('components/{id}/checkout',
        [
            Api\ComponentsController::class,
            'checkout'
        ]
    )->name('api.components.checkout');


      Route::resource('components', 
        Api\ComponentsController::class,
        ['names' => [
                'index' => 'api.components.index',
                'show' => 'api.components.show',
                'update' => 'api.components.update',
                'store' => 'api.components.store',
                'destroy' => 'api.components.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['component' => 'component_id'],
        ]
    ); // end components API routes


      /**
      * Consumables API routes
      */
      Route::group(['prefix' => 'consumables'], function () {
        
        Route::get('selectlist',
            [
                Api\ConsumablesController::class, 
                'selectlist'
            ]
        )->name('api.consumables.selectlist');

        Route::get('{id}/users',
            [
                Api\ConsumablesController::class, 
                'getDataView'
            ]
        )->name('api.consumables.show.users');


        Route::post('{consumable}/checkout',
            [
                Api\ConsumablesController::class, 
                'checkout'
            ]
        )->name('api.consumables.checkout');

      }); 


      Route::resource('consumables', 
        Api\ConsumablesController::class,
        ['names' => [
                'index' => 'api.consumables.index',
                'show' => 'api.consumables.show',
                'update' => 'api.consumables.update',
                'store' => 'api.consumables.store',
                'destroy' => 'api.consumables.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['consumable' => 'consumable_id'],
        ]
        ); // end consumables API routes



        /**
         * Depreciations API routes
        */
        Route::resource('depreciations', 
        Api\DepreciationsController::class,
        ['names' => [
                'index' => 'api.depreciations.index',
                'show' => 'api.depreciations.show',
                'update' => 'api.depreciations.update',
                'store' => 'api.depreciations.store',
                'destroy' => 'api.depreciations.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['depreciations' => 'depreciation_id'],
        ]
        ); // end depreciations API routes


        Route::get('reports/depreciation',
        [
            Api\AssetsController::class, 
            'index'
        ]
        )->name('api.depreciation-report.index');

       
        
        /**
         * Fields API routes
        */
        Route::group(['prefix' => 'fields'], function () {
        
            Route::post('fieldsets/{id}/order',
                [
                    Api\CustomFieldsController::class, 
                    'postReorder'
                ]
            )->name('api.customfields.order');
    
            Route::post('{field}/associate',
                [
                    Api\CustomFieldsController::class, 
                    'associate'
                ]
            )->name('api.customfields.associate');

            Route::post('{field}/disassociate',
                [
                    Api\CustomFieldsController::class, 
                    'disassociate'
                ]
            )->name('api.customfields.disassociate');
        });

        Route::resource('fields', 
        Api\CustomFieldsController::class,
            ['names' => 
                [
                    'index' => 'api.customfields.index',
                    'show' => 'api.customfields.show',
                    'update' => 'api.customfields.update',
                    'store' => 'api.customfields.store',
                    'destroy' => 'api.customfields.destroy',
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['field' => 'field_id'],
            ]
        ); // end custom fields API routes

        /**
         * Fieldsets API routes
        */
        Route::group(['prefix' => 'fieldsets'], function () {
        
            Route::post('{fieldset}/fields',
                [
                    Api\CustomFieldsetsController::class, 
                    'fields'
                ]
            )->name('api.fieldsets.fields');

            Route::post('{fieldset}/fields/{model}',
                [
                    Api\CustomFieldsetsController::class, 
                    'fieldsWithDefaultValues'
                ]
            )->name('api.fieldsets.fields-with-default-value');
    
        });

        Route::resource('fieldsets', 
        Api\CustomFieldsetsController::class,
            ['names' => [
                    'index' => 'api.fieldsets.index',
                    'show' => 'api.fieldsets.show',
                    'update' => 'api.fieldsets.update',
                    'store' => 'api.fieldsets.store',
                    'destroy' => 'api.fieldsets.destroy',
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['fieldset' => 'fieldset_id'],
            ]
        ); // end custom fieldsets API routes



        /**
         * Groups API routes
        */
        Route::resource('groups', 
        Api\GroupsController::class,
            ['names' => [
                    'index' => 'api.groups.index',
                    'show' => 'api.groups.show',
                    'update' => 'api.groups.update',
                    'store' => 'api.groups.store',
                    'destroy' => 'api.groups.destroy',
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['group' => 'group_id'],
            ]
        ); // end groups API routes


     /**
      * Assets API routes
      */
      Route::group(['prefix' => 'hardware'], function () {

        Route::get('selectlist',
            [
                Api\AssetsController::class, 
                'selectlist'
            ]
        )->name('assets.selectlist');

        Route::get('{asset_id}/licenses',
            [
                Api\AssetsController::class, 
                'licenses'
            ]
        )->name('api.assets.licenselist');

        Route::get('bytag/{tag}',
            [
                Api\AssetsController::class, 
                'showByTag'
            ]
        )->name('assets.show.bytag');

        Route::get('bytag/{any}',
            [
                Api\AssetsController::class, 
                'showByTag'
            ]
        )->name('api.assets.show.bytag')
        ->where('any', '.*');

        Route::post('bytag/{any}/checkout',
            [
                Api\AssetsController::class, 
                'checkoutByTag'
            ]
        )->name('api.assets.checkout.bytag');

        Route::post('bytag/{any}/checkin',
            [
                Api\AssetsController::class,
                'checkinbytag'
            ]
        )->name('api.asset.checkinbytagPath');

        Route::post('checkinbytag',
            [
                Api\AssetsController::class,
                'checkinbytag'
            ]
        )->name('api.asset.checkinbytag');

        Route::get('byserial/{any}',
            [
                Api\AssetsController::class, 
                'showBySerial'
            ]
        )->name('api.assets.show.byserial')
        ->where('any', '.*');




        // This gets the "due or overdue" API endpoints for audit/audits and checkins
        Route::get('{action}/{upcoming_status}',
              [
                  Api\AssetsController::class,
                  'index'
              ]
        )->name('api.assets.list-upcoming')
        ->where(['action' => 'audit|audits|checkins', 'upcoming_status' => 'due|overdue|due-or-overdue']);


        // Legacy URL for audit
          Route::post('audit',
              [
                  Api\AssetsController::class,
                  'audit'
              ]
          )->name('api.asset.audit.legacy');


          // Newer url for audit
        Route::post('{asset}/audit',
        [
            Api\AssetsController::class, 
            'audit'
        ]
        )->name('api.asset.audit');

        Route::post('{id}/checkin',
        [
            Api\AssetsController::class, 
            'checkin'
        ]
        )->name('api.asset.checkin');

        Route::post('{id}/checkout',
          [
            Api\AssetsController::class, 
            'checkout'
          ]
        )->name('api.asset.checkout');

        Route::post('{asset_id}/restore',
          [
              Api\AssetsController::class,
              'restore'
          ]
        )->name('api.assets.restore');


          /** Begin assigned routes */
          Route::get('{asset}/assigned/assets',
              [
                  Api\AssetsController::class,
                  'assignedAssets'
              ]
          )->name('api.assets.assigned_assets');

          Route::get('{asset}/assigned/accessories',
              [
                  Api\AssetsController::class,
                  'assignedAccessories'
              ]
          )->name('api.assets.assigned_accessories');

          Route::get('{asset}/assigned/components',
              [
                  Api\AssetsController::class,
                  'assignedComponents'
              ]
          )->name('api.assets.assigned_components');
          /** End assigned routes */

      });




    // pulling this out of resource route group to begin normalizing for route-model binding.
    // this would probably keep working with the resource route group, but the general practice is for
    // the model name to be the parameter - and i think it's a good differentiation in the code while we convert the others.
    Route::patch('/hardware/{asset}', [Api\AssetsController::class, 'update'])->name('api.assets.update');
    Route::put('/hardware/{asset}', [Api\AssetsController::class, 'update'])->name('api.assets.put-update');
    
    Route::resource('hardware',
        Api\AssetsController::class,
        ['names' => [
                'index' => 'api.assets.index',
                'show' => 'api.assets.show',
                'store' => 'api.assets.store',
                'destroy' => 'api.assets.destroy',
            ],
            'except' => ['create', 'edit', 'update'],
        'parameters' => ['asset' => 'asset_id'],
        ]
        ); // end assets API routes

        /**
         * Asset maintenances API routes
         */
        Route::resource('maintenances', 
        Api\MaintenancesController::class,
        ['names' => [
                'index' => 'api.maintenances.index',
                'show' => 'api.maintenances.show',
                'update' => 'api.maintenances.update',
                'store' => 'api.maintenances.store',
                'destroy' => 'api.maintenances.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['maintenance' => 'maintenance_id'],
        ]
        ); // end assets API routes


      /**
      * Imports API routes
      */
      Route::group(['prefix' => 'imports'], function () {
        
        Route::post('process/{import}',
            [
                Api\ImportController::class, 
                'process'
            ]
        )->name('api.imports.importFile');

      }); 

      Route::resource('imports', 
        Api\ImportController::class,
        ['names' => [
                'index' => 'api.imports.index',
                'show' => 'api.imports.show',
                'update' => 'api.imports.update',
                'store' => 'api.imports.store',
                'destroy' => 'api.imports.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['import' => 'import_id'],
        ]
    ); // end imports API routes


        /**
         * Labels API routes
         */
        Route::group(['prefix' => 'labels'], function() {
            Route::get('{name}', [ Api\LabelsController::class, 'show'])
                ->where('name', '.*')
                ->name('api.labels.show');
            Route::get('', [ Api\LabelsController::class, 'index'])
                ->name('api.labels.index');
        });

        /**
         * Licenses API routes
        */
        Route::group(['prefix' => 'licenses'], function () {

        Route::get('selectlist',
            [
                Api\LicensesController::class, 
                'selectlist'
            ]
        )->name('api.licenses.selectlist');

        }); 

        Route::resource('licenses', 
        Api\LicensesController::class,
        ['names' => [
                'index' => 'api.licenses.index',
                'show' => 'api.licenses.show',
                'update' => 'api.licenses.update',
                'store' => 'api.licenses.store',
                'destroy' => 'api.licenses.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['licenses' => 'license_id'],
        ]
        ); 


        Route::resource('licenses.seats', 
        Api\LicenseSeatsController::class,
        ['names' => [
                'index' => 'api.licenses.seats.index',
                'show' => 'api.licenses.seats.show',
                'update' => 'api.licenses.seats.update',
            ],
        'except' => ['create', 'edit', 'destroy', 'store'],
        'parameters' => ['licenseseat' => 'licenseseat_id'],
        ]
        ); // end license API routes


        /**
         * Locations API routes
        */
        Route::group(['prefix' => 'locations'], function () {

            Route::get('selectlist',
                [
                    Api\LocationsController::class, 
                    'selectlist'
                ]
            )->name('api.locations.selectlist');

            // Users within a location
            Route::get('{location}/users',
                [
                    Api\LocationsController::class, 
                    'getDataViewUsers'
                ]
            )->name('api.locations.viewusers');


            // Get list of assets with a default location
            Route::get('{location}/assets',
            [
                Api\LocationsController::class, 
                'assets'
            ]
            )->name('api.locations.viewassets');


            // Add a comment here, you moron
            /** Begin assigned routes */
            Route::get('{location}/assigned/assets',
                [
                    Api\LocationsController::class,
                    'assignedAssets'
                ]
            )->name('api.locations.assigned_assets');

            Route::get('{location}/assigned/accessories',
                [
                    Api\LocationsController::class,
                    'assignedAccessories'
                ]
            )->name('api.locations.assigned_accessories');
            /** End assigned routes */
        }); 
    
        Route::resource('locations', 
        Api\LocationsController::class,
        ['names' => [
                'index' => 'api.locations.index',
                'show' => 'api.locations.show',
                'update' => 'api.locations.update',
                'store' => 'api.locations.store',
                'destroy' => 'api.locations.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['location' => 'location_id'],
        ]
        ); // end locations API routes


        /**
        * Manufacturers API routes
        */
        Route::group(['prefix' => 'manufacturers'], function () {

            Route::get('selectlist',
                [
                    Api\ManufacturersController::class, 
                    'selectlist'
                ]
            )->name('api.manufacturers.selectlist');

            Route::post('{id}/restore',
                [
                    Api\ManufacturersController::class,
                    'restore'
                ]
            )->name('api.manufacturers.restore');

        }); 
    
        Route::resource('manufacturers', 
        Api\ManufacturersController::class,
        ['names' => [
                'index' => 'api.manufacturers.index',
                'show' => 'api.manufacturers.show',
                'update' => 'api.manufacturers.update',
                'store' => 'api.manufacturers.store',
                'destroy' => 'api.manufacturers.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['manufacturer' => 'manufacturer_id'],
        ]
        ); // end  manufacturers API routes


        /**
        * Asset models API routes
        */
        Route::group(['prefix' => 'models'], function () {

            Route::get('selectlist',
                [
                    Api\AssetModelsController::class, 
                    'selectlist'
                ]
            )->name('api.models.selectlist');

            Route::get('assets',
                [
                    Api\AssetModelsController::class, 
                    'assets'
                ]
            )->name('api.models.assets');

            Route::post('{id}/restore',
                [
                    Api\AssetModelsController::class,
                    'restore'
                ]
            )->name('api.models.restore');

        }); 
    
        Route::resource('models', 
        Api\AssetModelsController::class,
        ['names' => [
                'index' => 'api.models.index',
                'show' => 'api.models.show',
                'update' => 'api.models.update',
                'store' => 'api.models.store',
                'destroy' => 'api.models.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['model' => 'model_id'],
        ]
        ); // end asset models API routes


        /**
         * Asset notes API routes
         */
        Route::group(['prefix' => 'notes'], function () {

            Route::post(
                '{asset}/store',
                [
                    Api\NotesController::class,
                    'store'
                ]
            )->name('api.notes.store');

            Route::get(
                '{asset}/index',
                [
                    Api\NotesController::class,
                    'index'
                ]
            )->name('api.notes.index');
        }
        ); // end asset notes API routes

        /**
        * Settings API routes
        */
        Route::group(['middleware'=> ['auth', 'authorize:superuser'], 'prefix' => 'settings'], function () {

            Route::get('ldaptest',
                [
                    Api\SettingsController::class, 
                    'ldaptest'
                ]
            )->name('api.settings.ldaptest');

            Route::post('purge_barcodes',
                [
                    Api\SettingsController::class, 
                    'purgeBarcodes'
                ]
            )->name('api.settings.purgebarcodes');

            Route::get('login-attempts',
                [
                    Api\SettingsController::class, 
                    'showLoginAttempts'
                ]
            )->name('api.settings.login_attempts');

            Route::post('ldaptestlogin',
                [
                    Api\SettingsController::class, 
                    'ldaptestlogin'
                ]
            )->name('api.settings.ldaptestlogin');

            Route::post('slacktest',
            [
                Api\SettingsController::class, 
                'slacktest'
            ]
            )->name('api.settings.slacktest');

            Route::post('mailtest',
            [
                Api\SettingsController::class, 
                'ajaxTestEmail'
            ]
            )->name('api.settings.mailtest');

            Route::get('backups',
                [
                    Api\SettingsController::class,
                    'listBackups'
                ]
            )->name('api.settings.backups.index');

            Route::get('backups/download/latest',
                [
                    Api\SettingsController::class,
                    'downloadLatestBackup'
                ]
            )->name('api.settings.backups.latest');

            Route::get('backups/download/{file}',
                [
                    Api\SettingsController::class,
                    'downloadBackup'
                ]
            )->name('api.settings.backups.download');

        }); 
        
        Route::resource('settings', 
        Api\SettingsController::class,
        ['names' => [
                'show' => 'api.settings.show',
                'update' => 'api.settings.update',
                'store' => 'api.settings.store',
            ],
        'except' => ['create', 'edit', 'index', 'destroy'],
        'parameters' => ['setting' => 'setting_id'],
        ]
        ); // end settings API


        /**
        * Status labels API routes
        */
        Route::group(['prefix' => 'statuslabels'], function () {

            Route::get('selectlist',
                [
                    Api\StatuslabelsController::class, 
                    'selectlist'
                ]
            )->name('api.statuslabels.selectlist');

            Route::get('assets/name',
                [
                    Api\StatuslabelsController::class, 
                    'getAssetCountByStatuslabel'
                ]
            )->name('api.statuslabels.assets.byname');

            Route::get('assets/type',
                [
                    Api\StatuslabelsController::class,
                    'getAssetCountByMetaStatus'
                ]
            )->name('api.statuslabels.assets.bytype');

            Route::get('{id}/assetlist',
                [
                    Api\StatuslabelsController::class, 
                    'assets'
                ]
            )->name('api.statuslabels.assets');

            Route::get('{statuslabel}/deployable',
                [
                    Api\StatuslabelsController::class, 
                    'checkIfDeployable'
                ]
            )->name('api.statuslabels.deployable');

            Route::get('selectlist',
                [
                    Api\StatuslabelsController::class,
                    'selectlist'
                ]
            )->name('api.statuslabels.selectlist');

        }); 
    
        Route::resource('statuslabels', 
        Api\StatuslabelsController::class,
        ['names' => [
                'index' => 'api.statuslabels.index',
                'show' => 'api.statuslabels.show',
                'update' => 'api.statuslabels.update',
                'store' => 'api.statuslabels.store',
                'destroy' => 'api.statuslabels.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['statuslabel' => 'statuslabel_id'],
        ]
        ); // end status labels API routes


        /**
        * Suppliers API routes
        */
        Route::group(['prefix' => 'suppliers'], function () {

            Route::get('selectlist',
                [
                    Api\SuppliersController::class, 
                    'selectlist'
                ]
            )->name('api.suppliers.selectlist');

        }); 
    
        Route::resource('suppliers', 
        Api\SuppliersController::class,
        ['names' => [
                'index' => 'api.suppliers.index',
                'show' => 'api.suppliers.show',
                'update' => 'api.suppliers.update',
                'store' => 'api.suppliers.store',
                'destroy' => 'api.suppliers.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['supplier' => 'supplier_id'],
        ]
        ); // end suppliers API routes



        /**
        * Users API routes
        */
        Route::group(['prefix' => 'users'], function () {

            Route::get('selectlist',
                [
                    Api\UsersController::class, 
                    'selectlist'
                ]
            )->name('api.users.selectlist');

            Route::post('ldapsync',
                [
                    Api\UsersController::class,
                    'syncLdapUsers'
                ]
            )->name('api.users.ldapsync');

            Route::post('two_factor_reset',
                [
                    Api\UsersController::class, 
                    'postTwoFactorReset'
                ]
            )->name('api.users.two_factor_reset');

            Route::get('me',
                [
                    Api\UsersController::class, 
                    'getCurrentUserInfo'
                ]
            )->name('api.users.me');

            Route::get('{user}/eulas',
                [
                    Api\UsersController::class,
                    'eulas'
                ]
            )->name('api.user.eulas');


            Route::get('list/{status?}',
            [
                Api\UsersController::class, 
                'getDatatable'
            ]
            )->name('api.users.list');

            Route::get('{user}/assets',
            [
                Api\UsersController::class, 
                'assets'
            ]
            )->name('api.users.assetlist');

            Route::post('{user}/email',
                [
                    Api\UsersController::class,
                    'emailAssetList'
                ]
            )->name('api.users.email_assets');

            Route::get('{user}/accessories',
            [
                Api\UsersController::class, 
                'accessories'
            ]
            )->name('api.users.accessorieslist');

            Route::get('{user}/licenses',
            [
                Api\UsersController::class, 
                'licenses'
            ]
            )->name('api.users.licenselist');


            Route::post('{user}/restore',
                [
                    Api\UsersController::class,
                    'restore'
                ]
            )->name('api.users.restore');

        });

        Route::resource('users', 
        Api\UsersController::class,
        ['names' => [
                'index' => 'api.users.index',
                'show' => 'api.users.show',
                'store' => 'api.users.store',
                'update' => 'api.users.update',
                'destroy' => 'api.users.destroy',
            ],
         'except' => ['create', 'edit'],
        'parameters' => ['user' => 'user_id'],
        ]
        ); // end users API routes


        /**
        * Kits API routes
        */
        Route::resource('kits', 
        Api\PredefinedKitsController::class,
        ['names' => [
                'index' => 'api.kits.index',
                'show' => 'api.kits.show',
                'update' => 'api.kits.update',
                'store' => 'api.kits.store',
                'destroy' => 'api.kits.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['kit' => 'kit_id'],
        ]
        ); // end kits API routes


        Route::group(['prefix' => 'kits/{kit_id}'], function () {

             // kit licenses
            Route::get('licenses',
                [
                    Api\PredefinedKitsController::class, 
                    'indexLicenses'
                ]
            )->name('api.kits.licenses.index');

            Route::post('licenses',
                [
                    Api\PredefinedKitsController::class, 
                    'storeLicense'
                ]
            )->name('api.kits.licenses.store');

            Route::put('licenses/{license_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'updateLicense'
                ]
            )->name('api.kits.licenses.update');

            Route::delete('licenses/{license_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'detachLicense'
                ]
            )->name('api.kits.licenses.destroy');


            // kit models
            Route::get('models',
                [
                    Api\PredefinedKitsController::class, 
                    'indexModels'
                ]
            )->name('api.kits.models.index');

            Route::post('models',
                [
                    Api\PredefinedKitsController::class, 
                    'storeModel'
                ]
            )->name('api.kits.models.store');

            Route::put('models/{model_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'updateModels'
                ]
            )->name('api.kits.models.update');

            Route::delete('models/{model_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'detachModels'
                ]
            )->name('api.kits.models.destroy');

             // kit accessories
             Route::get('accessories',
                [
                    Api\PredefinedKitsController::class, 
                    'indexAccessories'
                ]
            )->name('api.kits.accessories.index');

            Route::post('accessories',
                [
                    Api\PredefinedKitsController::class, 
                    'storeAccessory'
                ]
            )->name('api.kits.accessories.store');

            Route::put('accessories/{accessory_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'updateAccessory'
                ]
            )->name('api.kits.accessories.update');

            Route::delete('accessories/{accessory_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'detachAccessory'
                ]
            )->name('api.kits.accessories.destroy');

            // kit consumables
            Route::get('consumables',
            [
                Api\PredefinedKitsController::class, 
                'indexConsumables'
            ]
            )->name('api.kits.consumables.index');

            Route::post('consumables',
            [
                Api\PredefinedKitsController::class, 
                'storeConsumable'
            ]
            )->name('api.kits.consumables.store');

            Route::put('consumables/{consumable_id}',
            [
                Api\PredefinedKitsController::class, 
                'updateConsumable'
            ]
            )->name('api.kits.consumables.update');

            Route::delete('consumables/{consumable_id}',
            [
                Api\PredefinedKitsController::class, 
                'detachConsumable'
            ]
            )->name('api.kits.consumables.destroy');

        }); // end consumable routes
    
        
        /**
         * Reports API routes
         */
        
        Route::group(['prefix' => 'reports'], function () {

            Route::get('activity',
            [
                Api\ReportsController::class, 
                'index'
            ]
            )->name('api.activity.index');
        }); // end reports api routes



    /**
         * Version API routes
         */

        Route::get('/version', function () {
            return response()->json(
                [
                    'version' => config('version.app_version'),
                    'build_version' => config('version.build_version'),
                    'hash_version' => config('version.hash_version'),
                    'full_version' => config('version.full_app_version')
                ]
            );
        }); // end version api routes


        Route::fallback(function () {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => '404 endpoint not found. Please check the API reference at https://snipe-it.readme.io/reference to find a valid API endpoint.',
                    'payload' => null,
                ], 404);
        }); // end fallback routes

        /**
         * Generate label routes
         */
        Route::post('hardware/labels', [
            Api\AssetsController::class,
            'getLabels'
        ])->name('api.assets.labels');
        // end generate label routes

    /**
     * Uploaded files API routes
     */

    // List files
    Route::get('{object_type}/{id}/files',
        [
            Api\UploadedFilesController::class,
            'index'
        ]
    )->name('api.files.index')
        ->where(['object_type' => 'accessories|assets|components|consumables|hardware|licenses|locations|maintenances|models|users']);

    // Get a file
    Route::get('{object_type}/{id}/files/{file_id}',
        [
            Api\UploadedFilesController::class,
            'show'
        ]
    )->name('api.files.show')
        ->where(['object_type' => 'accessories|assets|components|consumables|hardware|licenses|locations|maintenances|models|users']);

    // Upload files(s)
    Route::post('{object_type}/{id}/files',
        [
            Api\UploadedFilesController::class,
            'store'
        ]
    )->name('api.files.store')
        ->where(['object_type' => 'accessories|assets|components|consumables|hardware|licenses|locations|maintenances|models|users']);

    // Delete files(s)
    Route::delete('{object_type}/{id}/files/{file_id}/delete',
        [
            Api\UploadedFilesController::class,
            'destroy'
        ]
    )->name('api.files.destroy')
        ->where(['object_type' => 'accessories|assets|components|consumables|hardware|licenses|locations|maintenances|models|users']);

}); // end API routes
