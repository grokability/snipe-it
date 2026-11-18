<?php

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.assets.assigned_accessories', Asset::factory()->create()))
        ->assertForbidden();
});

test('adheres to company scoping', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $asset = Asset::factory()->for($companyA)->create();

    $user = User::factory()->forCompany($companyB)->viewAssets()->create();

    $this->actingAsForApi($user)
        ->getJson(route('api.assets.assigned_accessories', $asset))
        ->assertOk()
        ->assertStatusMessageIs('error')
        ->assertMessagesAre('Asset not found');
});

test('does not leak accessory details when caller lacks accessories view', function () {
    $asset = Asset::factory()->create();
    $accessory = Accessory::factory()->create(['name' => 'Hidden Bluetooth Adapter']);
    DB::table('accessories_checkout')->insert([
        'accessory_id' => $accessory->id,
        'assigned_to' => $asset->id,
        'assigned_type' => Asset::class,
        'note' => 'sensitive accessory note',
        'created_by' => User::factory()->superuser()->create()->id,
        'created_at' => now(),
    ]);

    // viewAssets only. No viewAccessories.
    $actor = User::factory()->viewAssets()->create();

    $this->actingAsForApi($actor)
        ->getJson(route('api.assets.assigned_accessories', $asset))
        ->assertOk()
        ->assertJsonPath('total', 0)
        ->assertJsonCount(0, 'rows');
});

test('returns full accessory details when caller has accessories view', function () {
    $asset = Asset::factory()->create();
    $accessory = Accessory::factory()->create(['name' => 'Visible Bluetooth Adapter']);
    DB::table('accessories_checkout')->insert([
        'accessory_id' => $accessory->id,
        'assigned_to' => $asset->id,
        'assigned_type' => Asset::class,
        'note' => 'accessory note',
        'created_by' => User::factory()->superuser()->create()->id,
        'created_at' => now(),
    ]);

    $actor = User::factory()->viewAssets()->viewAccessories()->create();

    $this->actingAsForApi($actor)
        ->getJson(route('api.assets.assigned_accessories', $asset))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('rows.0.accessory.id', $accessory->id)
        ->assertJsonPath('rows.0.accessory.name', 'Visible Bluetooth Adapter')
        ->assertJsonPath('rows.0.note', 'accessory note');
});
