<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StoreAssetTest extends TestCase
{

    public function testRequiresPermissionToCreateAsset()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.assets.store'))
            ->assertForbidden();
    }

    public function testAllAssetAttributesAreStored()
    {
        $company = Company::factory()->create();
        $location = Location::factory()->create();
        $model = AssetModel::factory()->create();
        $rtdLocation = Location::factory()->create();
        $status = Statuslabel::factory()->readyToDeploy()->create();
        $supplier = Supplier::factory()->create();
        $user = User::factory()->createAssets()->create();
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

        $this->assertTrue($asset->adminuser->is($user));

        $this->assertEquals('2024-06-02', $asset->asset_eol_date);
        $this->assertEquals('random_string', $asset->asset_tag);
        $this->assertEquals($userAssigned->id, $asset->assigned_to);
        $this->assertTrue($asset->company->is($company));
        $this->assertEquals('2023-09-03 00:00:00', $asset->last_audit_date);
        $this->assertTrue($asset->location->is($location));
        $this->assertTrue($asset->model->is($model));
        $this->assertEquals('A New Asset', $asset->name);
        $this->assertEquals('Some notes', $asset->notes);
        $this->assertEquals('5678', $asset->order_number);
        $this->assertEquals('123.45', $asset->purchase_cost);
        $this->assertTrue($asset->purchase_date->is('2023-09-02'));
        $this->assertEquals('1', $asset->requestable);
        $this->assertTrue($asset->defaultLoc->is($rtdLocation));
        $this->assertEquals('1234567890', $asset->serial);
        $this->assertTrue($asset->assetstatus->is($status));
        $this->assertTrue($asset->supplier->is($supplier));
        $this->assertEquals(10, $asset->warranty_months);

        $this->assertHasTheseActionLogs($asset, ['create', 'checkout']);
    }

    public function testSetsLastAuditDateToMidnightOfProvidedDate()
    {
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
        $this->assertEquals('2023-09-03 00:00:00', $asset->last_audit_date);
    }

    public function testLastAuditDateCanBeNull()
    {
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
        $this->assertNull($asset->last_audit_date);
    }

    public function testNonDateUsedForLastAuditDateReturnsValidationError()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'last_audit_date' => 'this-is-not-valid',
                'asset_tag' => '1234',
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertNotNull($response->json('messages.last_audit_date'));
    }

    public function testSaveWithArchivedStatusAndUserReturnsValidationError()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'assigned_to' => '1',
                'assigned_type' => User::class,
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->archived()->create()->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertNotNull($response->json('messages.status_id'));
    }

    public function testSaveWithPendingStatusAndUserReturnsValidationError()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'assigned_to' => '1',
                'assigned_type' => User::class,
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->pending()->create()->id,
            ])
            ->assertOk()
            ->assertJson([
                'messages' =>  ['status_id' => [trans('admin/hardware/form.asset_not_deployable')]]
            ]);

        $this->assertNotNull($response->json('messages.status_id'));
    }

    public function testSaveWithAssignedToChecksOut()
    {
        $user = User::factory()->create();
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'asset_tag' => '1235',
                'assigned_to' => $user->id,
                'assigned_type' => User::class,
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $asset = Asset::find($response->json()['payload']['id']);
        $this->assertEquals($user->id, $asset->assigned_to);
        $this->assertEquals('Asset created successfully. :)', $response->json('messages'));

        $this->assertHasTheseActionLogs($asset, ['create'/*, 'checkout'*/]); // TODO - this _should_ be the two actions
    }


    public function testSaveWithNoAssignedTypeReturnsValidationError()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'asset_tag' => '1235',
                'assigned_to' => '1',
