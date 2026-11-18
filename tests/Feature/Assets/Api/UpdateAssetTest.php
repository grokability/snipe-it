<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

test('that a non existent asset id returns error', function () {
    $this->actingAsForApi(User::factory()->editAssets()->createAssets()->create())
        ->patchJson(route('api.assets.update', 123456789))
        ->assertStatusMessageIs('error');
});

test('requires permission to update asset', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->patchJson(route('api.assets.update', $asset->id))
        ->assertForbidden();
});

test('given permission update asset is allowed', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson(route('api.assets.update', $asset->id), [
            'name' => 'test',
        ])
        ->assertOk();
});

test('all asset attributes are stored', function () {
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

    expect($updatedAsset->asset_eol_date)->toEqual('2024-06-02');
    expect($updatedAsset->asset_tag)->toEqual('random_string');
    expect($updatedAsset->assigned_to)->toEqual($userAssigned->id);
    expect($updatedAsset->company->is($company))->toBeTrue();
    expect($updatedAsset->location->is($location))->toBeTrue();
    expect($updatedAsset->model->is($model))->toBeTrue();
    expect($updatedAsset->name)->toEqual('A New Asset');
    expect($updatedAsset->notes)->toEqual('Some notes');
    expect($updatedAsset->order_number)->toEqual('5678');
    expect($updatedAsset->purchase_cost)->toEqual('123.45');
    expect($updatedAsset->purchase_date->is('2023-09-02'))->toBeTrue();
    expect($updatedAsset->requestable)->toEqual('1');
    expect($updatedAsset->defaultLoc->is($rtdLocation))->toBeTrue();
    expect($updatedAsset->serial)->toEqual('1234567890');
    expect($updatedAsset->status->is($status))->toBeTrue();
    expect($updatedAsset->supplier->is($supplier))->toBeTrue();
    expect($updatedAsset->warranty_months)->toEqual(10);

    // $this->assertEquals('2023-09-03 00:00:00', $updatedAsset->last_audit_date->format('Y-m-d H:i:s'));
    expect($updatedAsset->last_audit_date)->toEqual('2023-09-03 00:00:00');
});

test('updates period as comma separator for purchase cost', function () {
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

    expect($asset->purchase_cost)->toEqual(1112.34);
});

test('updates float for purchase cost', function () {
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

    expect($asset->purchase_cost)->toEqual(12.34);
});

test('updates us decimal for purchase cost', function () {
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

    expect($asset->purchase_cost)->toEqual(5412.34);
});

test('updates float us decimal for purchase cost', function () {
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

    expect($asset->purchase_cost)->toEqual(12.34);
});

