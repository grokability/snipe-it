<?php

namespace Tests\Feature\Search;

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
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Test the Searchable trait across multiple search modes:
 * - Free-text search (search=term)
 * - Structured filter search (filter={"field":"value"})
 *
 * Tests verify that:
 * 1. Attributes are searchable via both modes
 * 2. Relations are searchable via both modes
 * 3. Relation aliases (e.g., status_label → status) work correctly
 * 4. Multi-word searches work as expected
 */
class SearchableTraitTest extends TestCase
{
    /**
     * Test Asset free-text search on attributes
     */
    public function test_asset_free_text_search_on_attributes()
    {
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
    }

    /**
     * Test Asset free-text search on relations
     */
    public function test_asset_free_text_search_on_relations()
    {
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
    }

    /**
     * Test Asset structured filter search on attributes
     */
    public function test_asset_structured_filter_on_attributes()
    {
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
    }

    /**
     * Test Asset structured filter search on relations
     */
    public function test_asset_structured_filter_on_relations()
    {
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
    }

    /**
     * Test Asset structured filter using relation alias (status_label → status)
     */
    public function test_asset_structured_filter_using_relation_alias()
    {
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
    }

    /**
     * Test License free-text search on attributes
     */
    public function test_license_free_text_search_on_attributes()
    {
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
    }

    /**
     * Test License free-text search on relations
     */
    public function test_license_free_text_search_on_relations()
    {
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
    }

    /**
     * Test License structured filter search on attributes
     */
    public function test_license_structured_filter_on_attributes()
    {
        License::factory()->create(['name' => 'Microsoft Office', 'serial' => 'SN-OFFICE-001']);
        License::factory()->create(['name' => 'Adobe Suite', 'serial' => 'SN-ADOBE-002']);

        $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.licenses.index', [
                'filter' => json_encode(['name' => 'Office']),
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());

        $this->actingAsForApi(User::factory()->viewLicenses()->create())
            ->getJson(route('api.licenses.index', [
                'filter' => json_encode(['serial' => 'ADOBE']),
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
    }

    /**
     * Test License structured filter search on relations
     */
    public function test_license_structured_filter_on_relations()
    {
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
    }

    /**
     * Test User free-text search on attributes
     */
    #[Group('skip-flaky')]
    public function test_user_free_text_search_on_attributes()
    {
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
    }

    /**
     * Test User multi-word search (first_name + last_name concat)
     */
    public function test_user_multi_word_free_text_search()
    {
        $timestamp = now()->timestamp;
        $uniqueName = 'ABC'.$timestamp;
        User::factory()->create(['first_name' => 'TestJohn'.$uniqueName, 'last_name' => 'Smith'.$uniqueName, 'username' => 'jsmith'.$uniqueName]);
        User::factory()->create(['first_name' => 'TestJane'.$uniqueName, 'last_name' => 'Doe'.$uniqueName, 'username' => 'jdoe'.$uniqueName]);

        // Search for full name should match when both first and last are concatenated
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.users.index', ['search' => 'TestJohn'.$uniqueName.' Smith']))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
    }

    /**
     * Test User structured filter on attributes
     */
    public function test_user_structured_filter_on_attributes()
    {
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
    }

    /**
     * "name" is a virtual column on User (CONCAT of first_name + last_name).
     * A positive filter should match on concatenated full name.
     */
    public function test_user_name_virtual_column_filter_positive()
    {
        $ts = now()->timestamp;
        User::factory()->create(['first_name' => 'VirtFirst'.$ts, 'last_name' => 'VirtLast'.$ts]);
        User::factory()->create(['first_name' => 'Other'.$ts, 'last_name' => 'Person'.$ts]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.users.index', [
                'filter' => json_encode(['name' => 'VirtFirst'.$ts]),
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
    }

    /**
     * A negated "name" filter using the "!" prefix should exclude matching users,
     * returning only those whose full name does NOT contain the term.
     */
    public function test_user_name_virtual_column_filter_negation_bang_prefix()
    {
        $ts = now()->timestamp;
        $negUser = User::factory()->create(['first_name' => 'NegFirst'.$ts,  'last_name' => 'NegLast'.$ts]);
        $safeUser = User::factory()->create(['first_name' => 'SafeFirst'.$ts, 'last_name' => 'SafeLast'.$ts]);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.users.index', [
                'filter' => json_encode(['name' => '!NegFirst'.$ts]),
            ]))
            ->assertOk();

        $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        // The matched (negated) user must not appear in results.
        $this->assertNotContains((int) $negUser->id, $returnedIds);

        // The safe user should appear in results.
        $this->assertContains((int) $safeUser->id, $returnedIds);
    }

