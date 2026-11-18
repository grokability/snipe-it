<?php

use App\Mcp\Tools\CheckinComponentTool;
use App\Mcp\Tools\CheckoutComponentTool;
use App\Mcp\Tools\DeleteComponentTool;
use App\Mcp\Tools\UpdateComponentTool;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;

beforeEach(function () {
    [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();

    $this->componentA = Component::factory()->for($this->companyA)->create(['qty' => 5]);
    $this->componentB = Component::factory()->for($this->companyB)->create(['qty' => 5]);

    $this->userInCompanyA = $this->companyA->users()->save(
        User::factory()->editComponents()->deleteComponents()->checkoutComponents()->checkinComponents()->make()
    );
    $this->superUser = $this->companyA->users()->save(User::factory()->superuser()->make());

    $this->settings->enableMultipleFullCompanySupport();
});

test('update blocked for cross company component', function () {
    $this->actingAs($this->userInCompanyA);

    $response = (new UpdateComponentTool)->handle(new Request([
        'id' => $this->componentB->id,
        'qty' => 99,
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('components', ['id' => $this->componentB->id, 'qty' => 99]);
});

test('update allowed for same company component', function () {
    $this->actingAs($this->userInCompanyA);

    $content = (new UpdateComponentTool)->handle(new Request([
        'id' => $this->componentA->id,
        'qty' => 10,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('components', ['id' => $this->componentA->id, 'qty' => 10]);
});

test('delete blocked for cross company component', function () {
    $this->actingAs($this->userInCompanyA);

    $response = (new DeleteComponentTool)->handle(new Request([
        'id' => $this->componentB->id,
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted($this->componentB);
});

test('delete allowed for same company component', function () {
    $this->actingAs($this->userInCompanyA);

    $content = (new DeleteComponentTool)->handle(new Request([
        'id' => $this->componentA->id,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted($this->componentA);
});

test('checkout blocked for cross company component', function () {
    $asset = Asset::factory()->for($this->companyA)->create();

    $this->actingAs($this->userInCompanyA);

    $response = (new CheckoutComponentTool)->handle(new Request([
        'id' => $this->componentB->id,
        'asset_id' => $asset->id,
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('components_assets', ['component_id' => $this->componentB->id]);
});

test('checkout allowed for same company component', function () {
    $asset = Asset::factory()->for($this->companyA)->create();

    $this->actingAs($this->userInCompanyA);

    $content = (new CheckoutComponentTool)->handle(new Request([
        'id' => $this->componentA->id,
        'asset_id' => $asset->id,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('components_assets', ['component_id' => $this->componentA->id, 'asset_id' => $asset->id]);
});

test('checkin blocked for cross company component', function () {
    $asset = Asset::factory()->for($this->companyB)->create();

    // Checkout component B to asset B as superuser
    $componentAssetId = DB::table('components_assets')->insertGetId([
        'component_id' => $this->componentB->id,
        'asset_id' => $asset->id,
        'assigned_qty' => 1,
        'created_by' => $this->superUser->id,
        'created_at' => now(),
    ]);

    $this->actingAs($this->userInCompanyA);

    $response = (new CheckinComponentTool)->handle(new Request([
        'component_asset_id' => $componentAssetId,
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('components_assets', ['id' => $componentAssetId]);
});

test('superuser can update component in any company', function () {
    $this->actingAs($this->superUser);

    $content = (new UpdateComponentTool)->handle(new Request([
        'id' => $this->componentB->id,
        'qty' => 20,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('components', ['id' => $this->componentB->id, 'qty' => 20]);
});

test('superuser can delete component in any company', function () {
    $this->actingAs($this->superUser);

    $content = (new DeleteComponentTool)->handle(new Request([
        'id' => $this->componentB->id,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted($this->componentB);
});
