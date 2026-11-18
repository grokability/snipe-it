<?php

use App\Mcp\Tools\TransferAssetsTool;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\Company;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\Support\Settings;

beforeEach(function () {
    $this->actingAs(User::factory()->superuser()->create());
});

function transferAssetsTool(array $args): ResponseFactory
{
    return resolve(TransferAssetsTool::class)->handle(new Request($args));
}

test('dry run returns preview without moving anything', function () {
    $from = User::factory()->create(['username' => 'leaver']);
    $to = User::factory()->create(['username' => 'receiver']);
    $asset = Asset::factory()->assignedToUser($from)->create();

    $content = transferAssetsTool(['from_username' => 'leaver', 'to_username' => 'receiver'])
        ->getStructuredContent();

    expect($content['dry_run'])->toBeTrue();
    expect($content['preview']['assets_to_transfer'])->toHaveCount(1);
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'assigned_to' => $from->id]);
});

test('dry run is default', function () {
    User::factory()->create(['username' => 'a']);
    User::factory()->create(['username' => 'b']);

    $content = transferAssetsTool(['from_username' => 'a', 'to_username' => 'b'])->getStructuredContent();

    expect($content['dry_run'])->toBeTrue();
});

test('execute moves assets', function () {
    $from = User::factory()->create(['username' => 'leaver']);
    $to = User::factory()->create(['username' => 'receiver']);
    $asset = Asset::factory()->assignedToUser($from)->create();

    transferAssetsTool(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

    $fresh = $asset->fresh();
    expect($fresh->assigned_to)->toBe($to->id);
});

test('execute moves license seats', function () {
    $from = User::factory()->create(['username' => 'leaver']);
    $to = User::factory()->create(['username' => 'receiver']);
    $license = License::factory()->create();
    $seat = LicenseSeat::factory()->create([
        'license_id' => $license->id,
        'assigned_to' => $from->id,
    ]);

    transferAssetsTool(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

    expect($seat->fresh()->assigned_to)->toBe($to->id);
});

test('execute moves accessory checkouts', function () {
    $from = User::factory()->create(['username' => 'leaver']);
    $to = User::factory()->create(['username' => 'receiver']);
    $accessory = Accessory::factory()->create();
    $checkout = AccessoryCheckout::factory()->create([
        'accessory_id' => $accessory->id,
        'assigned_type' => User::class,
        'assigned_to' => $from->id,
    ]);

    transferAssetsTool(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

    expect($checkout->fresh()->assigned_to)->toBe($to->id);
});

test('rejects cross company asset transfer under fmcs', function () {
    // Same bug class as the asset-model bulk-fulfill route a
    // reporter filed: the per-asset checkOut() needs a
    // canCheckoutTo() guard under FMCS strict mode so a
    // company-A asset doesn't land on a user who only belongs
    // to company B.
    [$from, $to, $operator] = fmcsFixture($this->settings, $companyA, $companyB);
    $this->actingAs($operator);

    $asset = Asset::factory()->assignedToUser($from)->create(['company_id' => $companyA->id]);

    transferAssetsTool(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

    expect((int) $asset->fresh()->assigned_to)->toBe($from->id, 'FMCS cross-company transfer must leave the asset with the source user.');
});

test('rejects cross company accessory transfer under fmcs', function () {
    // Accessory branch of the tool was reassigning the
    // AccessoryCheckout row directly, with no FMCS guard. Per-
    // type test (no asset or license fixtures) so the
    // transaction-wide rollback from an asset-guard failure
    // doesn't mask a missing accessory guard.
    [$from, $to, $operator] = fmcsFixture($this->settings, $companyA, $companyB);
    $this->actingAs($operator);

    $accessory = Accessory::factory()->for($companyA, 'company')->create();
    $checkout = AccessoryCheckout::factory()->create([
        'accessory_id' => $accessory->id,
        'assigned_type' => User::class,
        'assigned_to' => $from->id,
    ]);

    transferAssetsTool(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

    expect((int) $checkout->fresh()->assigned_to)->toBe($from->id, 'FMCS cross-company transfer must leave the accessory checkout with the source user.');
});

test('rejects cross company license seat transfer under fmcs', function () {
    // License-seat branch had the same missing guard: the seat's
    // assigned_to was being rewritten directly without checking
    // whether the license's company allowed the target user.
    [$from, $to, $operator] = fmcsFixture($this->settings, $companyA, $companyB);
    $this->actingAs($operator);

    $license = License::factory()->for($companyA, 'company')->create();
    $seat = LicenseSeat::factory()->create([
        'license_id' => $license->id,
        'assigned_to' => $from->id,
    ]);

    transferAssetsTool(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

    expect((int) $seat->fresh()->assigned_to)->toBe($from->id, 'FMCS cross-company transfer must leave the license seat with the source user.');
});

/**
 * Shared fixture for the per-type FMCS tests. Enables FMCS,
 * builds two companies, a source user in A, a target user in
 * only B, and a non-superuser operator with the per-item
 * checkin / checkout permissions (superusers bypass FMCS by
 * design so canCheckoutTo would always allow them).
 *
 * @return array{0: User, 1: User, 2: User}
 */
function fmcsFixture(Settings $settings, ?Company &$companyA, ?Company &$companyB): array
{
    $settings->enableMultipleFullCompanySupport();
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $from = User::factory()->forCompany($companyA)->create(['username' => 'leaver']);
    $from->companies()->sync([$companyA->id]);
    $to = User::factory()->forCompany($companyB)->create(['username' => 'receiver']);
    $to->companies()->sync([$companyB->id]);

    $operator = User::factory()
        ->forCompany($companyA)
        ->checkinAssets()
        ->checkoutAssets()
        ->checkoutAccessories()
        ->checkoutLicenses()
        ->create();
    $operator->companies()->sync([$companyA->id, $companyB->id]);

    return [$from, $to, $operator];
}

test('rejects same user transfer', function () {
    User::factory()->create(['username' => 'solo']);

    $response = transferAssetsTool(['from_username' => 'solo', 'to_username' => 'solo']);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('returns ambiguous match set when from name resolves to multiple', function () {
    User::factory()->count(2)->create(['first_name' => 'John', 'last_name' => 'Doe']);
    User::factory()->create(['username' => 'receiver']);

    $content = transferAssetsTool([
        'from_first_name' => 'John',
        'from_last_name' => 'Doe',
        'to_username' => 'receiver',
    ])->getStructuredContent();

    expect($content['success'])->toBeFalse();
    expect($content['error'])->toBe('ambiguous_user');
    expect($content['side'])->toBe('from');
});

test('returns ambiguous match set when to name resolves to multiple', function () {
    User::factory()->create(['username' => 'source']);
    User::factory()->count(2)->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

    $content = transferAssetsTool([
        'from_username' => 'source',
        'to_first_name' => 'Jane',
        'to_last_name' => 'Smith',
    ])->getStructuredContent();

    expect($content['success'])->toBeFalse();
    expect($content['side'])->toBe('to');
});

test('returns error when from user not found', function () {
    User::factory()->create(['username' => 'receiver']);

    $response = transferAssetsTool(['from_username' => 'nobody', 'to_username' => 'receiver']);

    expect($response->responses()->first()->isError())->toBeTrue();
});

test('preview captures the before state on both sides', function () {
    $from = User::factory()->create(['username' => 'leaver']);
    $to = User::factory()->create(['username' => 'receiver']);

    $content = transferAssetsTool(['from_username' => 'leaver', 'to_username' => 'receiver'])
        ->getStructuredContent();

    expect($content['preview']['from']['id'])->toBe($from->id);
    expect($content['preview']['to']['id'])->toBe($to->id);
});
