<?php

use App\Models\Maintenance;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->superuser()->create();
});

test('page one returns first items', function () {
    foreach (range(1, 10) as $i) {
        Maintenance::factory()->create(['name' => sprintf('PAG-TEST-%03d', $i)]);
    }

    $names = $this->actingAsForApi($this->user)
        ->getJson(route('api.maintenances.index', ['page' => 1, 'limit' => 5, 'sort' => 'name', 'order' => 'asc']))
        ->assertOk()
        ->json('rows.*.name');

    expect($names)->toEqual(['PAG-TEST-001', 'PAG-TEST-002', 'PAG-TEST-003', 'PAG-TEST-004', 'PAG-TEST-005']);
});

test('page two returns second set of items', function () {
    foreach (range(1, 10) as $i) {
        Maintenance::factory()->create(['name' => sprintf('PAG-TEST-%03d', $i)]);
    }

    $names = $this->actingAsForApi($this->user)
        ->getJson(route('api.maintenances.index', ['page' => 2, 'limit' => 5, 'sort' => 'name', 'order' => 'asc']))
        ->assertOk()
        ->json('rows.*.name');

    expect($names)->toEqual(['PAG-TEST-006', 'PAG-TEST-007', 'PAG-TEST-008', 'PAG-TEST-009', 'PAG-TEST-010']);
});

test('offset returns correct items', function () {
    foreach (range(1, 10) as $i) {
        Maintenance::factory()->create(['name' => sprintf('PAG-TEST-%03d', $i)]);
    }

    $names = $this->actingAsForApi($this->user)
        ->getJson(route('api.maintenances.index', ['offset' => 5, 'limit' => 5, 'sort' => 'name', 'order' => 'asc']))
        ->assertOk()
        ->json('rows.*.name');

    expect($names)->toEqual(['PAG-TEST-006', 'PAG-TEST-007', 'PAG-TEST-008', 'PAG-TEST-009', 'PAG-TEST-010']);
});

test('page param respects limit', function () {
    Maintenance::factory()->count(10)->create();

    $response = $this->actingAsForApi($this->user)
        ->getJson(route('api.maintenances.index', ['page' => 1, 'limit' => 4]))
        ->assertOk();

    expect($response->json('rows'))->toHaveCount(4);
});

test('page beyond results returns empty rows', function () {
    Maintenance::factory()->count(5)->create();

    $response = $this->actingAsForApi($this->user)
        ->getJson(route('api.maintenances.index', ['page' => 99, 'limit' => 5]))
        ->assertOk();

    expect($response->json('rows'))->toHaveCount(0);
    expect($response->json('total'))->toEqual(5);
});
