<?php

use App\Models\Accessory;
use App\Models\SavedSearch;
use App\Models\User;

test('two users can use the same name on the same listing', function () {
    $adminA = User::factory()->create();
    $adminB = User::factory()->create();

    $this->actingAsForApi($adminA)
        ->postJson(route('api.saved-searches.store'), [
            'table_name' => 'accessoriesListingTable',
            'name' => 'Weekly audit',
            'query_string' => 'filter[category]=1',
        ])
        ->assertCreated();

    $this->actingAsForApi($adminB)
        ->postJson(route('api.saved-searches.store'), [
            'table_name' => 'accessoriesListingTable',
            'name' => 'Weekly audit',
            'query_string' => 'filter[category]=2',
        ])
        ->assertCreated();

    expect(SavedSearch::where('created_by', $adminA->id)->count())->toBe(1);
    expect(SavedSearch::where('created_by', $adminB->id)->count())->toBe(1);
});

test('same user cannot reuse a name on the same listing', function () {
    $admin = User::factory()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.saved-searches.store'), [
            'table_name' => 'accessoriesListingTable',
            'name' => 'Weekly audit',
            'query_string' => 'filter[category]=1',
        ])
        ->assertCreated();

    $this->actingAsForApi($admin)
        ->postJson(route('api.saved-searches.store'), [
            'table_name' => 'accessoriesListingTable',
            'name' => 'Weekly audit',
            'query_string' => 'filter[category]=99',
        ])
        ->assertStatus(422);

    expect(SavedSearch::where('created_by', $admin->id)->count())->toBe(1);
});

test('same user can reuse a name on a different listing', function () {
    $admin = User::factory()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.saved-searches.store'), [
            'table_name' => 'accessoriesListingTable',
            'name' => 'Weekly audit',
            'query_string' => 'filter[category]=1',
        ])
        ->assertCreated();

    $this->actingAsForApi($admin)
        ->postJson(route('api.saved-searches.store'), [
            'table_name' => 'assetsListingTable',
            'name' => 'Weekly audit',
            'query_string' => 'filter[category]=1',
        ])
        ->assertCreated();

    expect(SavedSearch::where('created_by', $admin->id)->count())->toBe(2);
});

test('model metadata accepts a real eloquent class', function () {
    $admin = User::factory()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.saved-searches.store'), [
            'table_name' => 'accessoriesListingTable',
            'model' => Accessory::class,
            'name' => 'Weekly audit',
            'query_string' => 'filter[category]=1',
        ])
        ->assertCreated();

    expect(SavedSearch::first()->model)->toBe(Accessory::class);
});

test('model metadata accepts null', function () {
    $admin = User::factory()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.saved-searches.store'), [
            'table_name' => 'accessoriesListingTable',
            'model' => null,
            'name' => 'Weekly audit',
            'query_string' => 'filter[category]=1',
        ])
        ->assertCreated();

    expect(SavedSearch::first()->model)->toBeNull();
});

test('model metadata rejects non eloquent strings', function (string $invalid) {
    $admin = User::factory()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.saved-searches.store'), [
            'table_name' => 'accessoriesListingTable',
            'model' => $invalid,
            'name' => 'Weekly audit',
            'query_string' => 'filter[category]=1',
        ])
        ->assertStatus(422)
        ->assertJsonPath('status', 'error')
        ->assertJsonStructure(['messages' => ['model']]);

    expect(SavedSearch::count())->toBe(0);
})->with('provideInvalidModelStrings');

dataset('provideInvalidModelStrings', function () {
    return [
        'nonexistent class' => ['App\\Models\\DoesNotExist'],
        'not a class at all' => ['<script>alert(1)</script>'],
        // Random non-Eloquent FQCN that IS loadable. Guards
        // against a future weakening of the subclass check to
        // just `class_exists`.
        'non-eloquent class' => ['Illuminate\\Support\\Str'],
    ];
});
