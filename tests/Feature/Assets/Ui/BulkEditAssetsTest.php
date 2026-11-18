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

test('user with permissions can access page', function () {
    $user = User::factory()->viewAssets()->editAssets()->create();
    $assets = Asset::factory()->count(2)->create();

    $id_array = $assets->pluck('id')->toArray();

    $this->actingAs($user)->post('/hardware/bulkedit', [
        'ids' => $id_array,
        'order' => 'asc',
        'bulk_actions' => 'edit',
        'sort' => 'id',
    ])->assertStatus(200);
});

test('handles model being deleted', function () {
    $this->withoutExceptionHandling();

    $user = User::factory()->viewAssets()->editAssets()->create();
    $assets = Asset::factory()->count(2)->create();

    $assets->first()->model->forceDelete();

    $id_array = $assets->pluck('id')->toArray();

    $this->actingAs($user)->post('/hardware/bulkedit', [
        'ids' => $id_array,
        'order' => 'asc',
        'bulk_actions' => 'edit',
        'sort' => 'id',
    ])->assertStatus(200);
});

test('standard user cannot access page', function () {
    $user = User::factory()->create();
    $assets = Asset::factory()->count(2)->create();

    $id_array = $assets->pluck('id')->toArray();

    $this->actingAs($user)->post('/hardware/bulkedit', [
        'ids' => $id_array,
        'order' => 'asc',
        'bulk_actions' => 'edit',
        'sort' => 'id',
    ])->assertStatus(403);
});

test('bulk edit assets accepts all possible attributes', function () {
    // sets up all needed models and attributes on the assets
    // this test does not deal with custom fields - will be dealt with in separate cases
    $status1 = Statuslabel::factory()->create();
    $status2 = Statuslabel::factory()->create();
    $model1 = AssetModel::factory()->create();
    $model2 = AssetModel::factory()->create();
    $supplier1 = Supplier::factory()->create();
    $supplier2 = Supplier::factory()->create();
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $assets = Asset::factory()->count(10)->create([
        'name' => 'Old Asset Name',
        'purchase_date' => '2023-01-01',
        'expected_checkin' => '2023-01-01',
        'status_id' => $status1->id,
        'model_id' => $model1->id,
        // skipping locations on this test, it deserves it's own test
        'purchase_cost' => 1234.90,
        'supplier_id' => $supplier1->id,
        'company_id' => $company1->id,
        'warranty_months' => 24,
        'next_audit_date' => '2024-06-01',
        'requestable' => false,
        'notes' => 'This is a new note!',
    ]);

    // gets the ids together to submit to the endpoint
    $id_array = $assets->pluck('id')->toArray();

    // submits the ids and new values for each attribute. order_number
    // was dropped from the payload when the parent Asset column moved
    // to the Orders / OrderItems data model; the bulk-edit path no
    // longer touches it.
    $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
        'ids' => $id_array,
        'name' => 'New Asset Name',
        'purchase_date' => '2024-01-01',
        'expected_checkin' => '2024-01-01',
        'status_id' => $status2->id,
        'model_id' => $model2->id,
        'purchase_cost' => 5678.92,
        'supplier_id' => $supplier2->id,
        'company_id' => $company2->id,
        'warranty_months' => 36,
        'next_audit_date' => '2025-01-01',
        'requestable' => true,
        'notes' => 'This is a newer note!',
    ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors();

    // asserts that each asset has the updated values
    Asset::findMany($id_array)->each(function (Asset $asset) use ($status2, $model2, $supplier2, $company2) {
        expect($asset->purchase_date->format('Y-m-d'))->toEqual('2024-01-01');
        expect($asset->expected_checkin->format('Y-m-d'))->toEqual('2024-01-01');
        expect($asset->status_id)->toEqual($status2->id);
        expect($asset->name)->toEqual('New Asset Name');
        expect($asset->model_id)->toEqual($model2->id);
        expect($asset->purchase_cost)->toEqual(5678.92);
        expect($asset->supplier_id)->toEqual($supplier2->id);
        expect($asset->company_id)->toEqual($company2->id);
        expect($asset->warranty_months)->toEqual(36);
        expect($asset->next_audit_date)->toEqual('2025-01-01');
        // shouldn't requestable be cast as a boolean??? it's not.
        expect($asset->requestable)->toEqual(1);
        expect($asset->notes)->toEqual('This is a newer note!');
    });
});

