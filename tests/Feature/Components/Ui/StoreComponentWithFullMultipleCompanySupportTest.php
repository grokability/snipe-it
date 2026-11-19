<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\User;

test('adheres to full multiple companies support scoping', function (User $actor, Company $company, Closure $assertions) {
    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAs($actor)
        ->post(route('components.store'), [
            'name' => 'My Cool Component',
            'qty' => '1',
            'category_id' => Category::factory()->create()->id,
            'company_id' => $company->id,
        ]);

    $component = Component::where('name', 'My Cool Component')->sole();

    $assertions($component);
})->with('FMCS data');
