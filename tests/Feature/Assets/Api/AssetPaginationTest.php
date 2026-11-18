<?php

use App\Models\Asset;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->superuser()->create();
});

test('response includes pagination fields', function () {
    Asset::factory()->count(3)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index'))
        ->assertOk()
        ->assertJsonStructure(['total', 'rows', 'current_page', 'per_page', 'total_pages']);
});

test('default request returns page one', function () {
    Asset::factory()->count(3)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index'))
        ->assertOk()
        ->assertJsonPath('current_page', 1);
});

test('offset zero returns page one', function () {
    Asset::factory()->count(10)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['offset' => 0, 'limit' => 5]))
        ->assertOk()
        ->assertJsonPath('current_page', 1);
});

test('offset derives correct page number', function () {
    Asset::factory()->count(10)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['offset' => 5, 'limit' => 5]))
        ->assertOk()
        ->assertJsonPath('current_page', 2);
});

test('page one returns current page one', function () {
    Asset::factory()->count(10)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 1, 'limit' => 5]))
        ->assertOk()
        ->assertJsonPath('current_page', 1);
});

test('page two returns current page two', function () {
    Asset::factory()->count(10)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 2, 'limit' => 5]))
        ->assertOk()
        ->assertJsonPath('current_page', 2);
});

test('page one returns first items', function () {
    foreach (range(1, 10) as $i) {
        Asset::factory()->create(['asset_tag' => sprintf('PAG-TEST-%03d', $i)]);
    }

    $tags = $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 1, 'limit' => 5, 'sort' => 'asset_tag', 'order' => 'asc']))
        ->assertOk()
        ->json('rows.*.asset_tag');

    expect($tags)->toEqual(['PAG-TEST-001', 'PAG-TEST-002', 'PAG-TEST-003', 'PAG-TEST-004', 'PAG-TEST-005']);
});

test('page two returns second set of items', function () {
    foreach (range(1, 10) as $i) {
        Asset::factory()->create(['asset_tag' => sprintf('PAG-TEST-%03d', $i)]);
    }

    $tags = $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 2, 'limit' => 5, 'sort' => 'asset_tag', 'order' => 'asc']))
        ->assertOk()
        ->json('rows.*.asset_tag');

    expect($tags)->toEqual(['PAG-TEST-006', 'PAG-TEST-007', 'PAG-TEST-008', 'PAG-TEST-009', 'PAG-TEST-010']);
});

test('offset returns correct items', function () {
    foreach (range(1, 10) as $i) {
        Asset::factory()->create(['asset_tag' => sprintf('PAG-TEST-%03d', $i)]);
    }

    $tags = $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['offset' => 5, 'limit' => 5, 'sort' => 'asset_tag', 'order' => 'asc']))
        ->assertOk()
        ->json('rows.*.asset_tag');

    expect($tags)->toEqual(['PAG-TEST-006', 'PAG-TEST-007', 'PAG-TEST-008', 'PAG-TEST-009', 'PAG-TEST-010']);
});

test('page param respects limit', function () {
    Asset::factory()->count(10)->create();

    $response = $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 1, 'limit' => 4]))
        ->assertOk();

    expect($response->json('rows'))->toHaveCount(4);
});

test('page beyond results returns empty rows', function () {
    Asset::factory()->count(5)->create();

    $response = $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 99, 'limit' => 5]))
        ->assertOk();

    expect($response->json('rows'))->toHaveCount(0);
    expect($response->json('total'))->toEqual(5);
});

test('offset takes precedence over page when both provided', function () {
    Asset::factory()->count(10)->create();

    // offset=0 should win over page=3, giving current_page=1
    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['offset' => 0, 'page' => 3, 'limit' => 5]))
        ->assertOk()
        ->assertJsonPath('current_page', 1);
});

test('per page reflects the limit parameter as an integer', function () {
    Asset::factory()->count(3)->create();

    $response = $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['limit' => 25]))
        ->assertOk()
        ->assertJsonPath('per_page', 25);

    expect($response->json('per_page'))->toBeInt();
});

test('prev page url is null on first page', function () {
    Asset::factory()->count(10)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 1, 'limit' => 5]))
        ->assertOk()
        ->assertJsonPath('prev_page_url', null);
});

test('next page url is null on last page', function () {
    Asset::factory()->count(10)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 2, 'limit' => 5]))
        ->assertOk()
        ->assertJsonPath('next_page_url', null);
});

test('next page url contains correct page number', function () {
    Asset::factory()->count(10)->create();

    $url = $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 1, 'limit' => 5]))
        ->assertOk()
        ->json('next_page_url');

    $this->assertStringContainsString('page=2', $url);
    $this->assertStringContainsString('limit=5', $url);
});

test('prev page url contains correct page number', function () {
    Asset::factory()->count(10)->create();

    $url = $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 2, 'limit' => 5]))
        ->assertOk()
        ->json('prev_page_url');

    $this->assertStringContainsString('page=1', $url);
    $this->assertStringContainsString('limit=5', $url);
});

test('page urls do not include limit when not in original request', function () {
    // Since this test relies on app.max_results,
    // let's lower it temporarily to avoid having
    // to create a bunch of models.
    $newMaxResults = 1;

    config(['app.max_results' => $newMaxResults]);

    Asset::factory()->count($newMaxResults + 1)->create();

    $response = $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 1]))
        ->assertOk();

    // Guards the thin margin above: without a second page there is no URL to
    // inspect and the assertion below would pass vacuously.
    expect($response->json('next_page_url'))->not->toBeNull();
    $this->assertStringNotContainsString('limit=', $response->json('next_page_url'));
});

test('both page urls null when single page', function () {
    Asset::factory()->count(3)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['page' => 1, 'limit' => 50]))
        ->assertOk()
        ->assertJsonPath('prev_page_url', null)
        ->assertJsonPath('next_page_url', null);
});

test('total pages is correct for even division', function () {
    Asset::factory()->count(10)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['limit' => 5]))
        ->assertOk()
        ->assertJsonPath('total_pages', 2);
});

test('total pages rounds up for uneven division', function () {
    Asset::factory()->count(11)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['limit' => 5]))
        ->assertOk()
        ->assertJsonPath('total_pages', 3);
});

test('total pages is one when results fit in single page', function () {
    Asset::factory()->count(3)->create();

    $this->actingAsForApi($this->user)
        ->getJson(route('api.assets.index', ['limit' => 5]))
        ->assertOk()
        ->assertJsonPath('total_pages', 1);
});