test('bulk edit assets nulls out fields if selected', function () {
    // sets up all needed models and attributes on the assets
    // this test does not deal with custom fields - will be dealt with in separate cases
    $status1 = Statuslabel::factory()->create();
    $status2 = Statuslabel::factory()->create();
    $model1 = AssetModel::factory()->create();
    $model2 = AssetModel::factory()->create();
    $supplier1 = Supplier::factory()->create();
    $supplier2 = Supplier::factory()->create();
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $assets = Asset::factory()->count(10)->create([
        'name' => 'Old Asset Name',
        'purchase_date' => '2023-01-01',
        'expected_checkin' => '2023-01-01',
        'status_id' => $status1->id,
        'model_id' => $model1->id,
        // skipping locations on this test, it deserves it's own test
        'purchase_cost' => 1234.90,
        'supplier_id' => $supplier1->id,
        'company_id' => $company1->id,
        'warranty_months' => 24,
        'next_audit_date' => '2024-06-01',
        'requestable' => false,
        'notes' => 'This is a note that will be deleted',
    ]);

    // gets the ids together to submit to the endpoint
    $id_array = $assets->pluck('id')->toArray();

    // submits the ids and new values for each attribute
    $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
        'ids' => $id_array,
        'null_name' => '1',
        'null_purchase_date' => '1',
        'null_expected_checkin_date' => '1',
        'null_next_audit_date' => '1',
        'null_notes' => '1',
        'status_id' => $status2->id,
        'model_id' => $model2->id,
    ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors();

    // asserts that each asset has the updated values
    Asset::findMany($id_array)->each(function (Asset $asset) {
        expect($asset->name)->toBeNull();
        expect($asset->purchase_date)->toBeNull();
        expect($asset->expected_checkin)->toBeNull();
        expect($asset->next_audit_date)->toBeNull();
        expect($asset->notes)->toBeNull();
    });
});

test('bulk edit writes default and current location independently', function () {
    $originalDefault = Location::factory()->create();
    $originalCurrent = Location::factory()->create();
    $newDefault = Location::factory()->create();
    $newCurrent = Location::factory()->create();

    $assets = Asset::factory()->count(3)->create([
        'rtd_location_id' => $originalDefault->id,
        'location_id' => $originalCurrent->id,
    ]);

    $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
        'ids' => $assets->pluck('id')->toArray(),
        'rtd_location_id' => $newDefault->id,
        'location_id' => $newCurrent->id,
    ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors();

    Asset::findMany($assets->pluck('id'))->each(function (Asset $asset) use ($newDefault, $newCurrent) {
        expect($asset->rtd_location_id)->toEqual($newDefault->id);
        expect($asset->location_id)->toEqual($newCurrent->id);
    });
});

test('bulk edit clears default and current location when null checkboxes set', function () {
    // Regression test for #11779. Pre-fix the bulk edit form had
    // no way to null out rtd_location_id or location_id, only the
    // single-asset edit path cleared them.
    $default = Location::factory()->create();
    $current = Location::factory()->create();
    $otherDefault = Location::factory()->create();

    $assets = Asset::factory()->count(3)->create([
        'rtd_location_id' => $default->id,
        'location_id' => $current->id,
    ]);

    $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
        'ids' => $assets->pluck('id')->toArray(),
        'null_rtd_location_id' => '1',
        'null_location_id' => '1',
    ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors();

    Asset::findMany($assets->pluck('id'))->each(function (Asset $asset) {
        expect($asset->rtd_location_id)->toBeNull();
        expect($asset->location_id)->toBeNull();
    });

    // Mixed path: set default, clear current, independently.
    $moreAssets = Asset::factory()->count(2)->create([
        'rtd_location_id' => $default->id,
        'location_id' => $current->id,
    ]);

    $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
        'ids' => $moreAssets->pluck('id')->toArray(),
        'rtd_location_id' => $otherDefault->id,
        'null_location_id' => '1',
    ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors();

    Asset::findMany($moreAssets->pluck('id'))->each(function (Asset $asset) use ($otherDefault) {
        expect($asset->rtd_location_id)->toEqual($otherDefault->id);
        expect($asset->location_id)->toBeNull();
    });
});

test('bulk edit assets accepts and updates unencrypted custom fields', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    CustomField::factory()->ram()->create();
    CustomField::factory()->cpu()->create();

    // when getting the custom field directly from the factory the field has not been fully created yet
    // so we have to do a query afterwards to get the actual model :shrug:
    $ram = CustomField::where('name', 'RAM')->first();
    $cpu = CustomField::where('name', 'CPU')->first();

    $assets = Asset::factory()->count(10)->hasMultipleCustomFields([$ram, $cpu])->create([
        $ram->db_column => 8,
        $cpu->db_column => '2.1',
    ]);

    $id_array = $assets->pluck('id')->toArray();

    $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
        'ids' => $id_array,
        $ram->db_column => 16,
        $cpu->db_column => '4.1',
    ])->assertStatus(302);

    Asset::findMany($id_array)->each(function (Asset $asset) use ($ram, $cpu) {
        expect($asset->{$ram->db_column})->toEqual(16);
        expect($asset->{$cpu->db_column})->toEqual('4.1');
    });
});

