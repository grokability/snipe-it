<?php

use App\Models\Accessory;
use App\Models\Category;
use App\Models\Company;
use App\Models\User;

test('adheres to full multiple companies support scoping', function (User $actor, Company $company, Closure $assertions) {
    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAs($actor)
        ->post(route('accessories.store'), [
            'redirect_option' => 'index',
            'name' => 'My Cool Accessory',
            'qty' => '1',
            'category_id' => Category::factory()->create()->id,
            'company_id' => $company->id,
        ]);

    $accessory = Accessory::withoutGlobalScopes()->where([
        'name' => 'My Cool Accessory',
    ])->sole();

    $assertions($accessory);
})->with('FMCS data');
