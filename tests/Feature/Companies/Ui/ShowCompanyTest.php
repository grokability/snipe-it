<?php

use App\Models\Company;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('companies.show', Company::factory()->create()))
        ->assertOk();
});

test('show page includes parent company breadcrumb hierarchy', function () {
    $parent = Company::factory()->create(['name' => 'Parent Breadcrumb Company']);
    $child = Company::factory()->childOf($parent)->create(['name' => 'Child Breadcrumb Company']);

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('companies.show', $child))
        ->assertOk()
        ->assertSeeInOrder([
            route('companies.show', $parent),
            route('companies.show', $child),
        ]);
});

test('sidebar shows children for parent company', function () {
    $parent = Company::factory()->create(['name' => 'Parent Sidebar Co']);
    $childA = Company::factory()->childOf($parent)->create(['name' => 'Aardvark Subsidiary']);
    $childB = Company::factory()->childOf($parent)->create(['name' => 'Zebra Subsidiary']);
    $unrelated = Company::factory()->create(['name' => 'Unrelated Co']);

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('companies.show', $parent))
        ->assertOk()
        ->assertSee(trans('admin/companies/table.children'))
        ->assertSee($childA->name)
        ->assertSee($childB->name)
        ->assertDontSee($unrelated->name);
});

test('sidebar shows parent for child company', function () {
    $parent = Company::factory()->create(['name' => 'Top Level Sidebar Co']);
    $child = Company::factory()->childOf($parent)->create(['name' => 'Subsidiary Sidebar Co']);

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('companies.show', $child))
        ->assertOk()
        ->assertSee(trans('admin/companies/table.parent'))
        ->assertSee($parent->name);
});

test('child companies tab appears when company has children', function () {
    $parent = Company::factory()->create(['name' => 'TabbedParent']);
    Company::factory()->childOf($parent)->create(['name' => 'TabbedChild']);

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('companies.show', $parent))
        ->assertOk()
        ->assertSee('href="#companies"', false);
    // raw HTML attribute substring
});

test('child companies tab does not appear for standalone company', function () {
    $solo = Company::factory()->create(['name' => 'TabbedStandalone']);

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('companies.show', $solo))
        ->assertOk()
        ->assertDontSee('href="#companies"', false);
});

test('sidebar shows children for fmcs scoped non superuser', function () {
    // Regression: $company->children uses withoutGlobalScopes() on the relation
    // so a non-superuser viewing a parent (they belong to) still sees its kids
    // listed in the info-panel even though they don't directly belong to those kids.
    $this->settings->enableMultipleFullCompanySupport();

    $parent = Company::factory()->create(['name' => 'FMCSParent']);
    $child = Company::factory()->childOf($parent)->create(['name' => 'FMCSChild']);

    $user = $parent->users()->save(User::factory()->viewCompanies()->create());

    $this->actingAs($user)
        ->get(route('companies.show', $parent))
        ->assertOk()
        ->assertSee($child->name);
});

test('sidebar hierarchy blocks hidden when company is standalone', function () {
    $solo = Company::factory()->create(['name' => 'Standalone Sidebar Co']);

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('companies.show', $solo))
        ->assertOk()
        ->assertDontSee(trans('admin/companies/table.children'));
});
