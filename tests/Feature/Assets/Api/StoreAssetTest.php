<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;

test('requires permission to create asset', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.assets.store'))
        ->assertForbidden();
});

test('all asset attributes are stored', function () {
    $company = Company::factory()->create();
    $location = Location::factory()->create();
    $model = AssetModel::factory()->create();
    $rtdLocation = Location::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->createAssets()->checkoutAssets()->create();
    $userAssigned = User::factory()->create();

    $response = $this->actingAsForApi($user)
        ->postJson(route('api.assets.store'), [
            'asset_eol_date' => '2024-06-02',
            'asset_tag' => 'random_string',
            'assigned_user' => $userAssigned->id,
            'company_id' => $company->id,
            'last_audit_date' => '2023-09-03',
            'location_id' => $location->id,
            'model_id' => $model->id,
            'name' => 'A New Asset',
            'notes' => 'Some notes',
            'order_number' => '5678',
            'purchase_cost' => '123.45',
            'purchase_date' => '2023-09-02',
            'requestable' => true,
            'rtd_location_id' => $rtdLocation->id,
            'serial' => '1234567890',
            'status_id' => $status->id,
            'supplier_id' => $supplier->id,
            'warranty_months' => 10,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $asset = Asset::find($response['payload']['id']);

    expect($asset->adminuser->is($user))->toBeTrue();

    expect($asset->asset_eol_date)->toEqual('2024-06-02');
    expect($asset->asset_tag)->toEqual('random_string');
    expect($asset->assigned_to)->toEqual($userAssigned->id);
    expect($asset->company->is($company))->toBeTrue();
    expect($asset->last_audit_date)->toEqual('2023-09-03 00:00:00');
    expect($asset->location->is($location))->toBeTrue();
    expect($asset->model->is($model))->toBeTrue();
    expect($asset->name)->toEqual('A New Asset');
    expect($asset->notes)->toEqual('Some notes');
    expect($asset->order_number)->toEqual('5678');
    expect($asset->purchase_cost)->toEqual('123.45');
    expect($asset->purchase_date->is('2023-09-02'))->toBeTrue();
    expect($asset->requestable)->toEqual('1');
    expect($asset->defaultLoc->is($rtdLocation))->toBeTrue();
    expect($asset->serial)->toEqual('1234567890');
    expect($asset->status->is($status))->toBeTrue();
    expect($asset->supplier->is($supplier))->toBeTrue();
    expect($asset->warranty_months)->toEqual(10);

    $this->assertHasTheseActionLogs($asset, ['create', 'checkout']);
});

test('sets last audit date to midnight of provided date', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'last_audit_date' => '2023-09-03',
            'asset_tag' => '1234',
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $asset = Asset::find($response['payload']['id']);
    expect($asset->last_audit_date)->toEqual('2023-09-03 00:00:00');
});

test('last audit date can be null', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            // 'last_audit_date' => '2023-09-03 12:23:45',
            'asset_tag' => '1234',
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $asset = Asset::find($response['payload']['id']);
    expect($asset->last_audit_date)->toBeNull();
});

test('non date used for last audit date returns validation error', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'last_audit_date' => 'this-is-not-valid',
            'asset_tag' => '1234',
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');

    expect($response->json('messages.last_audit_date'))->not->toBeNull();
});

test('save with archived status and user returns validation error', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'assigned_to' => '1',
            'assigned_type' => User::class,
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->archived()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');

    expect($response->json('messages.status_id'))->not->toBeNull();
});

test('save with pending status and user returns validation error', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'assigned_to' => '1',
            'assigned_type' => User::class,
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->pending()->create()->id,
        ])
        ->assertOk()
        ->assertJson([
            'messages' => ['status_id' => [trans('admin/hardware/form.asset_not_deployable')]],
        ]);

    expect($response->json('messages.status_id'))->not->toBeNull();
});

