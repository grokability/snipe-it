<?php

use App\Mcp\Tools\AuditAssetTool;
use App\Mcp\Tools\CheckinAssetTool;
use App\Mcp\Tools\CheckoutAssetTool;
use App\Mcp\Tools\DeleteAssetTool;
use App\Mcp\Tools\UpdateAssetTool;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;

beforeEach(function () {
    [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();

    $this->assetA = Asset::factory()->for($this->companyA)->create();
    $this->assetB = Asset::factory()->for($this->companyB)->create();

    $this->userInCompanyA = $this->companyA->users()->save(
        User::factory()->editAssets()->deleteAssets()->auditAssets()->checkinAssets()->checkoutAssets()->make()
    );
    $this->superUser = $this->companyA->users()->save(User::factory()->superuser()->make());

    $this->settings->enableMultipleFullCompanySupport();
});

test('update blocked for cross company asset', function () {
    $this->actingAs($this->userInCompanyA);

    $response = (new UpdateAssetTool)->handle(new Request([
        'asset_tag' => $this->assetB->asset_tag,
        'name' => 'Should Not Apply',
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('assets', ['id' => $this->assetB->id, 'name' => 'Should Not Apply']);
});

test('update allowed for same company asset', function () {
    $this->actingAs($this->userInCompanyA);

    $content = (new UpdateAssetTool)->handle(new Request([
        'asset_tag' => $this->assetA->asset_tag,
        'name' => 'Updated Name',
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $this->assetA->id, 'name' => 'Updated Name']);
});

test('delete blocked for cross company asset', function () {
    $this->actingAs($this->userInCompanyA);

    $response = (new DeleteAssetTool)->handle(new Request([
        'asset_tag' => $this->assetB->asset_tag,
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted($this->assetB);
});

test('delete allowed for same company asset', function () {
    $this->actingAs($this->userInCompanyA);

    $content = (new DeleteAssetTool)->handle(new Request([
        'asset_tag' => $this->assetA->asset_tag,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted($this->assetA);
});

test('audit blocked for cross company asset', function () {
    $this->actingAs($this->userInCompanyA);

    (new AuditAssetTool)->handle(new Request([
        'asset_tag' => $this->assetB->asset_tag,
    ]));

    expect($this->assetB->fresh()->last_audit_date)->toBeNull();
});

test('audit allowed for same company asset', function () {
    $this->actingAs($this->userInCompanyA);

    $content = (new AuditAssetTool)->handle(new Request([
        'asset_tag' => $this->assetA->asset_tag,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect($this->assetA->fresh()->last_audit_date)->not->toBeNull();
});

test('checkin blocked for cross company asset', function () {
    $user = $this->companyB->users()->save(User::factory()->make());
    $checkedOutAsset = Asset::factory()->for($this->companyB)->assignedToUser($user)->create();

    $this->actingAs($this->userInCompanyA);

    $response = resolve(CheckinAssetTool::class)->handle(new Request([
        'asset_tag' => $checkedOutAsset->asset_tag,
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $checkedOutAsset->id, 'assigned_to' => $user->id]);
});

test('checkin allowed for same company asset', function () {
    $user = $this->companyA->users()->save(User::factory()->make());
    $asset = Asset::factory()->for($this->companyA)->assignedToUser($user)->create();

    $this->actingAs($this->userInCompanyA);

    $content = resolve(CheckinAssetTool::class)->handle(new Request([
        'asset_tag' => $asset->asset_tag,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'assigned_to' => null]);
});

test('checkout blocked for cross company asset', function () {
    $user = $this->companyA->users()->save(User::factory()->make());

    $this->actingAs($this->userInCompanyA);

    $response = resolve(CheckoutAssetTool::class)->handle(new Request([
        'asset_tag' => $this->assetB->asset_tag,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $this->assetB->id, 'assigned_to' => null]);
});

test('checkout allowed for same company asset', function () {
    $user = $this->companyA->users()->save(User::factory()->make());

    $this->actingAs($this->userInCompanyA);

    $content = resolve(CheckoutAssetTool::class)->handle(new Request([
        'asset_tag' => $this->assetA->asset_tag,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $this->assetA->id, 'assigned_to' => $user->id]);
});

test('superuser can update asset in any company', function () {
    $this->actingAs($this->superUser);

    $content = (new UpdateAssetTool)->handle(new Request([
        'asset_tag' => $this->assetB->asset_tag,
        'name' => 'Superuser Override',
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('assets', ['id' => $this->assetB->id, 'name' => 'Superuser Override']);
});

test('superuser can delete asset in any company', function () {
    $this->actingAs($this->superUser);

    $content = (new DeleteAssetTool)->handle(new Request([
        'asset_tag' => $this->assetB->asset_tag,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted($this->assetB);
});
