<?php

use App\Models\Company;
use App\Models\Component;
use App\Models\User;

test('requires permission', function () {
    $component = Component::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.components.destroy', $component))
        ->assertForbidden();

    $this->assertNotSoftDeleted($component);
});

test('adheres to full multiple companies support scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $componentA = Component::factory()->for($companyA)->create();
    $componentB = Component::factory()->for($companyB)->create();
    $componentC = Component::factory()->for($companyB)->create();

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = $companyA->users()->save(User::factory()->deleteComponents()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->deleteComponents()->make());

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($userInCompanyA)
        ->deleteJson(route('api.components.destroy', $componentB))
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($userInCompanyB)
        ->deleteJson(route('api.components.destroy', $componentA))
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($superUser)
        ->deleteJson(route('api.components.destroy', $componentC))
        ->assertStatusMessageIs('success');

    $this->assertNotSoftDeleted($componentA);
    $this->assertNotSoftDeleted($componentB);
    $this->assertSoftDeleted($componentC);
});

test('can delete components', function () {
    $component = Component::factory()->create();

    $this->actingAsForApi(User::factory()->deleteComponents()->create())
        ->deleteJson(route('api.components.destroy', $component))
        ->assertStatusMessageIs('success');

    $this->assertSoftDeleted($component);
});

test('cannot delete component if checked out', function () {
    $component = Component::factory()->checkedOutToAsset()->create();

    $this->actingAsForApi(User::factory()->deleteComponents()->create())
        ->deleteJson(route('api.components.destroy', $component))
        ->assertStatusMessageIs('error');
});
