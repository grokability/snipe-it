<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;

test('adheres to full multiple companies support scoping', function (User $actor, Company $company, Closure $assertions) {
    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAs($actor)
        ->post(route('consumables.store'), [
            'name' => 'My Cool Consumable',
            'category_id' => Category::factory()->forConsumables()->create()->id,
            'company_id' => $company->id,
        ]);

    $consumable = Consumable::where('name', 'My Cool Consumable')->sole();

    $assertions($consumable);
})->with('FMCS data');