test('raw assigned to pair is ignored on store', function () {
    // Security regression: sending assigned_to + assigned_type on create must
    // not bypass checkOut() — the asset must be created unassigned with only a
    // 'create' log entry. Use assigned_user / assigned_asset / assigned_location
    // instead (those go through the proper checkout workflow).
    $target = User::factory()->create();
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => '1235',
            'assigned_to' => $target->id,
            'assigned_type' => User::class,
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $asset = Asset::find($response->json()['payload']['id']);
    expect($asset->assigned_to)->toBeNull('assigned_to must not be set via the raw pair');
    $this->assertHasTheseActionLogs($asset, ['create']);
    $this->assertDatabaseMissing('action_logs', [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'checkout',
    ]);
});

test('raw assigned to without assigned type is ignored on store', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => '1235',
            'assigned_to' => '1',
            // 'assigned_type' => User::class — deliberately omit
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $asset = Asset::find($response->json()['payload']['id']);
    expect($asset->assigned_to)->toBeNull();
});

test('raw assigned to with bad assigned type is ignored on store', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => '1235',
            'assigned_to' => '1',
            'assigned_type' => 'nonsense_string',
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $asset = Asset::find($response->json()['payload']['id']);
    expect($asset->assigned_to)->toBeNull();
});

test('raw assigned type without assigned to is ignored on store', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => '1235',
            // 'assigned_to' => '1' — deliberately omit
            'assigned_type' => User::class,
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $asset = Asset::find($response->json()['payload']['id']);
    expect($asset->assigned_to)->toBeNull();
});

test('save with pending status without user is successful', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => '1234',
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->pending()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');
});

test('archived depreciate and physical can be null', function () {
    $model = AssetModel::factory()->ipadModel()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();

    $this->settings->enableAutoIncrement();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'archive' => null,
            'depreciate' => null,
            'physical' => null,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $asset = Asset::find($response['payload']['id']);
    expect($asset->archived)->toEqual(0);
    expect($asset->physical)->toEqual(1);
    expect($asset->depreciate)->toEqual(0);
});

test('archived depreciate and physical can be empty', function () {
    $model = AssetModel::factory()->ipadModel()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();

    $this->settings->enableAutoIncrement();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'archive' => '',
            'depreciate' => '',
            'physical' => '',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $asset = Asset::find($response['payload']['id']);
    expect($asset->archived)->toEqual(0);
    expect($asset->physical)->toEqual(1);
    expect($asset->depreciate)->toEqual(0);
});

test('asset eol date is calculated if purchase date set', function () {
    $model = AssetModel::factory()->mbp13Model()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();

    $this->settings->enableAutoIncrement();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'purchase_date' => '2021-01-01',
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $asset = Asset::find($response['payload']['id']);
    expect($asset->asset_eol_date)->toEqual('2024-01-01');
});

test('asset eol date is not calculated if purchase date not set', function () {
    $model = AssetModel::factory()->mbp13Model()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();

    $this->settings->enableAutoIncrement();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $asset = Asset::find($response['payload']['id']);
    expect($asset->asset_eol_date)->toBeNull();
});

test('asset eol explicit is set if asset eol date is explicitly set', function () {
    $model = AssetModel::factory()->mbp13Model()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();

    $this->settings->enableAutoIncrement();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'asset_eol_date' => '2025-01-01',
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $asset = Asset::find($response['payload']['id']);
    expect($asset->asset_eol_date)->toEqual('2025-01-01');
    expect($asset->eol_explicit)->toBeTrue();
});

test('asset gets asset tag with auto increment', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();

    $this->settings->enableAutoIncrement();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $asset = Asset::find($response['payload']['id']);
    expect($asset->asset_tag)->not->toBeNull();
});

test('asset creation fails with no asset tag or auto increment', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();

    $this->settings->disableAutoIncrement();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('stores period as decimal separator for purchase cost', function () {
    $this->settings->set([
        'default_currency' => 'USD',
        'digit_separator' => '1,234.56',
    ]);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => 'random-string',
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            // API accepts float
            'purchase_cost' => 12.34,
        ])
        ->assertStatusMessageIs('success');

    $asset = Asset::find($response['payload']['id']);

    expect($asset->purchase_cost)->toEqual(12.34);
});

