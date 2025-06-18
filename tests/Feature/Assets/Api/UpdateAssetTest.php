<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use App\Models\CustomField;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class UpdateAssetTest extends TestCase
{
    public function testThatANonExistentAssetIdReturnsError()
    {
        $this->actingAsForApi(User::factory()->editAssets()->createAssets()->create())
            ->patchJson(route('api.assets.update', 123456789))
            ->assertStatusMessageIs('error');
    }

    public function testRequiresPermissionToUpdateAsset()
    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->patchJson(route('api.assets.update', $asset->id))
            ->assertForbidden();
    }

    public function testGivenPermissionUpdateAssetIsAllowed()

    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'name' => 'test'
            ])
            ->assertOk();
    }

    public function testAllAssetAttributesAreStored()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $userAssigned = User::factory()->create();
        $company = Company::factory()->create();
        $location = Location::factory()->create();
        $model = AssetModel::factory()->create();
        $rtdLocation = Location::factory()->create();
        $status = Statuslabel::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'asset_eol_date' => '2024-06-02',
                'asset_tag' => 'random_string',
                'assigned_user' => $userAssigned->id,
                'company_id' => $company->id,
                'last_audit_date' => '2023-09-03 12:23:45',
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

        $updatedAsset = Asset::find($response['payload']['id']);

        $this->assertEquals('2024-06-02', $updatedAsset->asset_eol_date);
        $this->assertEquals('random_string', $updatedAsset->asset_tag);
        $this->assertEquals($userAssigned->id, $updatedAsset->assigned_to);
        $this->assertTrue($updatedAsset->company->is($company));
        $this->assertTrue($updatedAsset->location->is($location));
        $this->assertTrue($updatedAsset->model->is($model));
        $this->assertEquals('A New Asset', $updatedAsset->name);
        $this->assertEquals('Some notes', $updatedAsset->notes);
        $this->assertEquals('5678', $updatedAsset->order_number);
        $this->assertEquals('123.45', $updatedAsset->purchase_cost);
        $this->assertTrue($updatedAsset->purchase_date->is('2023-09-02'));
        $this->assertEquals('1', $updatedAsset->requestable);
        $this->assertTrue($updatedAsset->defaultLoc->is($rtdLocation));
        $this->assertEquals('1234567890', $updatedAsset->serial);
        $this->assertTrue($updatedAsset->assetstatus->is($status));
        $this->assertTrue($updatedAsset->supplier->is($supplier));
        $this->assertEquals(10, $updatedAsset->warranty_months);
        //$this->assertEquals('2023-09-03 00:00:00', $updatedAsset->last_audit_date->format('Y-m-d H:i:s'));
        $this->assertEquals('2023-09-03 00:00:00', $updatedAsset->last_audit_date);
    }

    public function testUpdatesPeriodAsCommaSeparatorForPurchaseCost()
    {
        $this->settings->set([
            'default_currency' => 'EUR',
            'digit_separator' => '1.234,56',
        ]);

        $original_asset = Asset::factory()->create();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.assets.update', $original_asset->id), [
                'asset_tag' => 'random-string',
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->create()->id,
                // API also accepts string for comma separated values
                'purchase_cost' => '1.112,34',
            ])
            ->assertStatusMessageIs('success');

        $asset = Asset::find($response['payload']['id']);

        $this->assertEquals(1112.34, $asset->purchase_cost);
    }

    public function testUpdatesFloatForPurchaseCost()
    {
        $this->settings->set([
            'default_currency' => 'EUR',
            'digit_separator' => '1.234,56',
        ]);

        $original_asset = Asset::factory()->create();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.assets.update', $original_asset->id), [
                'asset_tag' => 'random-string',
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->create()->id,
                // API also accepts string for comma separated values
                'purchase_cost' => 12.34,
            ])
            ->assertStatusMessageIs('success');

        $asset = Asset::find($response['payload']['id']);

        $this->assertEquals(12.34, $asset->purchase_cost);
    }

    public function testUpdatesUSDecimalForPurchaseCost()
    {
        $this->settings->set([
            'default_currency' => 'EUR',
            'digit_separator' => '1,234.56',
        ]);

        $original_asset = Asset::factory()->create();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.assets.update', $original_asset->id), [
                'asset_tag' => 'random-string',
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->create()->id,
                // API also accepts string for comma separated values
                'purchase_cost' => '5412.34', //NOTE - you cannot use thousands-separator here!!!!
            ])
            ->assertStatusMessageIs('success');

        $asset = Asset::find($response['payload']['id']);

        $this->assertEquals(5412.34, $asset->purchase_cost);
    }

    public function testUpdatesFloatUSDecimalForPurchaseCost()
    {
        $this->settings->set([
            'default_currency' => 'EUR',
            'digit_separator' => '1,234.56',
        ]);

        $original_asset = Asset::factory()->create();

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.assets.update', $original_asset->id), [
                'asset_tag' => 'random-string',
                'model_id' => AssetModel::factory()->create()->id,
                'status_id' => Statuslabel::factory()->create()->id,
                // API also accepts string for comma separated values
                'purchase_cost' => 12.34,
            ])
            ->assertStatusMessageIs('success');

        $asset = Asset::find($response['payload']['id']);

        $this->assertEquals(12.34, $asset->purchase_cost);
    }

    public function testAssetEolDateIsCalculatedIfPurchaseDateUpdated()
    {
        $asset = Asset::factory()->laptopMbp()->noPurchaseOrEolDate()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson((route('api.assets.update', $asset->id)), [
                'purchase_date' => '2021-01-01',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset->refresh();

        $this->assertEquals('2024-01-01', $asset->asset_eol_date);
    }

    public function testAssetEolDateIsNotCalculatedIfPurchaseDateNotSet()
    {
        $asset = Asset::factory()->laptopMbp()->noPurchaseOrEolDate()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'name' => 'test asset',
                'asset_eol_date' => '2022-01-01'
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset->refresh();

        $this->assertEquals('2022-01-01', $asset->asset_eol_date);
    }

    public function testAssetEolExplicitIsSetIfAssetEolDateIsExplicitlySet()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'asset_eol_date' => '2025-01-01',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset->refresh();

        $this->assertEquals('2025-01-01', $asset->asset_eol_date);
        $this->assertTrue($asset->eol_explicit);
    }

    public function testAssetTagCannotUpdateToNullValue()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'asset_tag' => null,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testAssetTagCannotUpdateToEmptyStringValue()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'asset_tag' => "",
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testModelIdCannotUpdateToNullValue()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'model_id' => null
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testModelIdCannotUpdateToEmptyStringValue()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'model_id' => ""
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testStatusIdCannotUpdateToNullValue()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'status_id' => null
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testStatusIdCannotUpdateToEmptyStringValue()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'status_id' => ""
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function testIfRtdLocationIdIsSetWithoutLocationIdAssetReturnsToDefault()
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->laptopMbp()->create([
            'location_id' => $location->id
        ]);
        $rtdLocation = Location::factory()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'rtd_location_id' => $rtdLocation->id
            ]);

        $asset->refresh();

        $this->assertTrue($asset->defaultLoc->is($rtdLocation));
        $this->assertTrue($asset->location->is($rtdLocation));
    }

    public function testIfLocationAndRtdLocationAreSetLocationIdIsLocation()
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->laptopMbp()->create();
        $rtdLocation = Location::factory()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'rtd_location_id' => $rtdLocation->id,
                'location_id' => $location->id
            ]);

        $asset->refresh();

        $this->assertTrue($asset->defaultLoc->is($rtdLocation));
        $this->assertTrue($asset->location->is($location));
    }

    public function testEncryptedCustomFieldCanBeUpdated()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $field = CustomField::factory()->testEncrypted()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
        $superuser = User::factory()->superuser()->create();

        $this->actingAsForApi($superuser)
            ->patchJson(route('api.assets.update', $asset->id), [
                $field->db_column_name() => 'This is encrypted field'
            ])
            ->assertStatusMessageIs('success')
            ->assertOk();

        $asset->refresh();
        $this->assertEquals('This is encrypted field', Crypt::decrypt($asset->{$field->db_column_name()}));
    }

    public function testPermissionNeededToUpdateEncryptedField()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $field = CustomField::factory()->testEncrypted()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
        $normal_user = User::factory()->editAssets()->create();

        $asset->{$field->db_column_name()} = Crypt::encrypt("encrypted value should not change");
        $asset->save();

        // test that a 'normal' user *cannot* change the encrypted custom field
        $this->actingAsForApi($normal_user)
            ->patchJson(route('api.assets.update', $asset->id), [
                $field->db_column_name() => 'Some Other Value Entirely!'
            ])
            ->assertStatusMessageIs('success')
            ->assertOk()
            ->assertMessagesAre('Asset updated successfully, but encrypted custom fields were not due to permissions');

        $asset->refresh();
        $this->assertEquals("encrypted value should not change", Crypt::decrypt($asset->{$field->db_column_name()}));
    }

    public function testCheckoutToUserOnAssetUpdate()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_user = User::factory()->create();

        $response = $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_user' => $assigned_user->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset->refresh();
        $this->assertEquals($assigned_user->id, $asset->assigned_to);
        $this->assertEquals($asset->assigned_type, 'App\Models\User');
    }

    public function testCheckoutToUserWithAssignedToAndAssignedType()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_user = User::factory()->create();

        $response = $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_to' => $assigned_user->id,
                'assigned_type' => User::class
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset->refresh();
        $this->assertEquals($assigned_user->id, $asset->assigned_to);
        $this->assertEquals($asset->assigned_type, 'App\Models\User');
    }

    public function testCheckoutToUserWithAssignedToWithoutAssignedType()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_user = User::factory()->create();

        $response = $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_to' => $assigned_user->id,
