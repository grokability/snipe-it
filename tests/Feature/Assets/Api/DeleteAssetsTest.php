<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;

test('requires permission', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->create())
        ->deleteJson(route('api.assets.destroy', $asset))
        ->assertForbidden();

    $this->assertNotSoftDeleted($asset);
});

test('adheres to full multiple companies support scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->for($companyA)->create();
    $assetB = Asset::factory()->for($companyB)->create();
    $assetC = Asset::factory()->for($companyB)->create();

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = $companyA->users()->save(User::factory()->deleteAssets()->make());
    $userInCompanyB = $companyB->users()->save(User::factory()->deleteAssets()->make());

    $this->settings->enableMultipleFullCompanySupport();

    $this->actingAsForApi($userInCompanyA)
        ->deleteJson(route('api.assets.destroy', $assetB))
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($userInCompanyB)
        ->deleteJson(route('api.assets.destroy', $assetA))
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($superUser)
        ->deleteJson(route('api.assets.destroy', $assetC))
        ->assertStatusMessageIs('success');

    $this->assertNotSoftDeleted($assetA);
    $this->assertNotSoftDeleted($assetB);
    $this->assertSoftDeleted($assetC);
});

test('cannot delete asset that is checked out', function () {
    $this->markTestSkipped('This behavior is not functioning yet.');
});

test('can delete asset', function () {
    $asset = Asset::factory()->create();

    $this->actingAsForApi(User::factory()->deleteAssets()->create())
        ->deleteJson(route('api.assets.destroy', $asset))
        ->assertStatusMessageIs('success');

    $this->assertSoftDeleted($asset);
    $this->assertHasTheseActionLogs($asset, ['create', 'delete']);
});