test('stores period as comma separator for purchase cost', function () {
    $this->settings->set([
        'default_currency' => 'EUR',
        'digit_separator' => '1.234,56',
    ]);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => 'random-string',
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            // API also accepts string for comma separated values
            'purchase_cost' => '12,34',
        ])
        ->assertStatusMessageIs('success');

    $asset = Asset::find($response['payload']['id']);

    expect($asset->purchase_cost)->toEqual(12.34);
});

test('unique serial numbers is enforced when enabled', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();
    $serial = '1234567890';

    $this->settings->enableAutoIncrement();
    $this->settings->enableUniqueSerialNumbers();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'serial' => $serial,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'serial' => $serial,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('unique serial numbers is not enforced when disabled', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();
    $serial = '1234567890';

    $this->settings->enableAutoIncrement();
    $this->settings->disableUniqueSerialNumbers();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'serial' => $serial,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'serial' => $serial,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');
});

test('asset tags must be unique when undeleted', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();
    $asset_tag = '1234567890';

    $this->settings->disableAutoIncrement();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => $asset_tag,
            'model_id' => $model->id,
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => $asset_tag,
            'model_id' => $model->id,
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('asset tags can be duplicated if deleted', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();
    $asset_tag = '1234567890';

    $this->settings->disableAutoIncrement();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => $asset_tag,
            'model_id' => $model->id,
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    Asset::find($response['payload']['id'])->delete();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => $asset_tag,
            'model_id' => $model->id,
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');
});

