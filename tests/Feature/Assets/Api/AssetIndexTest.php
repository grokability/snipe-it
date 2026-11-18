<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;

test('asset api index returns expected assets', function () {
    Asset::factory()->count(3)->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.assets.index', [
                'sort' => 'name',
                'order' => 'asc',
                'offset' => '0',
                'limit' => '20',
            ]))
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
});

test('asset api index returns display upcoming audits due', function () {
    Asset::factory()->count(3)->create(['next_audit_date' => Carbon::now()->format('Y-m-d')]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.assets.list-upcoming', ['action' => 'audits', 'upcoming_status' => 'due']))
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
});

test('asset api index returns overdue for audit', function () {
    Asset::factory()->count(3)->create(['next_audit_date' => Carbon::now()->subDays(1)->format('Y-m-d')]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.assets.list-upcoming', ['action' => 'audits', 'upcoming_status' => 'overdue']))
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
});

test('asset api index returns due or overdue for audit', function () {
    Asset::factory()->count(3)->create(['next_audit_date' => Carbon::now()->format('Y-m-d')]);
    Asset::factory()->count(2)->create(['next_audit_date' => Carbon::now()->subDays(1)->format('Y-m-d')]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.assets.list-upcoming', ['action' => 'audits', 'upcoming_status' => 'due-or-overdue']))
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 5)->etc());
});

test('asset api index returns due for expected checkin', function () {
    Asset::factory()->count(3)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->format('Y-m-d')]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.assets.list-upcoming', ['action' => 'checkins', 'upcoming_status' => 'due'])
        )
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
});

test('asset api index returns overdue for expected checkin', function () {
    Asset::factory()->count(3)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->subDays(1)->format('Y-m-d')]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.list-upcoming', ['action' => 'checkins', 'upcoming_status' => 'overdue']))
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
});

test('asset api index returns due or overdue for expected checkin', function () {
    Asset::factory()->count(3)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->subDays(1)->format('Y-m-d')]);
    Asset::factory()->count(2)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->format('Y-m-d')]);

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.list-upcoming', ['action' => 'checkins', 'upcoming_status' => 'due-or-overdue']))
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 5)->etc());
});

test('asset api index adheres to company scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->for($companyA)->create();
    $assetB = Asset::factory()->for($companyB)->create();

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = $companyA->users()->save(User::factory()->viewAssets()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->viewAssets()->make());

    $this->settings->disableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('api.assets.index'))
        ->assertResponseContainsInRows($assetA, 'asset_tag')
        ->assertResponseContainsInRows($assetB, 'asset_tag');

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.assets.index'))
        ->assertResponseContainsInRows($assetA, 'asset_tag')
        ->assertResponseContainsInRows($assetB, 'asset_tag');

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.assets.index'))
        ->assertResponseContainsInRows($assetA, 'asset_tag')
        ->assertResponseContainsInRows($assetB, 'asset_tag');

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('api.assets.index'))
        ->assertResponseContainsInRows($assetA, 'asset_tag')
        ->assertResponseContainsInRows($assetB, 'asset_tag');

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.assets.index'))
        ->assertResponseContainsInRows($assetA, 'asset_tag')
        ->assertResponseDoesNotContainInRows($assetB, 'asset_tag');

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.assets.index'))
        ->assertResponseDoesNotContainInRows($assetA, 'asset_tag')
        ->assertResponseContainsInRows($assetB, 'asset_tag');
});

test('assets can be filtered by custom field', function () {
    $field = CustomField::factory()->create();

    $matchingAssets = Asset::factory()->count(3)->hasMultipleCustomFields([$field])->create();
    foreach ($matchingAssets as $asset) {
        $asset->{$field->db_column_name()} = 'target-value';
        $asset->save();
    }

    // These assets have a null value for the custom field column and should not be returned
    Asset::factory()->count(2)->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.index', [
            $field->db_column_name() => 'target-value',
        ]))
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
});

test('gracefully handles malformed filter', function () {
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            // filter should be a json encoded array and not a string
            'filter' => 'asset_tag:12345',
        ]))
        ->assertStatusMessageIs('error')
        ->assertJson(function (AssertableJson $json) {
            $json->has('messages.filter')->etc();
        });
});

test('returns result via filter', function () {
    Asset::factory()->count(3)->create(['name' => 'MY AWESOME ASSET NAME']);
    $this->actingAsForApi(User::factory()->viewAssets()->create())
        ->getJson(route('api.assets.index', [
            'filter' => '{"name":"MY AWESOME ASSET NAME"}',
        ]))
        ->assertOk()
        ->assertJsonStructure([
            'total',
            'rows',
        ])
        ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
});

test('sort by location does not fall through to rtd location', function () {
    $zebraLocation = Location::factory()->create(['name' => 'Zebra Warehouse']);
    $aardvarkLocation = Location::factory()->create(['name' => 'Aardvark Warehouse']);

    // Cross the pairing between location and rtd_location so a fallthrough
    // that applied OrderRtdLocation would put the assets in a different
    // order than a clean OrderLocation.
    $assetLocatedAtAardvark = Asset::factory()->create([
        'location_id' => $aardvarkLocation->id,
        'rtd_location_id' => $zebraLocation->id,
    ]);
    $assetLocatedAtZebra = Asset::factory()->create([
        'location_id' => $zebraLocation->id,
        'rtd_location_id' => $aardvarkLocation->id,
    ]);

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.index', [
            'sort' => 'location',
            'order' => 'asc',
            'limit' => 20,
        ]))
        ->assertOk()
        ->json('rows');

    expect([$response[0]['id'], $response[1]['id']])->toBe([$assetLocatedAtAardvark->id, $assetLocatedAtZebra->id]);
});

test('deleted status type returns soft deleted assets', function () {
    $deleted = Asset::factory()->count(3)->create();
    foreach ($deleted as $asset) {
        $asset->delete();
    }
    Asset::factory()->count(2)->create();

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.index', ['status_type' => 'Deleted', 'limit' => 10]))
        ->assertOk();

    $response->assertJsonPath('total', 3);
    $rows = $response->json('rows');
    expect($rows)->toHaveCount(3, 'Deleted view must return the soft-deleted assets, not an empty rows array.');

    $returnedIds = collect($rows)->pluck('id')->sort()->values()->all();
    $expectedIds = $deleted->pluck('id')->sort()->values()->all();
    expect($returnedIds)->toBe($expectedIds, 'Returned rows must match the soft-deleted asset ids, not any live ones.');
});

test('default index still excludes soft deleted assets', function () {
    // Companion to the Deleted-view regression. The withTrashed() on
    // re-hydration is scoped to that one query, so the id pluck
    // (which has no explicit onlyTrashed() for a default request)
    // keeps excluding soft-deleted rows and the rows array matches.
    $live = Asset::factory()->count(2)->create();
    $deleted = Asset::factory()->count(3)->create();
    foreach ($deleted as $asset) {
        $asset->delete();
    }

    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(route('api.assets.index', ['limit' => 10]))
        ->assertOk();

    $response->assertJsonPath('total', 2);
    $returnedIds = collect($response->json('rows'))->pluck('id')->sort()->values()->all();
    expect($returnedIds)->toBe($live->pluck('id')->sort()->values()->all());
});
