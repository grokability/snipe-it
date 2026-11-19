<?php

use App\Models\User;

test('requires permission to view create company page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('companies.create'))
        ->assertForbidden();
});

test('create company page renders', function () {
    $this->actingAs(User::factory()->createCompanies()->create())
        ->get(route('companies.create'))
        ->assertOk()
        ->assertViewIs('companies.edit');
});

test('requires permission to create company', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('companies.store'))
        ->assertForbidden();
});

test('valid data required to create company', function () {
    $this->actingAs(User::factory()->createCompanies()->create())
        ->post(route('companies.store'), [
            //
        ])
        ->assertSessionHasErrors([
            'name',
        ]);
});

test('can create company', function () {
    $data = [
        'email' => 'email@example.com',
        'fax' => '619-666-6666',
        'name' => 'My New Company',
        'phone' => '619-555-5555',
    ];

    $user = User::factory()->createCompanies()->create();

    $this->actingAs($user)
        ->post(route('companies.store'), array_merge($data, ['redirect_option' => 'index']))
        ->assertRedirect(route('companies.index'));

    $this->assertDatabaseHas('companies', array_merge($data));
});