test('an asset can be checked out to user on store', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();
    $user = User::factory()->createAssets()->checkoutAssets()->create();
    $userAssigned = User::factory()->create();

    $this->settings->enableAutoIncrement();

    $response = $this->actingAsForApi($user)
        ->postJson(route('api.assets.store'), [
            'assigned_user' => $userAssigned->id,
            'model_id' => $model->id,
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $asset = Asset::find($response['payload']['id']);

    expect($asset->adminuser->is($user))->toBeTrue();
    expect($asset->checkedOutToUser())->toBeTrue();
    expect($asset->assignedTo->is($userAssigned))->toBeTrue();
    $this->assertHasTheseActionLogs($asset, ['create', 'checkout']);
});

test('store rejects cross company checkout target with full company support enabled', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $actorInCompanyA = User::factory()->createAssets()->checkoutAssets()->forCompany($companyA)->create();
    $targetUserInCompanyB = User::factory()->forCompany($companyB)->create();

    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();
    $assetTag = 'fmcs-store-rollback-asset';

    $this->actingAsForApi($actorInCompanyA)
        ->postJson(route('api.assets.store'), [
            'asset_tag' => $assetTag,
            'model_id' => $model->id,
            'status_id' => $status->id,
            'company_id' => $companyA->id,
            'assigned_user' => $targetUserInCompanyB->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre(trans('general.error_user_company'));

    $this->assertDatabaseMissing('assets', [
        'asset_tag' => $assetTag,
    ]);

    $this->assertDatabaseMissing('action_logs', [
        'action_type' => 'checkout',
        'target_type' => User::class,
        'target_id' => $targetUserInCompanyB->id,
        'item_type' => Asset::class,
    ]);
});

// dataset('checkoutTargets', function () {
//     yield 'Users' => [
//         function () {
//             return [
//                 'key' => 'assigned_user',
//                 'value' => [
//                     User::factory()->create()->id,
//                     User::factory()->create()->id,
//                 ],
//             ];
//         },
//     ];
//
//     yield 'Locations' => [
//         function () {
//             return [
//                 'key' => 'assigned_location',
//                 'value' => [
//                     Location::factory()->create()->id,
//                     Location::factory()->create()->id,
//                 ],
//             ];
//         },
//     ];
//
//     yield 'Assets' => [
//         function () {
//             return [
//                 'key' => 'assigned_asset',
//                 'value' => [
//                     Asset::factory()->create()->id,
//                     Asset::factory()->create()->id,
//                 ],
//             ];
//         },
//     ];
// });

test('assigned field validation cannot be array', function ($key, $value) {
    // ['key' => $key, 'value' => $value] = $data();

    $this->actingAsForApi(User::factory()->createAssets()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => '123456',
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            $key => $value,
        ])
        ->assertStatusMessageIs('error')
        ->assertJson(function (AssertableJson $json) use ($key) {
            $json->has("messages.{$key}")->etc();
        });
})
    // ->with('checkoutTargets');
    ->with([
        'Users' => [
            function () {
                return [
                    'key' => 'assigned_user',
                    'value' => [
                        User::factory()->create()->id,
                        User::factory()->create()->id,
                    ],
                ];
            },
        ],

        'Locations' => [
            function () {
                return [
                    'key' => 'assigned_location',
                    'value' => [
                        Location::factory()->create()->id,
                        Location::factory()->create()->id,
                    ],
                ];
            },
        ],

        'Assets' => [
            function () {
                return [
                    'key' => 'assigned_asset',
                    'value' => [
                        Asset::factory()->create()->id,
                        Asset::factory()->create()->id,
                    ],
                ];
            },
        ],
    ]);

test('an asset can be checked out to location on store', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();
    $location = Location::factory()->create();
    $user = User::factory()->createAssets()->checkoutAssets()->create();

    $this->settings->enableAutoIncrement();

    $response = $this->actingAsForApi($user)
        ->postJson(route('api.assets.store'), [
            'assigned_location' => $location->id,
            'model_id' => $model->id,
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $asset = Asset::find($response['payload']['id']);

    expect($asset->adminuser->is($user))->toBeTrue();
    expect($asset->checkedOutToLocation())->toBeTrue();
    expect($asset->location->is($location))->toBeTrue();
    $this->assertHasTheseActionLogs($asset, ['create', 'checkout']);
});

test('an asset can be checked out to asset on store', function () {
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();
    $asset = Asset::factory()->create();
    $user = User::factory()->createAssets()->checkoutAssets()->create();

    $this->settings->enableAutoIncrement();

    $response = $this->actingAsForApi($user)
        ->postJson(route('api.assets.store'), [
            'assigned_asset' => $asset->id,
            'model_id' => $model->id,
            'status_id' => $status->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $apiAsset = Asset::find($response['payload']['id']);

    expect($apiAsset->adminuser->is($user))->toBeTrue();
    expect($apiAsset->checkedOutToAsset())->toBeTrue();

    // I think this makes sense, but open to a sanity check
    expect($asset->assignedAssets()->find($response['payload']['id'])->is($apiAsset))->toBeTrue();
    $this->assertHasTheseActionLogs($asset, ['create'/* , 'checkout' */]);
    // TODO - should be the two events
});

test('company id needs to be integer', function () {
    $this->actingAsForApi(User::factory()->createAssets()->create())
        ->postJson(route('api.assets.store'), [
            'company_id' => [1],
        ])
        ->assertStatusMessageIs('error')
        ->assertJson(function (AssertableJson $json) {
            $json->has('messages.company_id')->etc();
        });
});

test('serial validation', function () {
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'asset_tag' => '1234',
            'model_id' => AssetModel::factory()->create()->id,
            'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            'serial' => [
                // this should not be an array
            ],
        ])
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesContains('serial');
});

test('asset store does not lazy load custom field default values per field', function () {
    // Regression test for the N+1 on `CustomField::defaultValue($modelId)`.
    // The store() controller used to call `AssetModel::find($model_id)`
    // without eager loading, then iterate `$model->fieldset->fields` and
    // call `$field->defaultValue($model_id)` inside the loop. Each call
    // lazy-loaded `$this->defaultValues` which is
    // `belongsToMany(AssetModel::class, 'models_custom_fields')`, firing
    // one `SELECT models.*  INNER JOIN models_custom_fields` per field.
    // custom_fill resolves the defaults together as a column/value map,
    // so that pivot walk fires once.
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $status = Statuslabel::factory()->readyToDeploy()->create();
    $superuser = User::factory()->superuser()->create();

    $fieldA = CustomField::factory()->macAddress()->create();
    $fieldB = CustomField::factory()->ram()->create();
    $fieldC = CustomField::factory()->cpu()->create();

    $fieldset = CustomFieldset::factory()->create();
    $fieldset->fields()->attach([
        $fieldA->id => ['order' => 1, 'required' => false],
        $fieldB->id => ['order' => 2, 'required' => false],
        $fieldC->id => ['order' => 3, 'required' => false],
    ]);

    $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);
    $model->defaultValues()->attach([
        $fieldA->id => ['default_value' => 'aa:bb:cc:dd:ee:ff'],
        $fieldB->id => ['default_value' => 'default-ram'],
        $fieldC->id => ['default_value' => 'default-cpu'],
    ]);

    DB::enableQueryLog();

    $this->actingAsForApi($superuser)
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tag' => 'n-plus-one-probe',
        ])
        ->assertStatusMessageIs('success');

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Count queries that walk the models_custom_fields pivot from the
    // CustomField::defaultValue() side. Pre-fix fired once per custom
    // field (three times here). Post-fix fires once, from the eager
    // load in custom_fill. Threshold is one, more
    // than that means the lazy-load pattern crept back in.
    $pivotWalks = collect($queries)
        ->filter(fn ($q) => str_contains($q['query'], 'models_custom_fields'))
        ->count();

    expect($pivotWalks)->toBeLessThanOrEqual(1, 'Asset store fired '.$pivotWalks.' models <-> models_custom_fields pivot queries. Expected at most 1 (the eager load upfront).');
});