//                'assigned_type' => User::class //deliberately omit assigned_type
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $asset->refresh();
        $this->assertNotEquals($assigned_user->id, $asset->assigned_to);
        $this->assertNotEquals($asset->assigned_type, 'App\Models\User');
        $this->assertNotNull($response->json('messages.assigned_type'));
    }

    public function testCheckoutToUserWithAssignedToWithBadAssignedType()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_user = User::factory()->create();

        $response = $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_to'   => $assigned_user->id,
                'assigned_type' => 'more_deliberate_nonsense' //deliberately bad assigned_type
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $asset->refresh();
        $this->assertNotEquals($assigned_user->id, $asset->assigned_to);
        $this->assertNotEquals($asset->assigned_type, 'App\Models\User');
        $this->assertNotNull($response->json('messages.assigned_type'));
    }

    public function testCheckoutToUserWithoutAssignedToWithAssignedType()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_user = User::factory()->create();

        $response = $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                //'assigned_to'   => $assigned_user->id, // deliberately omit assigned_to
                'assigned_type' => User::class
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $asset->refresh();
        $this->assertNotEquals($assigned_user->id, $asset->assigned_to);
        $this->assertNotEquals($asset->assigned_type, 'App\Models\User');
        $this->assertNotNull($response->json('messages.assigned_to'));
    }

    public function testCheckoutToDeletedUserFailsOnAssetUpdate()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_user = User::factory()->deleted()->create();

        $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_user' => $assigned_user->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->json();

        $asset->refresh();
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assigned_type);
    }

    public function testCheckoutToLocationOnAssetUpdate()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_location = Location::factory()->create();

        $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_location' => $assigned_location->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset->refresh();
        $this->assertEquals($assigned_location->id, $asset->assigned_to);
        $this->assertEquals($asset->assigned_type, 'App\Models\Location');

    }

    public function testCheckoutToDeletedLocationFailsOnAssetUpdate()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_location = Location::factory()->deleted()->create();

        $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_location' => $assigned_location->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->json();

        $asset->refresh();
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assigned_type);
    }

    public function testCheckoutAssetOnAssetUpdate()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_asset = Asset::factory()->create();

        $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_asset'   => $assigned_asset->id,
                'checkout_to_type' => 'user',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset->refresh();
        $this->assertEquals($assigned_asset->id, $asset->assigned_to);
        $this->assertEquals($asset->assigned_type, 'App\Models\Asset');

    }

    public function testCheckoutToDeletedAssetFailsOnAssetUpdate()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_asset = Asset::factory()->deleted()->create();

        $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_asset' => $assigned_asset->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->json();

        $asset->refresh();
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assigned_type);
    }

    public function testAssetCannotBeUpdatedByUserInSeparateCompany()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->editAssets()->create([
            'company_id' => $companyA->id,
        ]);
        $userB = User::factory()->editAssets()->create([
            'company_id' => $companyB->id,
        ]);
        $asset = Asset::factory()->create([
            'created_by'    => $userA->id,
            'company_id' => $companyA->id,
        ]);

        $this->actingAsForApi($userB)
            ->patchJson(route('api.assets.update', $asset->id), [
                'name' => 'test name'
            ])
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($userA)
            ->patchJson(route('api.assets.update', $asset->id), [
                'name' => 'test name'
            ])
            ->assertStatusMessageIs('success');
    }

    public function testCustomFieldCannotBeUpdatedIfNotOnCurrentAssetModel()
    {
        $this->markIncompleteIfMySQL('Custom Field Tests do not work in MySQL');

        $customField = CustomField::factory()->create();
        $customField2 = CustomField::factory()->create();
        $asset = Asset::factory()->hasMultipleCustomFields([$customField])->create();
        $user = User::factory()->editAssets()->create();

        // successful
        $this->actingAsForApi($user)->patchJson(route('api.assets.update', $asset->id), [
            $customField->db_column_name() => 'test attribute',
        ])->assertStatusMessageIs('success');

        // custom field exists, but not on this asset model
        $this->actingAsForApi($user)->patchJson(route('api.assets.update', $asset->id), [
            $customField2->db_column_name() => 'test attribute',
        ])->assertStatusMessageIs('error');

        // custom field does not exist
        $this->actingAsForApi($user)->patchJson(route('api.assets.update', $asset->id), [
            '_snipeit_non_existent_custom_field_50' => 'test attribute',
        ])->assertStatusMessageIs('error');
    }
}
