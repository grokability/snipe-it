<?php

use App\Models\Category;
use App\Models\Component;

uses(\Tests\Support\ProvidesDataForFullMultipleCompanySupportTesting::class);

test('adheres to full multiple companies support scoping', function ($data) {
    ['actor' => $actor, 'company_attempting_to_associate' => $company, 'assertions' => $assertions] = $data();

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
})->with('dataForFullMultipleCompanySupportTesting');