    /**
     * A negated "name" filter using the "not:" prefix should behave identically to "!".
     */
    public function test_user_name_virtual_column_filter_negation_not_colon_prefix()
    {
        $ts = now()->timestamp;
        User::factory()->create(['first_name' => 'NotFirst'.$ts, 'last_name' => 'NotLast'.$ts]);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.users.index', [
                'filter' => json_encode(['name' => 'not:NotFirst'.$ts]),
            ]))
            ->assertOk();

        $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertNotContains(
            (int) User::where('first_name', 'NotFirst'.$ts)->value('id'),
            $returnedIds
        );
    }

    /**
     * Test Category free-text search on attributes
     */
    public function test_category_free_text_search_on_attributes()
    {
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
    }

    /**
     * Test Category structured filter on attributes
     */
    public function test_category_structured_filter_on_attributes()
    {
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
    }

    /**
     * Test Manufacturer free-text search on attributes
     */
    public function test_manufacturer_free_text_search_on_attributes()
    {
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
    }

    /**
     * Test Manufacturer structured filter on attributes
     */
    public function test_manufacturer_structured_filter_on_attributes()
    {
        Manufacturer::factory()->apple()->create();
        Manufacturer::factory()->microsoft()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.manufacturers.index', [
                'filter' => json_encode(['name' => 'Apple']),
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
    }

    /**
     * Test Location free-text search on attributes
     */
    public function test_location_free_text_search_on_attributes()
    {
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
    }

    /**
     * Test Location structured filter on attributes
     */
    public function test_location_structured_filter_on_attributes()
    {
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
    }

    /**
     * Test partial word matching works in both search modes
     */
    public function test_partial_word_matching()
    {
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
    }

    /**
     * Test search is case-insensitive
     */
    public function test_search_is_case_insensitive()
    {
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
    }

    /**
     * Test empty search/filter returns no special errors
     */
    public function test_empty_search_returns_all_results()
    {
        Asset::factory()->count(3)->create();

        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.index', ['search' => '']))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    /**
     * Test no results when search matches nothing
     */
    public function test_search_no_results()
    {
        Asset::factory()->create(['name' => 'Asset 1']);

        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.index', ['search' => 'NonExistentTerm']))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 0)->etc());
    }

    /**
     * Test asset free-text search across multiple custom fields.
     */
    public function test_asset_free_text_search_matches_across_multiple_custom_fields()
    {
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

        $this->assertSame($expectedIds, $returnedIds);
    }

    /**
     * Test filtering on a custom field using the raw db_column slug.
     */
    public function test_custom_field_filter_by_db_column_slug()
    {
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
    }

    /**
     * Test filtering by a human-readable custom field name is ignored.
     */
    public function test_custom_field_filter_by_human_readable_name_is_ignored()
    {
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
    }

    /**
     * Test custom field name collisions do not override relation filters.
     */
    public function test_custom_field_name_collision_does_not_override_relation_filter()
    {
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
    }

    /**
     * Test filtering on a custom field using the raw db_column slug.
     */
    public function test_custom_field_gets_skipped_if_encrypted()
    {
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
    }

    /**
     * Test that custom field filter returns no results when value doesn't match.
     */
    public function test_custom_field_filter_returns_empty_when_no_match()
    {
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
    }

    /**
     * "is:null" on a direct nullable attribute should match rows where that column is NULL.
     * "is:not_null" should match rows where it is not NULL.
     */
    public function test_is_null_filter_on_nullable_attribute()
    {
        $ts = now()->timestamp;

        $withNotes = Asset::factory()->create(['notes' => 'Some notes '.$ts]);
        $withoutNotes = Asset::factory()->create(['notes' => null]);

        $superuser = User::factory()->viewAssets()->create();

        // is:null → only the asset with no notes
        $response = $this->actingAsForApi($superuser)
            ->getJson(route('api.assets.index', ['filter' => json_encode(['notes' => 'is:null'])]))
            ->assertOk();

        $returnedIds = collect($response->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertContains((int) $withoutNotes->id, $returnedIds);
        $this->assertNotContains((int) $withNotes->id, $returnedIds);

        // is:not_null → only the asset with notes
        $response2 = $this->actingAsForApi($superuser)
            ->getJson(route('api.assets.index', ['filter' => json_encode(['notes' => 'is:not_null'])]))
            ->assertOk();

        $returnedIds2 = collect($response2->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertContains((int) $withNotes->id, $returnedIds2);
        $this->assertNotContains((int) $withoutNotes->id, $returnedIds2);
    }

    /**
     * "is:not_null" on the User virtual "name" column should match users where
     * at least one constituent column (first_name, last_name) is not null.
     * All factory-created users have a first_name, so they should all appear.
     */
    public function test_is_null_filter_on_virtual_name_column()
    {
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
        $this->assertContains((int) $userWithName->id, $returnedIds);

        // The acting superuser itself also has a name, so it should appear too.
        $this->assertContains((int) $superuser->id, $returnedIds);
    }

    /**
     * "is:null" on a searchable relation key should return records that have no
     * related record (equivalent to doesntHave).
     * "is:not_null" should return only records that have a related record.
     */
    public function test_is_null_filter_on_relation_key()
    {
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

        $this->assertContains((int) $withoutSupplier->id, $returnedIds);
        $this->assertNotContains((int) $withSupplier->id, $returnedIds);

        // is:not_null on supplier → assets with a supplier
        $response2 = $this->actingAsForApi($superuser)
            ->getJson(route('api.assets.index', ['filter' => json_encode(['supplier' => 'is:not_null'])]))
            ->assertOk();

        $returnedIds2 = collect($response2->json('rows'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertContains((int) $withSupplier->id, $returnedIds2);
        $this->assertNotContains((int) $withoutSupplier->id, $returnedIds2);
    }

    /**
     * Test custom field partial match via filter.
     */
    public function test_custom_field_filter_partial_match()
    {
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
    }

    /**
     * Regression: custom field filters should support exact-match via "is:".
     */
    public function test_custom_field_filter_exact_match_with_is_modifier()
    {
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
    }

    /**
     * Test negation filter using "!" prefix on a direct attribute.
     * filter={"name":"!Dell"} should return all assets whose name does NOT contain "Dell".
     */
    public function test_negation_filter_with_bang_prefix_on_attribute()
    {
        Asset::factory()->create(['name' => 'MacBook Pro', 'asset_tag' => 'NEG-001']);
        Asset::factory()->create(['name' => 'Dell XPS 13', 'asset_tag' => 'NEG-002']);
        Asset::factory()->create(['name' => 'HP Pavilion', 'asset_tag' => 'NEG-003']);

        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.index', [
                'filter' => json_encode(['name' => '!Dell']),
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
    }

    /**
     * Test negation filter using "not:" prefix on a direct attribute.
     * filter={"name":"not:Dell"} should behave identically to "!Dell".
     */
    public function test_negation_filter_with_not_prefix_on_attribute()
    {
        Asset::factory()->create(['name' => 'MacBook Pro', 'asset_tag' => 'NOTP-001']);
        Asset::factory()->create(['name' => 'Dell XPS 13', 'asset_tag' => 'NOTP-002']);
        Asset::factory()->create(['name' => 'HP Pavilion', 'asset_tag' => 'NOTP-003']);

        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.index', [
                'filter' => json_encode(['name' => 'not:Dell']),
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 2)->etc());
    }

    /**
     * Test that combining a positive filter and a negation filter works correctly.
     * filter={"asset_tag":"COMBO","name":"!Dell"} should return assets tagged COMBO that
     * are NOT named Dell.
     */
    public function test_combined_positive_and_negation_filters()
    {
        Asset::factory()->create(['name' => 'MacBook Pro', 'asset_tag' => 'COMBO-001']);
        Asset::factory()->create(['name' => 'Dell XPS 13', 'asset_tag' => 'COMBO-002']);
        Asset::factory()->create(['name' => 'HP Pavilion',  'asset_tag' => 'OTHER-001']);

        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.index', [
                'filter' => json_encode(['asset_tag' => 'COMBO', 'name' => '!Dell']),
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
    }

    /**
     * Test negation filter on a relation attribute.
     * filter={"manufacturer":"!Apple"} should return assets whose manufacturer does NOT
     * contain "Apple".
     */
    public function test_negation_filter_on_relation()
    {
        $apple = Manufacturer::factory()->create(['name' => 'Apple']);
        $dell = Manufacturer::factory()->create(['name' => 'Dell']);

        $appleModel = AssetModel::factory()->create(['manufacturer_id' => $apple->id]);
        $dellModel = AssetModel::factory()->create(['manufacturer_id' => $dell->id]);

        Asset::factory()->create(['model_id' => $appleModel->id, 'asset_tag' => 'REL-001']);
        Asset::factory()->create(['model_id' => $dellModel->id,  'asset_tag' => 'REL-002']);

        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.index', [
                'filter' => json_encode(['manufacturer' => '!Apple']),
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
    }

    /**
     * Test negation filter on a custom field.
     * filter={"_snipeit_cpu_X":"!Intel"} should return assets where the CPU field
     * does NOT contain "Intel".
     */
    public function test_negation_filter_on_custom_field()
    {
        $field = CustomField::factory()->cpu()->create();
        $dbColumn = $field->db_column_name();

        Asset::factory()->create([$dbColumn => '3.2GHz Intel Core i9', 'asset_tag' => 'CF-001']);
        Asset::factory()->create([$dbColumn => '2.4GHz AMD Ryzen 7',   'asset_tag' => 'CF-002']);

        Asset::flushCustomFieldFilterMap();

        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.index', [
                'filter' => json_encode([$dbColumn => '!Intel']),
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 1)->etc());
    }

    /**
     * Negation filter "!blah" on the "location" relation key for Assets
     * should exclude assets with a location name containing "blah".
     */
    public function test_negation_filter_on_asset_location_relation()
    {
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
        $this->assertNotContains((int) $blahAsset->id, $returnedIds);
        // Asset in a different location MUST appear.
        $this->assertContains((int) $safeAsset->id, $returnedIds);
    }

    /**
     * Negation filter "!blah" on the "location" relation key for Users
     * should exclude users whose location name contains "blah".
     *
     * The User model stores location via the "userloc" Eloquent relation
     * (not "location"), so a "location" → "userloc" alias must be registered.
     */
    public function test_negation_filter_on_user_location_relation()
    {
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
        $this->assertNotContains((int) $blahUser->id, $returnedIds);
        // The user in a safe location MUST appear.
        $this->assertContains((int) $safeUser->id, $returnedIds);
        // Users with no location should also be included for negated filters.
        $this->assertContains((int) $nullLocationUser->id, $returnedIds);
    }

    /**
     * Regression: structured AND filter should honor model_number and location together.
     */
    public function test_asset_structured_filter_and_operator_with_model_number_and_location()
    {
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
    }
}
