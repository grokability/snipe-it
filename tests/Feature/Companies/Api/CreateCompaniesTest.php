<?php

use App\Models\Company;
use App\Models\User;

test('requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.companies.store'))
        ->assertForbidden();
});

test('validation for creating company', function () {
    $this->actingAsForApi(User::factory()->createCompanies()->create())
        ->postJson(route('api.companies.store'))
        ->assertStatus(200)
        ->assertStatusMessageIs('error')
        ->assertJsonStructure([
            'messages' => [
                'name',
            ],
        ]);
});

test('can create company', function () {
    $this->actingAsForApi(User::factory()->createCompanies()->create())
        ->postJson(route('api.companies.store'), [
            'name' => 'My Cool Company',
            'notes' => 'A Cool Note',
        ])
        ->assertStatus(200)
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('companies', [
        'name' => 'My Cool Company',
        'notes' => 'A Cool Note',
    ]);
});

test('can reuse name of soft deleted company', function () {
    // Previously a DB-level unique index on companies.name made this
    // impossible — even if the original row was trashed, the schema
    // constraint matched it and the INSERT failed. The unique_undeleted
    // validation rule respects deleted_at, so the trashed row no
    // longer blocks creating a new one with the same name.
    $trashed = Company::factory()->create(['name' => 'Phoenix Co']);
    $trashed->delete();

    $this->actingAsForApi(User::factory()->createCompanies()->create())
        ->postJson(route('api.companies.store'), ['name' => 'Phoenix Co'])
        ->assertStatus(200)
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('companies', [
        'name' => 'Phoenix Co',
        'deleted_at' => null,
    ]);
});
