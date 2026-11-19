<?php

use App\Models\User;

test('user modal renders', function () {
    // Force distinctive attribute values here rather than accepting the
    // Faker defaults. assertDontSee does a substring check on the entire
    // response body, so a Faker-generated first name of "Gene" collides
    // with legitimate modal text like "Generate Password" and produces a
    // false-positive failure. The strings below cannot appear inside any
    // translated label, class name, or DOM attribute in the modal.
    $admin = User::factory()->createUsers()->create([
        'first_name' => 'ZzModalTestFirst',
        'last_name' => 'ZzModalTestLast',
        'email' => 'zz-modal-test@example.invalid',
    ]);

    $response = $this->actingAs($admin)
        ->get('modals/user')
        ->assertOk();

    $response->assertStatus(200);
    $response->assertDontSee($admin->first_name);
    $response->assertDontSee($admin->last_name);
    $response->assertDontSee($admin->email);
});

test('department modal renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get('modals/model')
        ->assertOk();
});

test('status label modal renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get('modals/statuslabel')
        ->assertOk();
});

test('location modal renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get('modals/location')
        ->assertOk();
});

test('kit model modal renders', function () {
    $kit = \App\Models\PredefinedKit::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('modal.show', ['type' => 'kit-model', 'itemId' => $kit->id]))
        ->assertOk()
        ->assertSee(route('api.kits.models.store', $kit->id));
});

test('kit license modal renders', function () {
    $kit = \App\Models\PredefinedKit::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('modal.show', ['type' => 'kit-license', 'itemId' => $kit->id]))
        ->assertOk()
        ->assertSee(route('api.kits.licenses.store', $kit->id));
});

test('kit accessory modal renders', function () {
    $kit = \App\Models\PredefinedKit::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('modal.show', ['type' => 'kit-accessory', 'itemId' => $kit->id]))
        ->assertOk()
        ->assertSee(route('api.kits.accessories.store', $kit->id));
});

test('kit consumable modal renders', function () {
    $kit = \App\Models\PredefinedKit::factory()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('modal.show', ['type' => 'kit-consumable', 'itemId' => $kit->id]))
        ->assertOk()
        ->assertSee(route('api.kits.consumables.store', $kit->id));
});

test('category modal renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get('modals/category')
        ->assertOk();
});

test('company modal renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get('modals/company')
        ->assertOk()
        ->assertSee(route('api.companies.store'))
        ->assertSee(trans('admin/companies/table.create'));
});

test('manufacturer modal renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get('modals/manufacturer')
        ->assertOk();
});

test('supplier modal renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get('modals/supplier')
        ->assertOk();
});
