<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.assets.assigned_components', Asset::factory()->create()))
        ->assertForbidden();
});

test('adheres to company scoping', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $asset = Asset::factory()->for($companyA)->create();

    $user = User::factory()->forCompany($companyB)->viewAssets()->create();

    $this->actingAsForApi($user)
        ->getJson(route('api.assets.assigned_components', $asset))
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre('Asset not found');
});

test('can get components assigned to specific asset', function () {
    $unassociatedComponent = Component::factory()->create();

    $asset = Asset::factory()->hasComponents(2)->create();

    $componentsAssignedToAsset = $asset->components;

    // Needs viewComponents too - the transformer now omits components
    // the caller can't view, so a viewAssets-only actor would see an
    // empty list under the fix.
    $response = $this->actingAsForApi(User::factory()->viewAssets()->viewComponents()->create())
        ->getJson(route('api.assets.assigned_components', $asset))
        ->assertOk()
        // ->assertResponseContainsInRows($componentsAssignedToAsset)
        // ->assertResponseDoesNotContainInRows($unassociatedComponent)
        ->assertJson(function (AssertableJson $json) {
            $json->where('total', 2)
                ->count('rows', 2)
                ->etc();
        });
});

test('does not leak component details when caller lacks components view', function () {
    $asset = Asset::factory()->create();
    $component = Component::factory()->create(['name' => 'Hidden SSD Component']);
    $component->assets()->attach($component->id, [
        'component_id' => $component->id,
        'assigned_qty' => 2,
        'created_by' => User::factory()->superuser()->create()->id,
        'asset_id' => $asset->id,
        'note' => 'sensitive component note',
    ]);

    // viewAssets only. No viewComponents.
    $actor = User::factory()->viewAssets()->create();

    $this->actingAsForApi($actor)
        ->getJson(route('api.assets.assigned_components', $asset))
        ->assertOk()
        ->assertJsonPath('total', 0)
        ->assertJsonCount(0, 'rows');
});

test('returns full component details when caller has components view', function () {
    $asset = Asset::factory()->create();
    $component = Component::factory()->create(['name' => 'Visible SSD']);
    $component->assets()->attach($component->id, [
        'component_id' => $component->id,
        'assigned_qty' => 2,
        'created_by' => User::factory()->superuser()->create()->id,
        'asset_id' => $asset->id,
        'note' => 'component note',
    ]);

    $actor = User::factory()->viewAssets()->viewComponents()->create();

    $this->actingAsForApi($actor)
        ->getJson(route('api.assets.assigned_components', $asset))
        ->assertOk()
        ->assertJsonPath('rows.0.name.id', $component->id)
        ->assertJsonPath('rows.0.name.name', 'Visible SSD')
        ->assertJsonPath('rows.0.assigned_qty', 2)
        ->assertJsonPath('rows.0.note', 'component note');
});

test('show asset with components true query omits components when denied', function () {
    $asset = Asset::factory()->create();
    $component = Component::factory()->withInitialAcquisition(null, 500.0)->create(['name' => 'Hidden CPU']);
    $component->assets()->attach($component->id, [
        'component_id' => $component->id,
        'assigned_qty' => 3,
        'created_by' => User::factory()->superuser()->create()->id,
        'asset_id' => $asset->id,
    ]);

    $actor = User::factory()->viewAssets()->create();

    $response = $this->actingAsForApi($actor)
        ->getJson(route('api.assets.show', ['hardware' => $asset->id, 'components' => 'true']))
        ->assertOk();

    expect($response->json('components'))->toBe([], 'Denied components must be absent entirely - not even id / pivot_id');
});