test('bulk edit assets nulls custom fields if selected', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    CustomField::factory()->ram()->create();
    CustomField::factory()->cpu()->create();
    CustomField::factory()->phone()->create();

    // when getting the custom field directly from the factory the field has not been fully created yet
    // so we have to do a query afterwards to get the actual model :shrug:
    $ram = CustomField::where('name', 'RAM')->first();
    $cpu = CustomField::where('name', 'CPU')->first();
    $phone = CustomField::where('name', 'Phone Number')->first();

    $assets = Asset::factory()->count(10)->hasMultipleCustomFields([$ram, $cpu])->create([
        $ram->db_column => 8,
        $cpu->db_column => '2.1',
    ]);

    $id_array = $assets->pluck('id')->toArray();

    $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
        'ids' => $id_array,
        $ram->db_column => 16,
        $cpu->db_column => '4.1',
        'null'.$phone->db_column => '8304997586',
    ])->assertStatus(302);

    $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
        'ids' => $id_array,
        null.$phone->db_column => 1,
    ]);

    Asset::findMany($id_array)->each(function (Asset $asset) use ($ram, $cpu, $phone) {
        expect($asset->{$ram->db_column})->toEqual(16);
        expect($asset->{$cpu->db_column})->toEqual('4.1');
        expect($asset->{$phone->db_column})->toEqual(null);
    });
});

test('bulk edit only fills fields attached to each asset and honors explicit nulls', function () {
    [$firstField, $secondField] = CustomField::factory()->count(2)->create();
    $first = Asset::factory()->hasMultipleCustomFields([$firstField])->create([$firstField->db_column => 'original']);
    $second = Asset::factory()->hasMultipleCustomFields([$secondField])->create([$secondField->db_column => 'original']);

    $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
        'ids' => [$first->id, $second->id],
        $firstField->db_column => '0',
        'null'.$secondField->db_column => '1',
    ])->assertRedirect()->assertSessionMissing('bulk_asset_errors');

    expect($first->fresh()->{$firstField->db_column})->toBe('0');
    expect($first->fresh()->getRawOriginal($secondField->db_column))->toBeNull();
    expect($second->fresh()->{$secondField->db_column})->toBeNull();
    expect($second->fresh()->getRawOriginal($firstField->db_column))->toBeNull();
});

test('bulk edit uses the new models fieldset when changing models', function () {
    [$oldField, $newField] = CustomField::factory()->count(2)->create();
    $asset = Asset::factory()->hasMultipleCustomFields([$oldField])->create();
    $newModel = AssetModel::factory()->hasMultipleCustomFields([$newField])->create();

    $this->actingAs(User::factory()->editAssets()->create())->post(route('hardware/bulksave'), [
        'ids' => [$asset->id],
        'model_id' => $newModel->id,
        $newField->db_column => 'new value',
        $oldField->db_column => 'ignored',
    ])->assertRedirect()->assertSessionMissing('bulk_asset_errors');

    expect($asset->fresh()->model_id)->toBe($newModel->id);
    expect($asset->fresh()->{$newField->db_column})->toBe('new value');
    expect($asset->fresh()->getRawOriginal($oldField->db_column))->toBeNull();
});

test('bulk edit keeps the admin requirement even when the actor can view encrypted fields', function () {
    $field = CustomField::factory()->encrypt()->create();
    $asset = Asset::factory()->hasEncryptedCustomField($field)->create([$field->db_column => 'original']);
    $actor = User::factory()->editAssets()->viewEncryptedCustomFields()->create();

    $this->actingAs($actor)->post(route('hardware/bulksave'), [
        'ids' => [$asset->id],
        $field->db_column => 'replacement',
        'null'.$field->db_column => '1',
    ])->assertRedirect()->assertSessionMissing('bulk_asset_errors');

    expect(Crypt::decrypt($asset->fresh()->getRawOriginal($field->db_column)))->toBe('original');
});