//                'assigned_type' => User::class, //deliberately omit assigned_type
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->readyToDeploy()->create()->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
        $this->assertNotNull($response->json('messages.assigned_type'));
    }

    public function testSaveWithBadAssignedTypeReturnsValidationError()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'asset_tag'     => '1235',
                'assigned_to'   => '1',
                'assigned_type' => 'nonsense_string', //deliberately bad assigned_type
                'model_id'      => AssetModel::factory()->create()->id,
                'status_id'     => Statuslabel::factory()->readyToDeploy()->create()->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
        $this->assertNotNull($response->json('messages.assigned_type'));
    }

    public function testSaveWithAssignedTypeAndNoAssignedToReturnsValidationError()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'asset_tag'     => '1235',
                //'assigned_to'   => '1', //deliberately omit assigned_to
                'assigned_type' => User::class,
                'model_id'      => AssetModel::factory()->create()->id,
                'status_id'     => Statuslabel::factory()->readyToDeploy()->create()->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
        $this->assertNotNull($response->json('messages.assigned_to'));
    }

    public function testSaveWithPendingStatusWithoutUserIsSuccessful()
    {
        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'asset_tag' => '1234',
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->pending()->create()->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');
    }


    public function testArchivedDepreciateAndPhysicalCanBeNull()
    {
        $model = AssetModel::factory()->ipadModel()->create();
        $status = Statuslabel::factory()->readyToDeploy()->create();

        $this->settings->enableAutoIncrement();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'model_id' => $model->id,
                'status_id' => $status->id,
                'archive' => null,
                'depreciate' => null,
                'physical' => null
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset = Asset::find($response['payload']['id']);
        $this->assertEquals(0, $asset->archived);
        $this->assertEquals(1, $asset->physical);
        $this->assertEquals(0, $asset->depreciate);
    }

    public function testArchivedDepreciateAndPhysicalCanBeEmpty()
    {
        $model = AssetModel::factory()->ipadModel()->create();
        $status = Statuslabel::factory()->readyToDeploy()->create();

        $this->settings->enableAutoIncrement();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.store'), [
                'model_id' => $model->id,
                'status_id' => $status->id,
                'archive' => '',
                'depreciate' => '',
                'physical' => ''
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset = Asset::find($response['payload']['id']);
        $this->assertEquals(0, $asset->archived);
        $this->assertEquals(1, $asset->physical);
        $this->assertEquals(0, $asset->depreciate);
    }

    public function testAssetEolDateIsCalculatedIfPurchaseDateSet()
    {
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
        $this->assertEquals('2024-01-01', $asset->asset_eol_date);
    }

    public function testAssetEolDateIsNotCalculatedIfPurchaseDateNotSet()
    {
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
        $this->assertNull($asset->asset_eol_date);
    }

    public function testAssetEolExplicitIsSetIfAssetEolDateIsExplicitlySet()
    {
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
        $this->assertEquals('2025-01-01', $asset->asset_eol_date);
        $this->assertTrue($asset->eol_explicit);
    }

    public function testAssetGetsAssetTagWithAutoIncrement()
    {
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
        $this->assertNotNull($asset->asset_tag);
    }

    public function testAssetCreationFailsWithNoAssetTagOrAutoIncrement()
    {
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
    }

    public function testStoresPeriodAsDecimalSeparatorForPurchaseCost()
    {
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

        $this->assertEquals(12.34, $asset->purchase_cost);
    }

    public function testStoresPeriodAsCommaSeparatorForPurchaseCost()
    {
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

        $this->assertEquals(12.34, $asset->purchase_cost);
    }

    public function testUniqueSerialNumbersIsEnforcedWhenEnabled()
    {
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
    }

    public function testUniqueSerialNumbersIsNotEnforcedWhenDisabled()
    {
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
    }

    public function testAssetTagsMustBeUniqueWhenUndeleted()
    {
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
    }

    public function testAssetTagsCanBeDuplicatedIfDeleted()
    {
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
    }

    public function testAnAssetCanBeCheckedOutToUserOnStore()
    {
        $model = AssetModel::factory()->create();
        $status = Statuslabel::factory()->readyToDeploy()->create();
        $user = User::factory()->createAssets()->create();
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

        $this->assertTrue($asset->adminuser->is($user));
        $this->assertTrue($asset->checkedOutToUser());
        $this->assertTrue($asset->assignedTo->is($userAssigned));
        $this->assertHasTheseActionLogs($asset, ['create', 'checkout']);
    }

    public static function checkoutTargets()
    {
        yield 'Users' => [
            function () {
                return [
                    'key' => 'assigned_user',
                    'value' => [
                        User::factory()->create()->id,
                        User::factory()->create()->id,
                    ],
                ];
            },
        ];

        yield 'Locations' => [
            function () {
                return [
                    'key' => 'assigned_location',
                    'value' => [
                        Location::factory()->create()->id,
                        Location::factory()->create()->id,
                    ],
                ];
            },
        ];

        yield 'Assets' => [
            function () {
                return [
                    'key' => 'assigned_asset',
                    'value' => [
                        Asset::factory()->create()->id,
                        Asset::factory()->create()->id,
                    ],
                ];
            },
        ];
    }

    /** @link https://app.shortcut.com/grokability/story/29181 */
    #[DataProvider('checkoutTargets')]
    public function testAssignedFieldValidationCannotBeArray($data)
    {
        ['key' => $key, 'value' => $value] = $data();

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
    }

    public function testAnAssetCanBeCheckedOutToLocationOnStore()
    {
        $model = AssetModel::factory()->create();
        $status = Statuslabel::factory()->readyToDeploy()->create();
        $location = Location::factory()->create();
        $user = User::factory()->createAssets()->create();

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

        $this->assertTrue($asset->adminuser->is($user));
        $this->assertTrue($asset->checkedOutToLocation());
        $this->assertTrue($asset->location->is($location));
        $this->assertHasTheseActionLogs($asset, ['create', 'checkout']);
    }

    public function testAnAssetCanBeCheckedOutToAssetOnStore()
    {
        $model = AssetModel::factory()->create();
        $status = Statuslabel::factory()->readyToDeploy()->create();
        $asset = Asset::factory()->create();
        $user = User::factory()->createAssets()->create();

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

        $this->assertTrue($apiAsset->adminuser->is($user));
        $this->assertTrue($apiAsset->checkedOutToAsset());
        // I think this makes sense, but open to a sanity check
        $this->assertTrue($asset->assignedAssets()->find($response['payload']['id'])->is($apiAsset));
        $this->assertHasTheseActionLogs($asset, ['create'/*, 'checkout'*/]); // TODO - should be the two events
    }

    /**
     * @link https://app.shortcut.com/grokability/story/24475
     */
    public function testCompanyIdNeedsToBeInteger()
    {
        $this->actingAsForApi(User::factory()->createAssets()->create())
            ->postJson(route('api.assets.store'), [
                'company_id' => [1],
            ])
            ->assertStatusMessageIs('error')
            ->assertJson(function (AssertableJson $json) {
                $json->has('messages.company_id')->etc();
            });
    }

    public function test_serial_validation()
    {
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
    }

    public function testEncryptedCustomFieldCanBeStored()
    {
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
            ->assertOk()
            ->json();

        $asset = Asset::findOrFail($response['payload']['id']);
        $this->assertEquals('This is encrypted field', Crypt::decrypt($asset->{$field->db_column_name()}));
    }

    public function test_encrypted_custom_field_validation_passes()
    {
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
                $alphaField->db_column_name()   => 'Thisisencryptedfield',
                $numericField->db_column_name() => '1234567890',
                $emailField->db_column_name()   => 'poop@poop.com',
                'model_id'                      => $assetData->model->id,
                'status_id'                     => $status->id,
                'asset_tag'                     => '1234',
            ])
            ->assertStatusMessageIs('success')
            ->assertOk()
            ->json();

        $asset = Asset::findOrFail($response['payload']['id']);
        $this->assertEquals('Thisisencryptedfield', Crypt::decrypt($asset->{$alphaField->db_column_name()}));
        $this->assertEquals('1234567890', Crypt::decrypt($asset->{$numericField->db_column_name()}));
        $this->assertEquals('poop@poop.com', Crypt::decrypt($asset->{$emailField->db_column_name()}));
    }

    public function test_encrypted_custom_field_validation_fails()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $status = Statuslabel::factory()->readyToDeploy()->create();
        $alphaField = CustomField::factory()->encrypt()->alpha()->create();
        $numericField = CustomField::factory()->encrypt()->numeric()->create();
        $emailField = CustomField::factory()->encrypt()->email()->create();
        $fields = [$alphaField, $numericField, $emailField];
        $superuser = User::factory()->superuser()->create();
        $assetData = Asset::factory()->hasMultipleCustomFields($fields)->make();
        $cleaned_name = trim(preg_replace('/_+|snipeit|\d+/', ' ', $alphaField->db_column_name()));

        $response = $this->actingAsForApi($superuser)
            ->postJson(route('api.assets.store'), [
                $alphaField->db_column_name() => 'Thisisencryptedfield123',
                'model_id'                    => $assetData->model->id,
                'status_id'                   => $status->id,
                'asset_tag'                   => '1234',
            ])
            ->assertStatusMessageIs('error')
            ->assertJsonPath('messages.'.$alphaField->db_column_name(), [trans('validation.alpha', ['attribute' => $cleaned_name])])
            ->assertOk()
            ->json();
    }


    public function testPermissionNeededToStoreEncryptedField()
    {
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
        $this->assertEquals('This is encrypted field', Crypt::decrypt($asset->{$field->db_column_name()}));
    }

    public function testBase64AssetImages()
    {
        $status = Statuslabel::factory()->readyToDeploy()->create();
        $model = AssetModel::factory()->create();
        $superuser = User::factory()->superuser()->create();

        $response = $this->actingAsForApi($superuser)
            ->postJson(route('api.assets.store'), [
                'model_id' => $model->id,
                'status_id' => $status->id,
                'asset_tag' => '1234',
                'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAAEsAQMAAADXeXeBAAAABlBMVEX+AAD///+KQee0AAAACXBIWXMAAAsSAAALEgHS3X78AAAAB3RJTUUH5QQbCAoNcoiTQAAAACZJREFUaN7twTEBAAAAwqD1T20JT6AAAAAAAAAAAAAAAAAAAICnATvEAAEnf54JAAAAAElFTkSuQmCC'
            ])
            ->assertStatusMessageIs('success')
            ->assertOk()
            ->json();

        $asset = Asset::findOrFail($response['payload']['id']);
        $this->assertEquals($asset->asset_tag, '1234');
        $image_data = Storage::disk('public')->get(app('assets_upload_path') . e($asset->image));
        //$this->assertEquals('3d67fb99a0b6926e350f7b71397525d7a6b936c1', sha1($image_data)); //this doesn't work because the image gets resized - use the resized hash instead
        $this->assertEquals('db2e13ba04318c99058ca429d67777322f48566b', sha1($image_data));
    }
}
