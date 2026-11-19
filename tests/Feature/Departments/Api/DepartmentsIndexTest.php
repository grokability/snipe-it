<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('viewing department index requires authentication', function () {
    $this->getJson(route('api.departments.index'))->assertRedirect();
});

test('viewing department index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.departments.index'))
        ->assertForbidden();
});

test('department index returns expected departments', function () {
    Department::factory()->count(3)->create();

    $this->actingAsForApi(User::factory()->superuser()->create())
        ->getJson(
            route('api.departments.index', [
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

test('department index adheres to company scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $departmentA = Department::factory()->for($companyA)->create();
    $departmentB = Department::factory()->for($companyB)->create();

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = $companyA->users()->save(User::factory()->viewDepartments()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->viewDepartments()->make());

    $this->settings->disableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('api.departments.index'))
        ->assertResponseContainsInRows($departmentA)
        ->assertResponseContainsInRows($departmentB);

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.departments.index'))
        ->assertResponseContainsInRows($departmentA)
        ->assertResponseContainsInRows($departmentB);

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.departments.index'))
        ->assertResponseContainsInRows($departmentA)
        ->assertResponseContainsInRows($departmentB);

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($superUser)
        ->getJson(route('api.departments.index'))
        ->assertResponseContainsInRows($departmentA)
        ->assertResponseContainsInRows($departmentB);

    $this->actingAsForApi($userInCompanyA)
        ->getJson(route('api.departments.index'))
        ->assertResponseContainsInRows($departmentA)
        ->assertResponseDoesNotContainInRows($departmentB);

    $this->actingAsForApi($userInCompanyB)
        ->getJson(route('api.departments.index'))
        ->assertResponseDoesNotContainInRows($departmentA)
        ->assertResponseContainsInRows($departmentB);
});

test('search matches location name', function () {
    // Departments table shows location as a column; before adding
    // location to Department's $searchableRelations the search
    // silently returned nothing when typing a location's name.
    $actor = User::factory()->superuser()->create();

    $location = Location::factory()->create(['name' => 'Bratislava HQ']);
    $matchingDepartment = Department::factory()->create(['location_id' => $location->id]);
    $otherDepartment = Department::factory()->create();

    $ids = collect($this->actingAsForApi($actor)
        ->getJson(route('api.departments.index', ['search' => 'Bratislava']))
        ->assertOk()
        ->json('rows'))
        ->pluck('id')
        ->all();

    expect($ids)->toContain($matchingDepartment->id);
    expect($ids)->not->toContain($otherDepartment->id);
});

test('search matches manager name', function () {
    $actor = User::factory()->superuser()->create();

    $manager = User::factory()->create(['first_name' => 'Ekaterina', 'last_name' => 'Volkova']);
    $matchingDepartment = Department::factory()->create(['manager_id' => $manager->id]);
    $otherDepartment = Department::factory()->create();

    $ids = collect($this->actingAsForApi($actor)
        ->getJson(route('api.departments.index', ['search' => 'Ekaterina']))
        ->assertOk()
        ->json('rows'))
        ->pluck('id')
        ->all();

    expect($ids)->toContain($matchingDepartment->id);
    expect($ids)->not->toContain($otherDepartment->id);
});

test('department index filters all supported exact fields', function () {
    $user = User::factory()->superuser()->create();
    $targetCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $targetManager = User::factory()->create();
    $otherManager = User::factory()->create();
    $targetLocation = Location::factory()->create();
    $otherLocation = Location::factory()->create();

    $targetDepartment = Department::factory()->create([
        'name' => 'Target Department',
        'company_id' => $targetCompany->id,
        'manager_id' => $targetManager->id,
        'location_id' => $targetLocation->id,
        'tag_color' => '#AA11AA',
    ]);

    $otherDepartment = Department::factory()->create([
        'name' => 'Other Department',
        'company_id' => $otherCompany->id,
        'manager_id' => $otherManager->id,
        'location_id' => $otherLocation->id,
        'tag_color' => '#11AA11',
    ]);

    $filters = [
        'name' => 'Target Department',
        'company_id' => $targetCompany->id,
        'manager_id' => $targetManager->id,
        'location_id' => $targetLocation->id,
        'tag_color' => '#AA11AA',
    ];

    foreach ($filters as $filterKey => $filterValue) {
        $this->actingAsForApi($user)
            ->getJson(route('api.departments.index', [$filterKey => $filterValue]))
            ->assertOk()
            ->assertResponseContainsInRows($targetDepartment)
            ->assertResponseDoesNotContainInRows($otherDepartment);
    }
});