test('bulk edit assets accepts and updates encrypted custom fields', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    CustomField::factory()->testEncrypted()->create();

    $encrypted = CustomField::where('name', 'Test Encrypted')->first();

    $assets = Asset::factory()->count(10)->hasEncryptedCustomField($encrypted)->create([
        $encrypted->db_column => 'Original Encrypted Text',
    ]);

    $id_array = $assets->pluck('id')->toArray();

    $this->actingAs(User::factory()->admin()->create())->post(route('hardware/bulksave'), [
        'ids' => $id_array,
        $encrypted->db_column => 'New Encrypted Text',
    ])->assertStatus(302);

    Asset::findMany($id_array)->each(function (Asset $asset) use ($encrypted) {
        expect(Crypt::decrypt($asset->getRawOriginal($encrypted->db_column)))->toEqual('New Encrypted Text');
    });
});

test('bulk edit assets requires admin to update encrypted custom fields', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on mysql');
    $edit_user = User::factory()->editAssets()->create();
    $admin_user = User::factory()->admin()->create();

    CustomField::factory()->testEncrypted()->create();

    $encrypted = CustomField::where('name', 'Test Encrypted')->first();

    $admin_assets = Asset::factory()->count(5)->hasEncryptedCustomField($encrypted)->create([
        $encrypted->db_column => 'Original Encrypted Text',
    ]);

    $standard_assets = Asset::factory()->count(5)->hasEncryptedCustomField($encrypted)->create([
        $encrypted->db_column => 'Original Encrypted Text',
    ]);

    $admin_id_array = $admin_assets->pluck('id')->toArray();
    $standard_id_array = $standard_assets->pluck('id')->toArray();

    $this->actingAs($admin_user)->post(route('hardware/bulksave'), [
        'ids' => $admin_id_array,
        $encrypted->db_column => 'New Encrypted Text',
    ])->assertStatus(302);

    // do we want to return an error when this happens???
    $this->actingAs($edit_user)->post(route('hardware/bulksave'), [
        'ids' => $standard_id_array,
        $encrypted->db_column => 'New Encrypted Text',
    ])->assertStatus(302);

    Asset::findMany($admin_id_array)->each(function (Asset $asset) use ($encrypted) {
        expect(Crypt::decrypt($asset->getRawOriginal($encrypted->db_column)))->toEqual('New Encrypted Text');
    });

    Asset::findMany($standard_id_array)->each(function (Asset $asset) use ($encrypted) {
        expect(Crypt::decrypt($asset->getRawOriginal($encrypted->db_column)))->toEqual('Original Encrypted Text');
    });
});

test('same origin referer is stored as bulk back url', function () {
    $user = User::factory()->viewAssets()->editAssets()->create();
    $assets = Asset::factory()->count(2)->create();
    $originUrl = route('hardware.index').'?status_type=Deployed';

    $this->actingAs($user)
        ->from($originUrl)
        ->post('/hardware/bulkedit', [
            'ids' => $assets->pluck('id')->toArray(),
            'bulk_actions' => 'edit',
            'sort' => 'id',
            'order' => 'asc',
        ])
        ->assertSessionHas('bulk_back_url', $originUrl);
});

test('offsite referer is not stored as bulk back url', function () {
    $user = User::factory()->viewAssets()->editAssets()->create();
    $assets = Asset::factory()->count(2)->create();

    $this->actingAs($user)
        ->from('https://evil.example.com/phish')
        ->post('/hardware/bulkedit', [
            'ids' => $assets->pluck('id')->toArray(),
            'bulk_actions' => 'edit',
            'sort' => 'id',
            'order' => 'asc',
        ])
        ->assertSessionMissing('bulk_back_url');
});

test('bulk update redirects to stashed same origin url', function () {
    $user = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();
    $originUrl = route('hardware.index').'?status_type=Deployed';

    $this->actingAs($user)
        ->withSession(['bulk_back_url' => $originUrl])
        ->post('/hardware/bulksave', [
            'ids' => [$asset->id => '1'],
        ])
        ->assertRedirect($originUrl);
});

test('bulk update falls back to hardware index when stashed url is missing', function () {
    $user = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();

    $this->actingAs($user)
        ->post('/hardware/bulksave', [
            'ids' => [$asset->id => '1'],
        ])
        ->assertRedirect(route('hardware.index'));
});

test('bulk update ignores poisoned stashed url', function () {
    // If the write-side gate were ever bypassed, the read-side
    // helper coalescing must still keep the redirect on-domain.
    $user = User::factory()->superuser()->create();
    $asset = Asset::factory()->create();

    $this->actingAs($user)
        ->withSession(['bulk_back_url' => 'https://evil.example.com/steal-session'])
        ->post('/hardware/bulksave', [
            'ids' => [$asset->id => '1'],
        ])
        ->assertRedirect(route('hardware.index'));
});