test('encrypted custom field can be stored', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $status = Statuslabel::factory()->readyToDeploy()->create();
    $field = CustomField::factory()->testEncrypted()->create();
    $superuser = User::factory()->superuser()->create();
    $assetData = Asset::factory()->hasEncryptedCustomField($field)->make();

    $response = $this->actingAsForApi($superuser)
        ->postJson(route('api.assets.store'), [
            $field->db_column_name() => 'This is encrypted field',
            'model_id' => $assetData->model->id,
            'status_id' => $status->id,
            'asset_tag' => '1234',
        ])
        ->assertStatusMessageIs('success')
        ->assertJsonPath('payload.'.$field->db_column_name(), 'This is encrypted field')
        ->assertOk()
        ->json();

    $asset = Asset::findOrFail($response['payload']['id']);
    expect(Crypt::decrypt($asset->getRawOriginal($field->db_column_name())))->toEqual('This is encrypted field');
});

test('encrypted custom field validation passes', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $status = Statuslabel::factory()->readyToDeploy()->create();
    $alphaField = CustomField::factory()->encrypt()->alpha()->create();
    $numericField = CustomField::factory()->encrypt()->numeric()->create();
    $emailField = CustomField::factory()->encrypt()->email()->create();
    $fields = [$alphaField, $numericField, $emailField];
    $superuser = User::factory()->superuser()->create();
    $assetData = Asset::factory()->hasMultipleCustomFields($fields)->make();

    $response = $this->actingAsForApi($superuser)
        ->postJson(route('api.assets.store'), [
            $alphaField->db_column_name() => 'Thisisencryptedfield',
            $numericField->db_column_name() => '1234567890',
            $emailField->db_column_name() => 'poop@poop.com',
            'model_id' => $assetData->model->id,
            'status_id' => $status->id,
            'asset_tag' => '1234',
        ])
        ->assertStatusMessageIs('success')
        ->assertOk()
        ->json();

    $asset = Asset::findOrFail($response['payload']['id']);
    expect(Crypt::decrypt($asset->getRawOriginal($alphaField->db_column_name())))->toEqual('Thisisencryptedfield');
    expect(Crypt::decrypt($asset->getRawOriginal($numericField->db_column_name())))->toEqual('1234567890');
    expect(Crypt::decrypt($asset->getRawOriginal($emailField->db_column_name())))->toEqual('poop@poop.com');
});

