<?php

use App\Mcp\Tools\ListAccessoriesTool;
use App\Mcp\Tools\OffboardUserTool;
use App\Mcp\Tools\TransferAssetsTool;
use App\Mcp\Tools\UploadAssetImageTool;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Mcp\Request;

beforeEach(function () {
    [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();

    // Non-superuser with the perms the tools need. Superusers bypass
    // FMCS so would hide the scope enforcement we are trying to verify.
    $this->userInA = $this->companyA->users()->save(
        User::factory()
            ->editUsers()
            ->editAssets()
            ->checkinAssets()
            ->checkoutAssets()
            ->viewAccessories()
            ->make()
    );
});

test('strict offboard blocks cross company target', function () {
    $this->settings->enableMultipleFullCompanySupport();
    $targetInB = $this->companyB->users()->save(User::factory()->make(['username' => 'leaver']));

    $this->actingAs($this->userInA);
    $response = (new OffboardUserTool)->handle(new Request(['username' => 'leaver']));

    expect($response->responses()->first()->isError())->toBeTrue();
    expect($targetInB->fresh()->deleted_at)->toBeNull();
});

test('floater offboard blocks cross company target', function () {
    $this->settings->enableFloaterMode();
    $targetInB = $this->companyB->users()->save(User::factory()->make(['username' => 'leaver']));

    $this->actingAs($this->userInA);
    $response = (new OffboardUserTool)->handle(new Request(['username' => 'leaver']));

    expect($response->responses()->first()->isError())->toBeTrue();
    expect($targetInB->fresh()->deleted_at)->toBeNull();
});

test('scoped locations offboard blocks cross company target', function () {
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
    $targetInB = $this->companyB->users()->save(User::factory()->make(['username' => 'leaver']));

    $this->actingAs($this->userInA);
    $response = (new OffboardUserTool)->handle(new Request(['username' => 'leaver']));

    expect($response->responses()->first()->isError())->toBeTrue();
    expect($targetInB->fresh()->deleted_at)->toBeNull();
});

test('strict offboard allows same company target', function () {
    $this->settings->enableMultipleFullCompanySupport();
    $targetInA = $this->companyA->users()->save(User::factory()->make(['username' => 'leaver']));

    $this->actingAs($this->userInA);
    $content = (new OffboardUserTool)->handle(new Request([
        'username' => 'leaver',
        'dry_run' => false,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect(User::withTrashed()->find($targetInA->id)->deleted_at)->not->toBeNull();
});

test('strict offboard blocks target with unreachable pivot company', function () {
    // Multi-company user case: target belongs to BOTH the actor's
    // company AND a company the actor cannot reach. The scoped
    // User::where query DOES find them (shared Company A pivot
    // row), and the Gate::allows('update') check passes, but
    // offboarding would check in only the actor-visible items via
    // FMCS-scoped queries and then soft-delete the whole user,
    // orphaning the Company B assignments. The tool must refuse.
    $this->settings->enableMultipleFullCompanySupport();

    // Use ->save(make()) to avoid the User factory's auto-attach a
    // fresh company behavior in create(), which would add a third
    // unreachable pivot row and muddy the "both companies, nothing
    // extra" setup this test is pinning down.
    $targetBoth = $this->companyA->users()->save(User::factory()->make(['username' => 'leaver']));
    $this->companyB->users()->attach($targetBoth);

    $this->actingAs($this->userInA);
    $response = (new OffboardUserTool)->handle(new Request([
        'username' => 'leaver',
        'dry_run' => false,
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    expect($targetBoth->fresh()->deleted_at)->toBeNull();
});

test('superuser offboard allows target with unreachable pivot company', function () {
    // Superusers bypass the cross-company guard: they can reach
    // every company, so there is no "unreachable pivot row" case
    // for them to protect against.
    $this->settings->enableMultipleFullCompanySupport();
    $targetBoth = $this->companyA->users()->save(User::factory()->make(['username' => 'leaver']));
    $this->companyB->users()->attach($targetBoth);

    $this->actingAs(User::factory()->superuser()->create());
    $content = (new OffboardUserTool)->handle(new Request([
        'username' => 'leaver',
        'dry_run' => false,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    expect(User::withTrashed()->find($targetBoth->id)->deleted_at)->not->toBeNull();
});

test('strict transfer blocks cross company source', function () {
    $this->settings->enableMultipleFullCompanySupport();
    $sourceInB = $this->companyB->users()->save(User::factory()->make(['username' => 'source']));
    $targetInA = $this->companyA->users()->save(User::factory()->make(['username' => 'target']));

    $this->actingAs($this->userInA);
    $response = (new TransferAssetsTool)->handle(new Request([
        'from_username' => 'source',
        'to_username' => 'target',
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('floater transfer blocks cross company source', function () {
    $this->settings->enableFloaterMode();
    $sourceInB = $this->companyB->users()->save(User::factory()->make(['username' => 'source']));
    $targetInA = $this->companyA->users()->save(User::factory()->make(['username' => 'target']));

    $this->actingAs($this->userInA);
    $response = (new TransferAssetsTool)->handle(new Request([
        'from_username' => 'source',
        'to_username' => 'target',
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('scoped locations transfer blocks cross company target', function () {
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
    $sourceInA = $this->companyA->users()->save(User::factory()->make(['username' => 'source']));
    $targetInB = $this->companyB->users()->save(User::factory()->make(['username' => 'target']));

    $this->actingAs($this->userInA);
    $response = (new TransferAssetsTool)->handle(new Request([
        'from_username' => 'source',
        'to_username' => 'target',
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('strict upload blocks cross company asset', function () {
    Storage::fake('public');
    $this->settings->enableMultipleFullCompanySupport();
    $assetB = Asset::factory()->for($this->companyB)->create();

    $this->actingAs($this->userInA);
    $response = (new UploadAssetImageTool)->handle(new Request([
        'asset_tag' => $assetB->asset_tag,
        'image_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
        'mime_type' => 'image/png',
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    expect($assetB->fresh()->image)->toBeNull();
});

test('floater upload blocks cross company asset', function () {
    Storage::fake('public');
    $this->settings->enableFloaterMode();
    $assetB = Asset::factory()->for($this->companyB)->create();

    $this->actingAs($this->userInA);
    $response = (new UploadAssetImageTool)->handle(new Request([
        'asset_tag' => $assetB->asset_tag,
        'image_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
        'mime_type' => 'image/png',
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('scoped locations upload blocks cross company asset', function () {
    Storage::fake('public');
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
    $assetB = Asset::factory()->for($this->companyB)->create();

    $this->actingAs($this->userInA);
    $response = (new UploadAssetImageTool)->handle(new Request([
        'asset_tag' => $assetB->asset_tag,
        'image_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
        'mime_type' => 'image/png',
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('strict list accessories excludes other company', function () {
    $this->settings->enableMultipleFullCompanySupport();
    $mine = Accessory::factory()->for($this->companyA)->create(['name' => 'OursA']);
    $theirs = Accessory::factory()->for($this->companyB)->create(['name' => 'TheirsB']);

    $this->actingAs($this->userInA);
    $content = (new ListAccessoriesTool)->handle(new Request(['limit' => 500]))->getStructuredContent();
    $names = array_column($content['accessories'], 'name');

    expect($names)->toContain('OursA');
    expect($names)->not->toContain('TheirsB');
});

test('floater list accessories includes null company accessory', function () {
    $this->settings->enableFloaterMode();
    $mine = Accessory::factory()->for($this->companyA)->create(['name' => 'OursA']);
    $floater = Accessory::factory()->create(['name' => 'Floater', 'company_id' => null]);
    $theirs = Accessory::factory()->for($this->companyB)->create(['name' => 'TheirsB']);

    $this->actingAs($this->userInA);
    $content = (new ListAccessoriesTool)->handle(new Request(['limit' => 500]))->getStructuredContent();
    $names = array_column($content['accessories'], 'name');

    expect($names)->toContain('OursA');
    expect($names)->toContain('Floater');
    expect($names)->not->toContain('TheirsB');
});

test('scoped locations list accessories excludes other company', function () {
    // In scoped-locations mode, visibility also depends on
    // user-location matching, so we only assert the cross-company
    // negative here. "Can see own company" is covered by the strict
    // and floater variants above.
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
    Accessory::factory()->for($this->companyB)->create(['name' => 'TheirsB']);

    $this->actingAs($this->userInA);
    $content = (new ListAccessoriesTool)->handle(new Request(['limit' => 500]))->getStructuredContent();
    $names = array_column($content['accessories'], 'name');

    expect($names)->not->toContain('TheirsB');
});
