<?php

use App\Models\Company;
use Illuminate\Support\Facades\Storage;

test('soft delete preserves image file', function () {
    Storage::fake('public');
    $company = Company::factory()->create(['image' => 'acme-logo.png']);
    Storage::disk('public')->put('companies/acme-logo.png', 'fake image bytes');

    $company->delete();

    Storage::disk('public')->assertExists('companies/acme-logo.png');
});

test('force delete wipes image file', function () {
    Storage::fake('public');
    $company = Company::factory()->create(['image' => 'acme-logo.png']);
    Storage::disk('public')->put('companies/acme-logo.png', 'fake image bytes');

    $company->forceDelete();

    Storage::disk('public')->assertMissing('companies/acme-logo.png');
});