test('encrypted custom field validation fails', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $status = Statuslabel::factory()->readyToDeploy()->create();
    $alphaField = CustomField::factory()->encrypt()->alpha()->create();
    $numericField = CustomField::factory()->encrypt()->numeric()->create();
    $emailField = CustomField::factory()->encrypt()->email()->create();
    $fields = [$alphaField, $numericField, $emailField];
    $superuser = User::factory()->superuser()->create();
    $assetData = Asset::factory()->hasMultipleCustomFields($fields)->make();

    $response = $this->actingAsForApi($superuser)
        ->postJson(route('api.assets.store'), [
            $alphaField->db_column_name() => 'Thisisencryptedfield123',
            'model_id' => $assetData->model->id,
            'status_id' => $status->id,
            'asset_tag' => '1234',
        ])
        ->assertStatusMessageIs('error')
        ->assertJsonPath('messages.'.$alphaField->db_column_name(), [trans('validation.alpha', ['attribute' => $alphaField->name])])
        ->assertOk()
        ->json();
});

test('permission needed to store encrypted field', function () {
    // @todo:
    $this->markTestIncomplete();

    $status = Statuslabel::factory()->readyToDeploy()->create();
    $field = CustomField::factory()->testEncrypted()->create();
    $normal_user = User::factory()->editAssets()->create();
    $assetData = Asset::factory()->hasEncryptedCustomField($field)->make();

    $response = $this->actingAsForApi($normal_user)
        ->postJson(route('api.assets.store'), [
            $field->db_column_name() => 'Some Other Value Entirely!',
            'model_id' => $assetData->model->id,
            'status_id' => $status->id,
            'asset_tag' => '1234',
        ])
        // @todo: this is 403 unauthorized
        ->assertStatusMessageIs('success')
        ->assertOk()
        ->assertMessagesAre('Asset updated successfully, but encrypted custom fields were not due to permissions')
        ->json();

    $asset = Asset::findOrFail($response['payload']['id']);
    expect(Crypt::decrypt($asset->getRawOriginal($field->db_column_name())))->toEqual('This is encrypted field');
});

test('base64 asset images', function () {
    $status = Statuslabel::factory()->readyToDeploy()->create();
    $model = AssetModel::factory()->create();
    $superuser = User::factory()->superuser()->create();

    $response = $this->actingAsForApi($superuser)
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tag' => '1234',
            'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAAEsAQMAAADXeXeBAAAABlBMVEX+AAD///+KQee0AAAACXBIWXMAAAsSAAALEgHS3X78AAAAB3RJTUUH5QQbCAoNcoiTQAAAACZJREFUaN7twTEBAAAAwqD1T20JT6AAAAAAAAAAAAAAAAAAAICnATvEAAEnf54JAAAAAElFTkSuQmCC',
        ])
        ->assertStatusMessageIs('success')
        ->assertOk()
        ->json();

    $asset = Asset::findOrFail($response['payload']['id']);
    expect('1234')->toEqual($asset->asset_tag);
    $image_data = Storage::disk('public')->get(app('assets_upload_path').e($asset->image));

    // $this->assertEquals('3d67fb99a0b6926e350f7b71397525d7a6b936c1', sha1($image_data)); //this doesn't work because the image gets resized - use the resized hash instead
    expect(sha1($image_data))->toEqual('db2e13ba04318c99058ca429d67777322f48566b');
});

test('api rejects asset create without serial when model requires serial', function () {
    $model = AssetModel::factory()->create(['require_serial' => 1]);
    $status = Statuslabel::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tag' => 'NEEDS-SERIAL-1',
            // 'serial' intentionally omitted
        ])
        ->assertStatusMessageIs('error');

    $this->assertDatabaseMissing('assets', ['asset_tag' => 'NEEDS-SERIAL-1']);
});

test('api accepts asset create without serial when model does not require serial', function () {
    $model = AssetModel::factory()->create(['require_serial' => 0]);
    $status = Statuslabel::factory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.assets.store'), [
            'model_id' => $model->id,
            'status_id' => $status->id,
            'asset_tag' => 'OPTIONAL-SERIAL-1',
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('assets', ['asset_tag' => 'OPTIONAL-SERIAL-1']);
});
