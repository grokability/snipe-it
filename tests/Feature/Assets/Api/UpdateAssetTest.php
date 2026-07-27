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
use Tests\TestCase;

class UpdateAssetTest extends TestCase
{
    public function test_that_a_non_existent_asset_id_returns_error()
    {
        $this->actingAsForApi(User::factory()->editAssets()->createAssets()->create())
            ->patchJson(route('api.assets.update', 123456789))
            ->assertStatusMessageIs('error');
    }

    public function test_requires_permission_to_update_asset()
    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->patchJson(route('api.assets.update', $asset->id))
            ->assertForbidden();
    }

    public function test_given_permission_update_asset_is_allowed()
    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'name' => 'test',
            ])
            ->assertOk();
    }

    public function test_all_asset_attributes_are_stored()
    {
        $asset = Asset::factory()->create();
        // Needs checkoutAssets too because this test also asserts assigned_user
        // sticks. The PATCH-based assignment path is now gated on the
        // checkout permission (was previously bypassable via edit-only)
        // and requires a deployable status (was previously bypassable too).
        $user = User::factory()->editAssets()->checkoutAssets()->create();
        $userAssigned = User::factory()->create();
        $company = Company::factory()->create();
        $location = Location::factory()->create();
        $model = AssetModel::factory()->create();
        $rtdLocation = Location::factory()->create();
        // Was Statuslabel::factory()->create() which defaults to deployable=0,
        // so a checkout via PATCH would now (correctly) be refused.
        $status = Statuslabel::factory()->rtd()->create();
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
        $this->assertTrue($updatedAsset->status->is($status));
        $this->assertTrue($updatedAsset->supplier->is($supplier));
        $this->assertEquals(10, $updatedAsset->warranty_months);
        // $this->assertEquals('2023-09-03 00:00:00', $updatedAsset->last_audit_date->format('Y-m-d H:i:s'));
        $this->assertEquals('2023-09-03 00:00:00', $updatedAsset->last_audit_date);
    }

    public function test_updates_period_as_comma_separator_for_purchase_cost()
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

    public function test_updates_float_for_purchase_cost()
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

    public function test_updates_us_decimal_for_purchase_cost()
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
                'purchase_cost' => '5412.34', // NOTE - you cannot use thousands-separator here!!!!
            ])
            ->assertStatusMessageIs('success');

        $asset = Asset::find($response['payload']['id']);

        $this->assertEquals(5412.34, $asset->purchase_cost);
    }

    public function test_updates_float_us_decimal_for_purchase_cost()
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

    public function test_asset_eol_date_is_calculated_if_purchase_date_updated()
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

    public function test_asset_eol_date_is_not_calculated_if_purchase_date_not_set()
    {
        $asset = Asset::factory()->laptopMbp()->noPurchaseOrEolDate()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'name' => 'test asset',
                'asset_eol_date' => '2022-01-01',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset->refresh();

        $this->assertEquals('2022-01-01', $asset->asset_eol_date);
    }

    public function test_asset_eol_explicit_is_set_if_asset_eol_date_is_explicitly_set()
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

    public function test_asset_tag_cannot_update_to_null_value()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'asset_tag' => null,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_asset_tag_cannot_update_to_empty_string_value()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'asset_tag' => '',
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_model_id_cannot_update_to_null_value()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'model_id' => null,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_model_id_cannot_update_to_empty_string_value()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'model_id' => '',
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_status_id_cannot_update_to_null_value()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'status_id' => null,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_status_id_cannot_update_to_empty_string_value()
    {
        $asset = Asset::factory()->laptopMbp()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'status_id' => '',
            ])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_if_rtd_location_id_is_set_without_location_id_asset_returns_to_default()
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->laptopMbp()->create([
            'location_id' => $location->id,
        ]);
        $rtdLocation = Location::factory()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'rtd_location_id' => $rtdLocation->id,
            ]);

        $asset->refresh();

        $this->assertTrue($asset->defaultLoc->is($rtdLocation));
        $this->assertTrue($asset->location->is($rtdLocation));
    }

    public function test_if_location_and_rtd_location_are_set_location_id_is_location()
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->laptopMbp()->create();
        $rtdLocation = Location::factory()->create();

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset->id), [
                'rtd_location_id' => $rtdLocation->id,
                'location_id' => $location->id,
            ]);

        $asset->refresh();

        $this->assertTrue($asset->defaultLoc->is($rtdLocation));
        $this->assertTrue($asset->location->is($location));
    }

    public function test_encrypted_custom_field_can_be_updated()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $field = CustomField::factory()->testEncrypted()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
        $superuser = User::factory()->superuser()->create();

        $this->actingAsForApi($superuser)
            ->patchJson(route('api.assets.update', $asset->id), [
                $field->db_column_name() => 'This is encrypted field',
            ])
            ->assertStatusMessageIs('success')
            ->assertOk();

        $asset->refresh();
        $this->assertEquals('This is encrypted field', Crypt::decrypt($asset->{$field->db_column_name()}));
    }

    public function test_permission_needed_to_update_encrypted_field()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $field = CustomField::factory()->testEncrypted()->create();
        $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
        $normal_user = User::factory()->editAssets()->create();

        $asset->{$field->db_column_name()} = Crypt::encrypt('encrypted value should not change');
        $asset->save();

        // test that a 'normal' user *cannot* change the encrypted custom field
        $this->actingAsForApi($normal_user)
            ->patchJson(route('api.assets.update', $asset->id), [
                $field->db_column_name() => 'Some Other Value Entirely!',
            ])
            ->assertStatusMessageIs('success')
            ->assertOk()
            ->assertMessagesAre('Asset updated successfully, but encrypted custom fields were not due to permissions');

        $asset->refresh();
        $this->assertEquals('encrypted value should not change', Crypt::decrypt($asset->{$field->db_column_name()}));
    }

    public function test_checkout_to_user_on_asset_update()
    {
        $asset = Asset::factory()->create();
        // Needs both edit + checkout: assigning via PATCH triggers the
        // checkout workflow, which is now gated on the checkout permission
        // (see security fix in Api\AssetsController::applyAssetUpdate).
        $user = User::factory()->editAssets()->checkoutAssets()->create();
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

    public function test_update_rejects_cross_company_checkout_target_with_full_company_support_enabled()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $asset = Asset::factory()->for($companyA)->create(['name' => 'Original Name']);
        $actorInCompanyA = User::factory()->editAssets()->forCompany($companyA)->create();
        $targetUserInCompanyB = User::factory()->forCompany($companyB)->create();

        $this->actingAsForApi($actorInCompanyA)
            ->patchJson(route('api.assets.update', $asset->id), [
                'name' => 'Name That Should Roll Back',
                'assigned_user' => $targetUserInCompanyB->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertMessagesAre(trans('general.error_user_company'));

        $asset->refresh();

        $this->assertEquals('Original Name', $asset->name);
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assigned_type);

        $this->assertDatabaseMissing('action_logs', [
            'action_type' => 'checkout',
            'target_type' => User::class,
            'target_id' => $targetUserInCompanyB->id,
            'item_type' => Asset::class,
            'item_id' => $asset->id,
        ]);
    }

    public function test_raw_assigned_to_pair_is_ignored_on_update()
    {
        // Security regression: sending assigned_to + assigned_type directly must
        // not bypass checkOut() — the assignment must not change and no checkout
        // log must be written. Use assigned_user / assigned_asset / assigned_location
        // instead (those go through the proper checkout workflow).
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_user = User::factory()->create();

        $originalAssignedTo = $asset->assigned_to;

        $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_to' => $assigned_user->id,
                'assigned_type' => User::class,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $asset->refresh();
        $this->assertEquals($originalAssignedTo, $asset->assigned_to, 'assigned_to must not change via the raw pair');
        $this->assertDatabaseMissing('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'checkout',
        ]);
    }

    public function test_raw_assigned_to_without_assigned_type_is_ignored_on_update()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_user = User::factory()->create();

        $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_to' => $assigned_user->id,
                // 'assigned_type' => User::class — deliberately omit
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $asset->refresh();
        $this->assertNotEquals($assigned_user->id, $asset->assigned_to);
    }

    public function test_raw_assigned_to_with_bad_assigned_type_is_ignored_on_update()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_user = User::factory()->create();

        $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_to' => $assigned_user->id,
                'assigned_type' => 'more_deliberate_nonsense',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $asset->refresh();
        $this->assertNotEquals($assigned_user->id, $asset->assigned_to);
    }

    public function test_raw_assigned_type_without_assigned_to_is_ignored_on_update()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->create();
        $assigned_user = User::factory()->create();

        $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                // 'assigned_to' => $assigned_user->id — deliberately omit
                'assigned_type' => User::class,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $asset->refresh();
        $this->assertNotEquals($assigned_user->id, $asset->assigned_to);
    }

    public function test_checkout_to_deleted_user_fails_on_asset_update()
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

    public function test_checkout_to_location_on_asset_update()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->checkoutAssets()->create();
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

    public function test_checkout_to_deleted_location_fails_on_asset_update()
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

    public function test_checkout_asset_on_asset_update()
    {
        $asset = Asset::factory()->create();
        $user = User::factory()->editAssets()->checkoutAssets()->create();
        $assigned_asset = Asset::factory()->create();

        $this->actingAsForApi($user)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_asset' => $assigned_asset->id,
                'checkout_to_type' => 'user',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success')
            ->json();

        $asset->refresh();
        $this->assertEquals($assigned_asset->id, $asset->assigned_to);
        $this->assertEquals($asset->assigned_type, 'App\Models\Asset');

    }

    public function test_checkout_to_deleted_asset_fails_on_asset_update()
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

    public function test_asset_cannot_be_updated_by_user_in_separate_company()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $userA = User::factory()->editAssets()->forCompany($companyA)->create();
        $userB = User::factory()->editAssets()->forCompany($companyB)->create();
        $asset = Asset::factory()->create([
            'created_by' => $userA->id,
            'company_id' => $companyA->id,
        ]);

        $this->actingAsForApi($userB)
            ->patchJson(route('api.assets.update', $asset->id), [
                'name' => 'test name',
            ])
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($userA)
            ->patchJson(route('api.assets.update', $asset->id), [
                'name' => 'test name',
            ])
            ->assertStatusMessageIs('success');
    }

    public function test_custom_field_cannot_be_updated_if_not_on_current_asset_model()
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

    public function test_updating_next_audit_date_creates_update_log_entry(): void
    {
        $asset = Asset::factory()->create(['next_audit_date' => now()->addMonths(3)->toDateString()]);

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset), [
                'next_audit_date' => now()->addMonths(6)->toDateString(),
            ])
            ->assertOk();

        $this->assertHasTheseActionLogs($asset, ['create', 'update']);
    }

    public function test_updating_next_audit_date_with_other_fields_logs_all_changes(): void
    {
        $asset = Asset::factory()->create([
            'name' => 'Old Name',
            'next_audit_date' => now()->addMonths(3)->toDateString(),
        ]);

        $this->actingAsForApi(User::factory()->editAssets()->create())
            ->patchJson(route('api.assets.update', $asset), [
                'name' => 'New Name',
                'next_audit_date' => now()->addMonths(6)->toDateString(),
            ])
            ->assertOk();

        // One update log — not suppressed by the presence of next_audit_date
        $this->assertHasTheseActionLogs($asset, ['create', 'update']);

        $logMeta = json_decode($asset->assetlog()->where('action_type', 'update')->first()->log_meta, true);
        $this->assertArrayHasKey('name', $logMeta);
        $this->assertArrayHasKey('next_audit_date', $logMeta);
        $this->assertEquals('Old Name', $logMeta['name']['old']);
        $this->assertEquals('New Name', $logMeta['name']['new']);
    }

    /**
     * Security regression pin: the PATCH endpoint accepted assigned_user /
     * assigned_asset / assigned_location and drove them straight into
     * $asset->checkOut(), which fires CheckoutableCheckedOut and bumps
     * checkout_counter — so a caller with only assets.edit (explicit deny
     * on assets.checkout) could still check assets out and steal custody.
     * Api\AssetsController::applyAssetUpdate() now requires the checkout
     * permission whenever the update payload asks for a checkout.
     */
    public function test_update_denies_checkout_when_actor_lacks_checkout_permission()
    {
        $asset = Asset::factory()->create(['name' => 'Original Name']);
        // Edit only. No checkout permission.
        $editOnly = User::factory()->editAssets()->create();
        $target = User::factory()->create();

        $this->actingAsForApi($editOnly)
            ->patchJson(route('api.assets.update', $asset->id), [
                'name' => 'Name That Should Roll Back',
                'assigned_user' => $target->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertMessagesAre(trans('general.unauthorized'));

        $asset->refresh();

        $this->assertNull($asset->assigned_to, 'edit-only actor must not be able to change custody');
        $this->assertNull($asset->assigned_type);
        $this->assertEquals('Original Name', $asset->name, 'name update should have rolled back with the failed checkout');
        $this->assertDatabaseMissing('action_logs', [
            'action_type' => 'checkout',
            'target_type' => User::class,
            'target_id' => $target->id,
            'item_type' => Asset::class,
            'item_id' => $asset->id,
        ]);
    }

    /**
     * Security regression pin: reassigning an already-assigned asset via
     * PATCH used to bypass the "must be currently checked in" workflow
     * check, recording two consecutive checkout events with no intervening
     * checkin. availableForCheckout() rejects any currently-assigned asset,
     * so the second PATCH must fail.
     */
    public function test_update_denies_reassignment_of_already_checked_out_asset()
    {
        $originalHolder = User::factory()->create();
        $newHolder = User::factory()->create();
        $asset = Asset::factory()->create();
        $actor = User::factory()->editAssets()->checkoutAssets()->create();

        // First checkout: succeeds (asset is available).
        $this->actingAsForApi($actor)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_user' => $originalHolder->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        // Second checkout without a checkin: should be refused.
        $this->actingAsForApi($actor)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_user' => $newHolder->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertMessagesAre(trans('admin/hardware/message.checkout.not_available'));

        $this->assertEquals($originalHolder->id, $asset->fresh()->assigned_to, 'custody must not have moved');
    }

    /**
     * Security regression pin: assigning via PATCH used to skip the
     * status_label->deployable check that the dedicated checkout endpoint
     * enforces. A Pending / non-deployable asset must not be checkoutable.
     */
    public function test_update_denies_checkout_when_asset_status_is_not_deployable()
    {
        $pendingStatus = Statuslabel::factory()->pending()->create();
        $asset = Asset::factory()->create(['status_id' => $pendingStatus->id]);
        $actor = User::factory()->editAssets()->checkoutAssets()->create();
        $target = User::factory()->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.assets.update', $asset->id), [
                'assigned_user' => $target->id,
            ])
            ->assertOk()
            ->assertStatusMessageIs('error')
            ->assertMessagesAre(trans('admin/hardware/message.checkout.not_available'));

        $this->assertNull($asset->fresh()->assigned_to);
    }
}
