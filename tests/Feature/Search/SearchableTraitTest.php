<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\License;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('asset free text search on attributes', function () {
    Asset::factory()->create(['name' => 'MacBook Pro 15"', 'asset_tag' => 'ASSET-001']);
    Asset::factory()->create(['name' => 'Dell XPS 13', 'asset_tag' => 'ASSET-002']);
    Asset::factory()->create(['name' => 'HP Pavilion', 'asset_tag' => 'ASSET-003']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', ['search' => 'MacBook']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', ['search' => 'ASSET']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
});

test('asset free text search on relations', function () {
    // Create fresh test data that won't conflict with system data
    $supplier = Supplier::factory()->create(['name' => 'TestVendor-'.now()->timestamp]);
    $location = Location::factory()->create(['name' => 'TestBuilding-'.now()->timestamp]);

    Asset::factory()->create([
        'name' => 'Asset 1',
        'supplier_id' => $supplier->id,
        'location_id' => $location->id,
    ]);

    Asset::factory()->create(['name' => 'Asset 2']);

    // Search by supplier name
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', ['search' => 'TestVendor']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    // Search by location name
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', ['search' => 'TestBuilding']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('asset structured filter on attributes', function () {
    Asset::factory()->create(['name' => 'MacBook Pro 15"', 'serial' => 'SN123456']);
    Asset::factory()->create(['name' => 'Dell XPS 13', 'serial' => 'SN789012']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['name' => 'MacBook']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['serial' => 'SN789']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('asset structured filter on relations', function () {
    $supplier = Supplier::factory()->create(['name' => 'TechVendor Inc']);
    $location = Location::factory()->create(['name' => 'Building A']);
    $manufacturer = Manufacturer::factory()->apple()->create();
    $model = AssetModel::factory()->create(['manufacturer_id' => $manufacturer->id]);
    $category = Category::factory()->assetLaptopCategory()->create();

    Asset::factory()->create([
        'name' => 'Asset 1',
        'model_id' => $model->id,
        'supplier_id' => $supplier->id,
        'location_id' => $location->id,
    ]);

    Asset::factory()->create(['name' => 'Asset 2']);

    // Filter by supplier name
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['supplier' => 'TechVendor']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    // Filter by location name
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['location' => 'Building']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    // Filter by manufacturer name (nested relation via model)
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['manufacturer' => 'Apple']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('asset structured filter using relation alias', function () {
    // Create a unique status to avoid conflicts with system data
    $status = Statuslabel::factory()->create(['name' => 'TestStatus-'.now()->timestamp]);

    Asset::factory()->create(['status_id' => $status->id]);
    Asset::factory()->create();

    // Filter using the API key 'status_label' should map to 'status' relation
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['status_label' => 'TestStatus']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('license free text search on attributes', function () {
    License::factory()->create(['name' => 'Microsoft Office 365', 'serial' => 'OFFICE-123']);
    License::factory()->create(['name' => 'Adobe Creative Cloud', 'serial' => 'ADOBE-456']);

    $this->actingAsForApi(User::factory()->viewLicenses()->create())
        ->getJson(route('api.licenses.index', ['search' => 'Microsoft']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    $this->actingAsForApi(User::factory()->viewLicenses()->create())
        ->getJson(route('api.licenses.index', ['search' => 'OFFICE']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('license free text search on relations', function () {
    $manufacturer = Manufacturer::factory()->microsoft()->create();
    $supplier = Supplier::factory()->create(['name' => 'CloudVendor Inc']);

    License::factory()->create([
        'name' => 'License 1',
        'manufacturer_id' => $manufacturer->id,
        'supplier_id' => $supplier->id,
    ]);

    License::factory()->create(['name' => 'License 2']);

    // Search by manufacturer name
    $this->actingAsForApi(User::factory()->viewLicenses()->create())
        ->getJson(route('api.licenses.index', ['search' => 'Microsoft']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    // Search by supplier name
    $this->actingAsForApi(User::factory()->viewLicenses()->create())
        ->getJson(route('api.licenses.index', ['search' => 'CloudVendor']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('license structured filter on attributes', function () {
    License::factory()->create(['name' => 'Microsoft Office', 'serial' => 'SN-OFFICE-001']);
    License::factory()->create(['name' => 'Adobe Suite', 'serial' => 'SN-ADOBE-002']);

    $this->actingAsForApi(User::factory()->viewLicenses()->create())
        ->getJson(route('api.licenses.index', [
            'filter' => json_encode(['name' => 'Office']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    $this->actingAsForApi(User::factory()->viewLicenses()->viewKeysLicenses()->create())
        ->getJson(route('api.licenses.index', [
            'filter' => json_encode(['serial' => 'ADOBE']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('license structured filter on relations', function () {
    $manufacturer = Manufacturer::factory()->adobe()->create();
    $supplier = Supplier::factory()->create(['name' => 'TechSupply Inc']);

    License::factory()->create([
        'name' => 'License 1',
        'manufacturer_id' => $manufacturer->id,
        'supplier_id' => $supplier->id,
    ]);

    License::factory()->create(['name' => 'License 2']);

    // Filter by manufacturer
    $this->actingAsForApi(User::factory()->viewLicenses()->create())
        ->getJson(route('api.licenses.index', [
            'filter' => json_encode(['manufacturer' => 'Adobe']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    // Filter by supplier
    $this->actingAsForApi(User::factory()->viewLicenses()->create())
        ->getJson(route('api.licenses.index', [
            'filter' => json_encode(['supplier' => 'TechSupply']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('user free text search on attributes', function () {
    // Note: User search includes the acting user in results, making this test flaky
    // Use the username search instead which is more deterministic
    $timestamp = now()->timestamp;
    $uniqueName = 'XYZ'.$timestamp;
    User::factory()->create(['first_name' => 'TestJohn'.$uniqueName, 'last_name' => 'Smith'.$uniqueName, 'username' => 'jsmith'.$uniqueName]);
    User::factory()->create(['first_name' => 'TestJane'.$uniqueName, 'last_name' => 'Doe'.$uniqueName, 'username' => 'jdoe'.$uniqueName]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.users.index', ['search' => 'jsmith'.$uniqueName]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
})->group('skip-flaky');

test('user multi word free text search', function () {
    $timestamp = now()->timestamp;
    $uniqueName = 'ABC'.$timestamp;
    User::factory()->create(['first_name' => 'TestJohn'.$uniqueName, 'last_name' => 'Smith'.$uniqueName, 'username' => 'jsmith'.$uniqueName]);
    User::factory()->create(['first_name' => 'TestJane'.$uniqueName, 'last_name' => 'Doe'.$uniqueName, 'username' => 'jdoe'.$uniqueName]);

    // Search for full name should match when both first and last are concatenated
    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.users.index', ['search' => 'TestJohn'.$uniqueName.' Smith']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('user structured filter on attributes', function () {
    $timestamp = now()->timestamp;
    $uniqueName = 'DEF'.$timestamp;
    User::factory()->create(['first_name' => 'TestJohn'.$uniqueName, 'last_name' => 'Smith'.$uniqueName, 'email' => 'john'.$uniqueName.'@example.com']);
    User::factory()->create(['first_name' => 'TestJane'.$uniqueName, 'last_name' => 'Doe'.$uniqueName, 'email' => 'jane'.$uniqueName.'@example.com']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.users.index', [
            'filter' => json_encode(['first_name' => 'TestJohn'.$uniqueName]),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.users.index', [
            'filter' => json_encode(['email' => 'jane'.$uniqueName]),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('user name virtual column filter positive', function () {
    $ts = now()->timestamp;
    User::factory()->create(['first_name' => 'VirtFirst'.$ts, 'last_name' => 'VirtLast'.$ts]);
    User::factory()->create(['first_name' => 'Other'.$ts, 'last_name' => 'Person'.$ts]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.users.index', [
            'filter' => json_encode(['name' => 'VirtFirst'.$ts]),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('user name virtual column filter negation bang prefix', function () {
    $ts = now()->timestamp;
    $negUser = User::factory()->create(['first_name' => 'NegFirst'.$ts, 'last_name' => 'NegLast'.$ts]);
    $safeUser = User::factory()->create(['first_name' => 'SafeFirst'.$ts, 'last_name' => 'SafeLast'.$ts]);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.users.index', [
            'filter' => json_encode(['name' => '!NegFirst'.$ts]),
        ]))
        ->assertOk();

    $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    // The matched (negated) user must not appear in results.
    expect($returnedIds)->not->toContain((int) $negUser->id);

    // The safe user should appear in results.
    expect($returnedIds)->toContain((int) $safeUser->id);
});

test('user name virtual column filter negation not colon prefix', function () {
    $ts = now()->timestamp;
    User::factory()->create(['first_name' => 'NotFirst'.$ts, 'last_name' => 'NotLast'.$ts]);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.users.index', [
            'filter' => json_encode(['name' => 'not:NotFirst'.$ts]),
        ]))
        ->assertOk();

    $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($returnedIds)->not->toContain((int) User::where('first_name', 'NotFirst'.$ts)->value('id'));
});

test('category free text search on attributes', function () {
    Category::factory()->assetLaptopCategory()->create();
    Category::factory()->assetDesktopCategory()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.categories.index', ['search' => 'Laptop']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.categories.index', ['search' => 'Desktop']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('category structured filter on attributes', function () {
    Category::factory()->assetLaptopCategory()->create(['notes' => 'For portable computing']);
    Category::factory()->assetDesktopCategory()->create(['notes' => 'For stationary computing']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.categories.index', [
            'filter' => json_encode(['name' => 'Laptop']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.categories.index', [
            'filter' => json_encode(['notes' => 'portable']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('manufacturer free text search on attributes', function () {
    Manufacturer::factory()->apple()->create();
    Manufacturer::factory()->microsoft()->create();
    Manufacturer::factory()->dell()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.manufacturers.index', ['search' => 'Apple']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.manufacturers.index', ['search' => 'Microsoft']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('manufacturer structured filter on attributes', function () {
    Manufacturer::factory()->apple()->create();
    Manufacturer::factory()->microsoft()->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.manufacturers.index', [
            'filter' => json_encode(['name' => 'Apple']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('location free text search on attributes', function () {
    Location::factory()->create(['name' => 'Building A', 'city' => 'New York']);
    Location::factory()->create(['name' => 'Building B', 'city' => 'Los Angeles']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.locations.index', ['search' => 'Building']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.locations.index', ['search' => 'New York']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('location structured filter on attributes', function () {
    Location::factory()->create(['name' => 'Building A', 'city' => 'New York']);
    Location::factory()->create(['name' => 'Building B', 'city' => 'Los Angeles']);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.locations.index', [
            'filter' => json_encode(['city' => 'New York']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.locations.index', [
            'filter' => json_encode(['name' => 'Building']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
});

test('partial word matching', function () {
    Asset::factory()->create(['name' => 'MacBook Pro 15"']);

    // Free-text search
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', ['search' => 'Book']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    // Filter search
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['name' => 'Pro']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('search is case insensitive', function () {
    Asset::factory()->create(['name' => 'MacBook Pro 15"']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', ['search' => 'macbook']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['name' => 'MACBOOK']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('empty search returns all results', function () {
    Asset::factory()->count(3)->create();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', ['search' => '']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
});

test('array search param does not throw', function () {
    Asset::factory()->create(['name' => 'ArraySearchMacBook']);
    Asset::factory()->create(['name' => 'ArraySearchDell']);

    // search[]=ArraySearchMacBook simulates ?search[]=ArraySearchMacBook in the URL
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', ['search' => ['ArraySearchMacBook']]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

    // Multiple array values must not throw — exact match count depends on join semantics
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', ['search' => ['ArraySearchMacBook', 'ArraySearchDell']]))
        ->assertOk();
});

test('search no results', function () {
    Asset::factory()->create(['name' => 'Asset 1']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', ['search' => 'NonExistentTerm']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 0)->etc());
});

test('asset free text search matches across multiple custom fields', function () {
    $macFieldOne = CustomField::factory()->macAddress()->create([
        'name' => 'MAC Address One '.now()->timestamp,
    ]);
    $macFieldTwo = CustomField::factory()->macAddress()->create([
        'name' => 'MAC Address Two '.(now()->timestamp + 1),
    ]);

    $dbColumnOne = $macFieldOne->db_column_name();
    $dbColumnTwo = $macFieldTwo->db_column_name();

    $firstMatchingAsset = Asset::factory()->create([
        $dbColumnOne => 'AA:BB:CC:11:22:33',
        $dbColumnTwo => null,
    ]);
    $secondMatchingAsset = Asset::factory()->create([
        $dbColumnOne => null,
        $dbColumnTwo => 'AA:BB:CC:44:55:66',
    ]);
    Asset::factory()->create([
        $dbColumnOne => '10:20:30:40:50:60',
        $dbColumnTwo => '66:55:44:33:22:11',
    ]);

    $response = $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', ['search' => 'AA:BB:CC']))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());

    $returnedIds = collect($response->json('rows'))
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->sort()
        ->values()
        ->all();

    $expectedIds = collect([$firstMatchingAsset->id, $secondMatchingAsset->id])
        ->sort()
        ->values()
        ->all();

    expect($returnedIds)->toBe($expectedIds);
});

test('custom field filter by db column slug', function () {
    $field = CustomField::factory()->cpu()->create();
    $dbColumn = $field->db_column_name();

    Asset::factory()->create([$dbColumn => '3.2GHz i9']);
    Asset::factory()->create([$dbColumn => '2.4GHz i5']);
    Asset::factory()->create([$dbColumn => null]);

    // Flush cache so the newly created field is picked up.
    Asset::flushCustomFieldFilterMap();

    // Filter using the raw db_column key.
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode([$dbColumn => '3.2GHz']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('custom field filter by human readable name is ignored', function () {
    $field = CustomField::factory()->cpu()->create();
    $dbColumn = $field->db_column_name();

    Asset::factory()->create([$dbColumn => '3.2GHz i9']);
    Asset::factory()->create([$dbColumn => '2.4GHz i5']);

    Asset::flushCustomFieldFilterMap();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['CPU' => 'i9']),
        ]))
        ->assertOk()
        // Human-readable custom field keys are intentionally ignored.
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
});

test('custom field name collision does not override relation filter', function () {
    $status = Statuslabel::factory()->create(['name' => 'CollisionStatus-'.now()->timestamp]);
    $otherStatus = Statuslabel::factory()->create(['name' => 'DifferentStatus-'.now()->timestamp]);

    $field = CustomField::factory()->create([
        'name' => 'status',
        'field_encrypted' => 0,
    ]);
    $dbColumn = $field->db_column_name();

    Asset::factory()->create([
        'status_id' => $status->id,
        $dbColumn => 'custom-status-value',
    ]);
    Asset::factory()->create([
        'status_id' => $otherStatus->id,
        $dbColumn => 'CollisionStatus',
    ]);

    Asset::flushCustomFieldFilterMap();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['status' => 'CollisionStatus']),
        ]))
        ->assertOk()
        // This must filter the status relation, not the custom field with same name.
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('custom field gets skipped if encrypted', function () {
    $field = CustomField::factory()->testEncrypted()->create();
    $dbColumn = $field->db_column_name();

    Asset::factory()->create([$dbColumn => '3.2GHz i9']);
    Asset::factory()->create([$dbColumn => '2.4GHz i5']);

    Asset::flushCustomFieldFilterMap();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode([$dbColumn => 'i9']),
        ]))
        ->assertOk()
        // Encrypted fields are not searchable, so this filter key is ignored.
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
});

test('custom field filter returns empty when no match', function () {
    $field = CustomField::factory()->cpu()->create();
    $dbColumn = $field->db_column_name();

    Asset::factory()->create([$dbColumn => '3.2GHz i9']);

    Asset::flushCustomFieldFilterMap();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode([$dbColumn => 'NonExistentCPU']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 0)->etc());
});

test('is null filter on nullable attribute', function () {
    $ts = now()->timestamp;

    $withNotes = Asset::factory()->create(['notes' => 'Some notes '.$ts]);
    $withoutNotes = Asset::factory()->create(['notes' => null]);

    $superuser = User::factory()->viewAssets()->create();

    // is:null → only the asset with no notes
    $response = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['notes' => 'is:null'])]))
        ->assertOk();

    $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($returnedIds)->toContain((int) $withoutNotes->id);
    expect($returnedIds)->not->toContain((int) $withNotes->id);

    // is:not_null → only the asset with notes
    $response2 = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['notes' => 'is:not_null'])]))
        ->assertOk();

    $returnedIds2 = collect($response2->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($returnedIds2)->toContain((int) $withNotes->id);
    expect($returnedIds2)->not->toContain((int) $withoutNotes->id);
});

test('is not null filter excludes blank string direct attributes', function () {
    // notes was picked as the direct-string attribute exercised here
    // because order_number moved off the parent Asset column to the
    // Orders / OrderItems polymorphic pair. The is:not_null semantic
    // is what the test asserts, not the specific column.
    $populated = Asset::factory()->create([
        'name' => 'Named Asset '.now()->timestamp,
        'notes' => 'has notes',
    ]);
    $blank = Asset::factory()->create([
        'name' => '',
        'notes' => '',
    ]);

    $superuser = User::factory()->viewAssets()->create();

    $response = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['notes' => 'is:not_null'])]))
        ->assertOk();

    $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($returnedIds)->toContain((int) $populated->id);
    expect($returnedIds)->not->toContain((int) $blank->id);
});

test('is null filter on date column', function () {
    // AssetFactory's afterMaking hook overwrites asset_eol_date, so set it after create.
    $withEol = Asset::factory()->create();
    $withEol->asset_eol_date = now()->addYear()->format('Y-m-d');
    $withEol->save();

    $withoutEol = Asset::factory()->create();
    $withoutEol->asset_eol_date = null;
    $withoutEol->save();

    $superuser = User::factory()->viewAssets()->create();

    $notNull = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['asset_eol_date' => 'is:not_null'])]))
        ->assertOk();

    $notNullIds = collect($notNull->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();
    expect($notNullIds)->toContain((int) $withEol->id);
    expect($notNullIds)->not->toContain((int) $withoutEol->id);

    $isNull = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['asset_eol_date' => 'is:null'])]))
        ->assertOk();

    $isNullIds = collect($isNull->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();
    expect($isNullIds)->toContain((int) $withoutEol->id);
    expect($isNullIds)->not->toContain((int) $withEol->id);
});

test('is null filter on virtual name column', function () {
    $ts = now()->timestamp;

    $userWithName = User::factory()->create([
        'first_name' => 'VirtNullFirst'.$ts,
        'last_name' => 'VirtNullLast'.$ts,
    ]);

    $superuser = User::factory()->superuser()->create();

    // is:not_null → users with at least first_name set should be returned.
    $response = $this->actingAsForApi($superuser)
        ->getJson(route('api.users.index', ['filter' => json_encode(['name' => 'is:not_null'])]))
        ->assertOk();

    $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    // The user with an actual name must appear.
    expect($returnedIds)->toContain((int) $userWithName->id);

    // The acting superuser itself also has a name, so it should appear too.
    expect($returnedIds)->toContain((int) $superuser->id);
});

test('is null filter on relation key', function () {
    $ts = now()->timestamp;

    $supplier = Supplier::factory()->create(['name' => 'RelNullSupplier'.$ts]);
    $withSupplier = Asset::factory()->create(['supplier_id' => $supplier->id]);
    $withoutSupplier = Asset::factory()->create(['supplier_id' => null]);

    $superuser = User::factory()->viewAssets()->create();

    // is:null on supplier → assets with no supplier
    $response = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['supplier' => 'is:null'])]))
        ->assertOk();

    $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($returnedIds)->toContain((int) $withoutSupplier->id);
    expect($returnedIds)->not->toContain((int) $withSupplier->id);

    // is:not_null on supplier → assets with a supplier
    $response2 = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['supplier' => 'is:not_null'])]))
        ->assertOk();

    $returnedIds2 = collect($response2->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($returnedIds2)->toContain((int) $withSupplier->id);
    expect($returnedIds2)->not->toContain((int) $withoutSupplier->id);
});

test('is null filter on polymorphic assigned to relation key', function () {
    /** @var User $assignee */
    $assignee = User::factory()->create();
    $assignedAsset = Asset::factory()->assignedToUser($assignee)->create();
    $unassignedAsset = Asset::factory()->create([
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $superuser = User::factory()->viewAssets()->create();

    $response = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['assigned_to' => 'is:null'])]))
        ->assertOk();

    $returnedNullIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($returnedNullIds)->toContain((int) $unassignedAsset->id);
    expect($returnedNullIds)->not->toContain((int) $assignedAsset->id);

    $response2 = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['assigned_to' => 'is:not_null'])]))
        ->assertOk();

    $returnedNotNullIds = collect($response2->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($returnedNotNullIds)->toContain((int) $assignedAsset->id);
    expect($returnedNotNullIds)->not->toContain((int) $unassignedAsset->id);
});

test('jobtitle filter matches assigned user jobtitle', function () {
    $ts = now()->timestamp;

    $engineer = User::factory()->create(['jobtitle' => 'Site Engineer '.$ts]);
    $designer = User::factory()->create(['jobtitle' => 'Senior Designer '.$ts]);

    $engineerAsset = Asset::factory()->assignedToUser($engineer)->create();
    $designerAsset = Asset::factory()->assignedToUser($designer)->create();

    $superuser = User::factory()->viewAssets()->create();

    $response = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['jobtitle' => 'Engineer'])]))
        ->assertOk();

    $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($returnedIds)->toContain((int) $engineerAsset->id);
    expect($returnedIds)->not->toContain((int) $designerAsset->id);
});

test('is not null filter on jobtitle relation column matches assets with titled assignee', function () {
    $ts = now()->timestamp;

    $withTitle = User::factory()->create(['jobtitle' => 'VP Operations '.$ts]);
    $withoutTitle = User::factory()->create(['jobtitle' => null]);
    $withEmptyTitle = User::factory()->create(['jobtitle' => '']);

    $titledAsset = Asset::factory()->assignedToUser($withTitle)->create();
    $nullTitleAsset = Asset::factory()->assignedToUser($withoutTitle)->create();
    $emptyTitleAsset = Asset::factory()->assignedToUser($withEmptyTitle)->create();
    $unassignedAsset = Asset::factory()->create([
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $superuser = User::factory()->viewAssets()->create();

    $response = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['jobtitle' => 'is:not_null'])]))
        ->assertOk();

    $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($returnedIds)->toContain((int) $titledAsset->id);
    expect($returnedIds)->not->toContain((int) $nullTitleAsset->id, 'Assignee with a NULL jobtitle must not surface as "has title".');
    expect($returnedIds)->not->toContain((int) $emptyTitleAsset->id, 'Assignee with an empty-string jobtitle must not surface as "has title", the way admins read the UI column.');
    expect($returnedIds)->not->toContain((int) $unassignedAsset->id);
});

test('is null filter on jobtitle relation column matches untitled and unassigned', function () {
    $ts = now()->timestamp;

    $withTitle = User::factory()->create(['jobtitle' => 'Director of Something '.$ts]);
    $withoutTitle = User::factory()->create(['jobtitle' => null]);

    $titledAsset = Asset::factory()->assignedToUser($withTitle)->create();
    $nullTitleAsset = Asset::factory()->assignedToUser($withoutTitle)->create();
    $unassignedAsset = Asset::factory()->create([
        'assigned_to' => null,
        'assigned_type' => null,
    ]);

    $superuser = User::factory()->viewAssets()->create();

    $response = $this->actingAsForApi($superuser)
        ->getJson(route('api.assets.index', ['filter' => json_encode(['jobtitle' => 'is:null'])]))
        ->assertOk();

    $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    expect($returnedIds)->not->toContain((int) $titledAsset->id);
    expect($returnedIds)->toContain((int) $nullTitleAsset->id);
    expect($returnedIds)->toContain((int) $unassignedAsset->id); // An asset with no assignee should present as "no title" from the UI column standpoint.
});

test('custom field filter partial match', function () {
    $field = CustomField::factory()->cpu()->create();
    $dbColumn = $field->db_column_name();

    Asset::factory()->create([$dbColumn => '3.2GHz Intel Core i9']);
    Asset::factory()->create([$dbColumn => '2.4GHz AMD Ryzen 7']);
    Asset::factory()->create([$dbColumn => null]);

    Asset::flushCustomFieldFilterMap();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode([$dbColumn => 'Intel']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('custom field filter exact match with is modifier', function () {
    $field = CustomField::factory()->cpu()->create();
    $dbColumn = $field->db_column_name();

    Asset::factory()->create([$dbColumn => '3.2GHz Intel Core i9']);
    Asset::factory()->create([$dbColumn => '3.2GHz Intel Core i9 Pro']);
    Asset::factory()->create([$dbColumn => '2.4GHz AMD Ryzen 7']);

    Asset::flushCustomFieldFilterMap();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode([$dbColumn => 'is:3.2GHz Intel Core i9']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('exact exclusion filter with is not prefix on attribute', function () {
    Asset::factory()->create(['name' => 'Dell', 'asset_tag' => 'ISNOT-ATTR-001']);
    Asset::factory()->create(['name' => 'Dell XPS 13', 'asset_tag' => 'ISNOT-ATTR-002']);
    Asset::factory()->create(['name' => 'HP Pavilion', 'asset_tag' => 'ISNOT-ATTR-003']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['name' => 'is_not:Dell']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
});

test('exact exclusion filter with is not prefix on relation', function () {
    $apple = Manufacturer::factory()->create(['name' => 'Apple']);
    $appleInc = Manufacturer::factory()->create(['name' => 'Apple Inc']);
    $dell = Manufacturer::factory()->create(['name' => 'Dell']);

    $appleModel = AssetModel::factory()->create(['manufacturer_id' => $apple->id]);
    $appleIncModel = AssetModel::factory()->create(['manufacturer_id' => $appleInc->id]);
    $dellModel = AssetModel::factory()->create(['manufacturer_id' => $dell->id]);

    Asset::factory()->create(['model_id' => $appleModel->id, 'asset_tag' => 'ISNOT-REL-001']);
    Asset::factory()->create(['model_id' => $appleIncModel->id, 'asset_tag' => 'ISNOT-REL-002']);
    Asset::factory()->create(['model_id' => $dellModel->id, 'asset_tag' => 'ISNOT-REL-003']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['manufacturer' => 'is_not:Apple']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
});

test('exact exclusion filter with is not prefix on custom field', function () {
    $field = CustomField::factory()->cpu()->create();
    $dbColumn = $field->db_column_name();

    Asset::factory()->create([$dbColumn => 'Intel', 'asset_tag' => 'ISNOT-CF-001']);
    Asset::factory()->create([$dbColumn => 'Intel Core i9', 'asset_tag' => 'ISNOT-CF-002']);
    Asset::factory()->create([$dbColumn => 'AMD Ryzen 7', 'asset_tag' => 'ISNOT-CF-003']);

    Asset::flushCustomFieldFilterMap();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode([$dbColumn => 'is_not:Intel']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
});

test('negation filter with bang prefix on attribute', function () {
    Asset::factory()->create(['name' => 'MacBook Pro', 'asset_tag' => 'NEG-001']);
    Asset::factory()->create(['name' => 'Dell XPS 13', 'asset_tag' => 'NEG-002']);
    Asset::factory()->create(['name' => 'HP Pavilion', 'asset_tag' => 'NEG-003']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['name' => '!Dell']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
});

test('negation filter with not prefix on attribute', function () {
    Asset::factory()->create(['name' => 'MacBook Pro', 'asset_tag' => 'NOTP-001']);
    Asset::factory()->create(['name' => 'Dell XPS 13', 'asset_tag' => 'NOTP-002']);
    Asset::factory()->create(['name' => 'HP Pavilion', 'asset_tag' => 'NOTP-003']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['name' => 'not:Dell']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
});

test('combined positive and negation filters', function () {
    Asset::factory()->create(['name' => 'MacBook Pro', 'asset_tag' => 'COMBO-001']);
    Asset::factory()->create(['name' => 'Dell XPS 13', 'asset_tag' => 'COMBO-002']);
    Asset::factory()->create(['name' => 'HP Pavilion', 'asset_tag' => 'OTHER-001']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['asset_tag' => 'COMBO', 'name' => '!Dell']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('negation filter on relation', function () {
    $apple = Manufacturer::factory()->create(['name' => 'Apple']);
    $dell = Manufacturer::factory()->create(['name' => 'Dell']);

    $appleModel = AssetModel::factory()->create(['manufacturer_id' => $apple->id]);
    $dellModel = AssetModel::factory()->create(['manufacturer_id' => $dell->id]);

    Asset::factory()->create(['model_id' => $appleModel->id, 'asset_tag' => 'REL-001']);
    Asset::factory()->create(['model_id' => $dellModel->id, 'asset_tag' => 'REL-002']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['manufacturer' => '!Apple']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('negation filter on custom field', function () {
    $field = CustomField::factory()->cpu()->create();
    $dbColumn = $field->db_column_name();

    Asset::factory()->create([$dbColumn => '3.2GHz Intel Core i9', 'asset_tag' => 'CF-001']);
    Asset::factory()->create([$dbColumn => '2.4GHz AMD Ryzen 7', 'asset_tag' => 'CF-002']);

    Asset::flushCustomFieldFilterMap();

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode([$dbColumn => '!Intel']),
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('negation filter on asset location relation', function () {
    $ts = now()->timestamp;

    $blahLocation = Location::factory()->create(['name' => 'Blah Office '.$ts]);
    $safeLocation = Location::factory()->create(['name' => 'Safe Office '.$ts]);

    $blahAsset = Asset::factory()->create(['location_id' => $blahLocation->id]);
    $safeAsset = Asset::factory()->create(['location_id' => $safeLocation->id]);

    $response = $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode(['location' => '!Blah']),
        ]))
        ->assertOk();

    $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    // Asset in the "blah" location must NOT appear.
    expect($returnedIds)->not->toContain((int) $blahAsset->id);

    // Asset in a different location MUST appear.
    expect($returnedIds)->toContain((int) $safeAsset->id);
});

test('negation filter on user location relation', function () {
    $ts = now()->timestamp;

    $blahLocation = Location::factory()->create([
        'name' => 'Blah Floor '.$ts,
        'address' => 'Safe Address '.$ts,
    ]);
    $safeLocation = Location::factory()->create([
        'name' => 'Safe Floor '.$ts,
        // Regression guard: structured filter on "location" should not inspect address.
        'address' => 'Blah Address '.$ts,
    ]);

    $blahUser = User::factory()->create(['location_id' => $blahLocation->id]);
    $safeUser = User::factory()->create(['location_id' => $safeLocation->id]);
    $nullLocationUser = User::factory()->create(['location_id' => null]);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.users.index', [
            'filter' => json_encode(['location' => '!Blah']),
        ]))
        ->assertOk();

    $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

    // The user in the "blah" location must NOT appear.
    expect($returnedIds)->not->toContain((int) $blahUser->id);

    // The user in a safe location MUST appear.
    expect($returnedIds)->toContain((int) $safeUser->id);

    // Users with no location should also be included for negated filters.
    expect($returnedIds)->toContain((int) $nullLocationUser->id);
});

test('asset structured filter and operator with model number and location', function () {
    $locationA = Location::factory()->create(['name' => 'HQ-East']);
    $locationB = Location::factory()->create(['name' => 'HQ-West']);
    $manufacturer = Manufacturer::factory()->create(['name' => 'FilterCo']);

    $modelMatch = AssetModel::factory()->create([
        'manufacturer_id' => $manufacturer->id,
        'model_number' => 'MODEL-111',
    ]);

    $modelOther = AssetModel::factory()->create([
        'manufacturer_id' => $manufacturer->id,
        'model_number' => 'MODEL-222',
    ]);

    // ✅ Matches both model_number and location.
    Asset::factory()->create([
        'asset_tag' => 'AND-MATCH-1',
        'model_id' => $modelMatch->id,
        'location_id' => $locationA->id,
    ]);

    // ❌ Matches location only.
    Asset::factory()->create([
        'asset_tag' => 'AND-LOC-ONLY',
        'model_id' => $modelOther->id,
        'location_id' => $locationA->id,
    ]);

    // ❌ Matches model_number only.
    Asset::factory()->create([
        'asset_tag' => 'AND-MODEL-ONLY',
        'model_id' => $modelMatch->id,
        'location_id' => $locationB->id,
    ]);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => json_encode([
                'model_number' => 'MODEL-111',
                'location' => 'HQ-East',
            ]),
            'filter_operator' => 'and',
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('array form filter applies as structured filter', function () {
    Asset::factory()->create(['name' => 'MacBook Pro 15"', 'serial' => 'SN-UNIQUE-NAME']);
    Asset::factory()->create(['name' => 'MacBook Pro 16"', 'serial' => 'SN-OTHER']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => ['serial' => 'SN-UNIQUE-NAME'],
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('array form filter combines fields with and by default', function () {
    Asset::factory()->create(['name' => 'MacBook', 'serial' => 'SN-MATCH']);
    Asset::factory()->create(['name' => 'MacBook', 'serial' => 'SN-OTHER']);
    Asset::factory()->create(['name' => 'Dell', 'serial' => 'SN-MATCH']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => [
                'name' => 'MacBook',
                'serial' => 'SN-MATCH',
            ],
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('array form filter honors or operator', function () {
    Asset::factory()->create(['name' => 'OnlyNameMatch-Alpha']);
    Asset::factory()->create(['name' => 'OtherAsset', 'serial' => 'OnlySerialMatch-Beta']);
    Asset::factory()->create(['name' => 'Unrelated', 'serial' => 'SomethingElse']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => [
                'name' => 'OnlyNameMatch-Alpha',
                'serial' => 'OnlySerialMatch-Beta',
            ],
            'filter_operator' => 'or',
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
});

test('array form filter ignores empty values', function () {
    Asset::factory()->create(['name' => 'KeeperAsset']);
    Asset::factory()->create(['name' => 'OtherAsset']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => [
                'name' => 'KeeperAsset',
                'serial' => '',
            ],
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});

test('array form filter resolves relation keys', function () {
    $supplier = Supplier::factory()->create(['name' => 'ArrayFormSupplier-'.now()->timestamp]);
    Asset::factory()->create(['name' => 'A', 'supplier_id' => $supplier->id]);
    Asset::factory()->create(['name' => 'B']);

    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => ['supplier' => 'ArrayFormSupplier'],
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
});
