<?php

use App\Models\Company;
use App\Models\User;

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('companies.edit', Company::factory()->create()))
        ->assertOk();
});

test('parent company picker ships only top level and exclude id data attrs', function () {
    // Regression guard for the migration off @include('partials.forms
    // .edit.company-select'). The parent-company picker relies on two
    // data-* hooks read by the js-data-ajax initializer in snipeit.js:
    // data-only-top-level="true" so sub-companies get greyed out (they
    // can't themselves become parents), and data-exclude-id="{id}" so
    // the company being edited never appears in its own parent list.
    $company = Company::factory()->create();

    $response = $this->actingAs(User::factory()->superuser()->create())
        ->get(route('companies.edit', $company))
        ->assertOk();

    $response->assertSee('data-only-top-level="true"', false);
    $response->assertSee('data-exclude-id="'.$company->id.'"', false);
    $response->assertSee('id="submit_button"', false);
});