test('asset eol date is calculated if purchase date updated', function () {
    $asset = Asset::factory()->laptopMbp()->noPurchaseOrEolDate()->create();

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson((route('api.assets.update', $asset->id)), [
            'purchase_date' => '2021-01-01',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $asset->refresh();

    expect($asset->asset_eol_date)->toEqual('2024-01-01');
});

test('asset eol date is not calculated if purchase date not set', function () {
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

    expect($asset->asset_eol_date)->toEqual('2022-01-01');
});

test('asset eol explicit is set if asset eol date is explicitly set', function () {
    $asset = Asset::factory()->laptopMbp()->create();

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson(route('api.assets.update', $asset->id), [
            'asset_eol_date' => '2025-01-01',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    $asset->refresh();

    expect($asset->asset_eol_date)->toEqual('2025-01-01');
    expect($asset->eol_explicit)->toBeTrue();
});

test('asset tag cannot update to null value', function () {
    $asset = Asset::factory()->laptopMbp()->create();

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson(route('api.assets.update', $asset->id), [
            'asset_tag' => null,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('asset tag cannot update to empty string value', function () {
    $asset = Asset::factory()->laptopMbp()->create();

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson(route('api.assets.update', $asset->id), [
            'asset_tag' => '',
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('model id cannot update to null value', function () {
    $asset = Asset::factory()->laptopMbp()->create();

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson(route('api.assets.update', $asset->id), [
            'model_id' => null,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('model id cannot update to empty string value', function () {
    $asset = Asset::factory()->laptopMbp()->create();

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson(route('api.assets.update', $asset->id), [
            'model_id' => '',
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('status id cannot update to null value', function () {
    $asset = Asset::factory()->laptopMbp()->create();

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson(route('api.assets.update', $asset->id), [
            'status_id' => null,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('status id cannot update to empty string value', function () {
    $asset = Asset::factory()->laptopMbp()->create();

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson(route('api.assets.update', $asset->id), [
            'status_id' => '',
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('if rtd location id is set without location id asset returns to default', function () {
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

    expect($asset->defaultLoc->is($rtdLocation))->toBeTrue();
    expect($asset->location->is($rtdLocation))->toBeTrue();
});

test('if location and rtd location are set location id is location', function () {
    $location = Location::factory()->create();
    $asset = Asset::factory()->laptopMbp()->create();
    $rtdLocation = Location::factory()->create();

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson(route('api.assets.update', $asset->id), [
            'rtd_location_id' => $rtdLocation->id,
            'location_id' => $location->id,
        ]);

    $asset->refresh();

    expect($asset->defaultLoc->is($rtdLocation))->toBeTrue();
    expect($asset->location->is($location))->toBeTrue();
});

test('encrypted custom field can be updated', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $field = CustomField::factory()->testEncrypted()->create();
    $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
    $superuser = User::factory()->superuser()->create();

    $this->actingAsForApi($superuser)
        ->patchJson(route('api.assets.update', $asset->id), [
            $field->db_column_name() => 'This is encrypted field',
        ])
        ->assertStatusMessageIs('success')
        ->assertJsonPath('payload.'.$field->db_column_name(), 'This is encrypted field')
        ->assertOk();

    $asset->refresh();
    expect(Crypt::decrypt($asset->getRawOriginal($field->db_column_name())))->toEqual('This is encrypted field');
});

test('permission needed to update encrypted field', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $field = CustomField::factory()->testEncrypted()->create();
    $asset = Asset::factory()->hasEncryptedCustomField($field)->create();
    $normal_user = User::factory()->editAssets()->create();

    $asset->{$field->db_column_name()} = 'encrypted value should not change';
    $asset->save();

    // test that a 'normal' user *cannot* change the encrypted custom field
    $this->actingAsForApi($normal_user)
        ->patchJson(route('api.assets.update', $asset->id), [
            $field->db_column_name() => 'Some Other Value Entirely!',
        ])
        ->assertStatusMessageIs('success')
        ->assertOk()
        ->assertMessagesAre('Asset updated successfully, but encrypted custom fields were not due to permissions')
        ->assertJsonPath('payload.'.$field->db_column_name(), strtoupper(trans('admin/custom_fields/general.encrypted')))
        ->assertDontSee('encrypted value should not change', false);

    $asset->refresh();
    expect(Crypt::decrypt($asset->getRawOriginal($field->db_column_name())))->toEqual('encrypted value should not change');
});

test('updating an unrelated attribute does not disclose encrypted fields', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $field = CustomField::factory()->testEncrypted()->create();
    $asset = Asset::factory()->hasEncryptedCustomField($field)->create([
        $field->db_column => 'secret retained on the asset',
    ]);

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson(route('api.assets.update', $asset->id), ['name' => 'Updated name'])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->assertJsonPath('payload.name', 'Updated name')
        ->assertJsonPath('payload.'.$field->db_column, strtoupper(trans('admin/custom_fields/general.encrypted')))
        ->assertDontSee('secret retained on the asset', false);

    expect($asset->fresh()->{$field->db_column})->toBe('secret retained on the asset');
});

test('checkout to user on asset update', function () {
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
    expect($asset->assigned_to)->toEqual($assigned_user->id);
    expect('App\Models\User')->toEqual($asset->assigned_type);
});

test('update rejects cross company checkout target with full company support enabled', function () {
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

    expect($asset->name)->toEqual('Original Name');
    expect($asset->assigned_to)->toBeNull();
    expect($asset->assigned_type)->toBeNull();

    $this->assertDatabaseMissing('action_logs', [
        'action_type' => 'checkout',
        'target_type' => User::class,
        'target_id' => $targetUserInCompanyB->id,
        'item_type' => Asset::class,
        'item_id' => $asset->id,
    ]);
});

test('raw assigned to pair is ignored on update', function () {
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
    expect($asset->assigned_to)->toEqual($originalAssignedTo, 'assigned_to must not change via the raw pair');
    $this->assertDatabaseMissing('action_logs', [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'checkout',
    ]);
});

test('raw assigned to without assigned type is ignored on update', function () {
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
});

test('raw assigned to with bad assigned type is ignored on update', function () {
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
});

test('raw assigned type without assigned to is ignored on update', function () {
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
});

test('checkout to deleted user fails on asset update', function () {
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
    expect($asset->assigned_to)->toBeNull();
    expect($asset->assigned_type)->toBeNull();
});

test('checkout to location on asset update', function () {
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
    expect($asset->assigned_to)->toEqual($assigned_location->id);
    expect('App\Models\Location')->toEqual($asset->assigned_type);
});

test('checkout to deleted location fails on asset update', function () {
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
    expect($asset->assigned_to)->toBeNull();
    expect($asset->assigned_type)->toBeNull();
});

test('checkout asset on asset update', function () {
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
    expect($asset->assigned_to)->toEqual($assigned_asset->id);
    expect('App\Models\Asset')->toEqual($asset->assigned_type);
});

test('checkout to deleted asset fails on asset update', function () {
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
    expect($asset->assigned_to)->toBeNull();
    expect($asset->assigned_type)->toBeNull();
});

test('asset cannot be updated by user in separate company', function () {
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
});

test('custom field cannot be updated if not on current asset model', function () {
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
});

test('updating next audit date creates update log entry', function () {
    $asset = Asset::factory()->create(['next_audit_date' => now()->addMonths(3)->toDateString()]);

    $this->actingAsForApi(User::factory()->editAssets()->create())
        ->patchJson(route('api.assets.update', $asset), [
            'next_audit_date' => now()->addMonths(6)->toDateString(),
        ])
        ->assertOk();

    $this->assertHasTheseActionLogs($asset, ['create', 'update']);
});

test('updating next audit date with other fields logs all changes', function () {
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
    expect($logMeta)->toHaveKey('name');
    expect($logMeta)->toHaveKey('next_audit_date');
    expect($logMeta['name']['old'])->toEqual('Old Name');
    expect($logMeta['name']['new'])->toEqual('New Name');
});

test('update denies checkout when actor lacks checkout permission', function () {
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

    expect($asset->assigned_to)->toBeNull('edit-only actor must not be able to change custody');
    expect($asset->assigned_type)->toBeNull();
    expect($asset->name)->toEqual('Original Name', 'name update should have rolled back with the failed checkout');
    $this->assertDatabaseMissing('action_logs', [
        'action_type' => 'checkout',
        'target_type' => User::class,
        'target_id' => $target->id,
        'item_type' => Asset::class,
        'item_id' => $asset->id,
    ]);
});

test('update denies reassignment of already checked out asset', function () {
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

    expect($asset->fresh()->assigned_to)->toEqual($originalHolder->id, 'custody must not have moved');
});

test('update denies checkout when asset status is not deployable', function () {
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

    expect($asset->fresh()->assigned_to)->toBeNull();
});
