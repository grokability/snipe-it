<?php

use App\Models\User;

test('page renders', function () {
    $this->get(route('setup'))
        ->assertOk();
});

test('page redirects if no records found', function () {
    $this->assertDatabaseEmpty('users');

    $this->get(route('home'))
        ->assertStatus(302)
        ->assertRedirectToRoute('setup');
});

test('page redirects if records found', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('setup'))
        ->assertStatus(302)
        ->assertRedirectToRoute('home');
});

test('migration page renders', function () {
    $this->assertDatabaseEmpty('users');

    $this->post(route('setup.migrate'))
        ->assertOk();
});

test('create first user page renders', function () {
    $this->assertDatabaseEmpty('users');

    $this->get(route('setup.user'))
        ->assertOk();
});

test('create first user validation', function () {
    $this->assertDatabaseEmpty('users');

    $response = $this->post(route('setup.user.save'))
        ->assertStatus(302);

    $this->followRedirects($response)->assertSee('error');
    $this->assertDatabaseCount('users', 0);
});

test('create first user saved', function () {
    $this->assertDatabaseEmpty('users');

    $this->post(route('setup.user.save'),
        [
            'site_name' => 'Snipe-IT',
            'first_name' => 'First',
            'last_name' => 'Admin',
            'username' => 'AwesomeAdmin',
            'password' => '0834529!!*423',
            'password_confirmation' => '0834529!!*423',
            'email_domain' => 'example.org',
        ])
        ->assertRedirectToRoute('setup.done')
        ->assertStatus(302)
        ->assertSessionHas('success');

    $this->assertDatabaseCount('users', 1);
});
