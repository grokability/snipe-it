<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;

test('requires permission to create department', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.departments.store'))
        ->assertForbidden();
});

test('can create department with all fields', function () {
    $company = Company::factory()->create();
    $location = Location::factory()->create();
    $manager = User::factory()->create();
    $user = User::factory()->superuser()->create();
    $response = $this->actingAsForApi($user)
        ->postJson(route('api.departments.store'), [
            'name' => 'Test Department',
            'company_id' => $company->id,
            'location_id' => $location->id,
            'manager_id' => $manager->id,
            'notes' => 'Test Note',
            'phone' => '1234567890',
            'fax' => '1234567890',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success')
        ->json();

    expect(Department::where('name', 'Test Department')->exists())->toBeTrue();

    $department = Department::find($response['payload']['id']);
    expect($department->name)->toEqual('Test Department');
    expect($department->notes)->toEqual('Test Note');

    $this->assertDatabaseHas('departments', [
        'name' => 'Test Department',
        'company_id' => $company->id,
        'location_id' => $location->id,
        'manager_id' => $manager->id,
        'notes' => 'Test Note',
        'phone' => '1234567890',
        'fax' => '1234567890',
        'created_by' => $user->id,
    ]);
});

test('name required for department', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.departments.store'), [
            'company_id' => Company::factory()->create()->id,
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('only name required for department', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.departments.store'), [
            'name' => 'Test Department',
        ])
        ->assertOk()
        ->assertStatusMessageIs('success');
});

test('arrays not allowed for numeric fields', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.departments.store'), [
            'name' => 'Test Department',
            'company_id' => [1, 2],
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});

test('arrays of strings are not allowed for numeric fields', function () {
    $response = $this->actingAsForApi(User::factory()->superuser()->create())
        ->postJson(route('api.departments.store'), [
            'name' => 'Test Department',
            'company_id' => ['1', '2'],
        ])
        ->assertOk()
        ->assertStatusMessageIs('error');
});
