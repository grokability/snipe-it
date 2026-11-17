<?php

namespace Tests\Feature\Mcp;

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
use Tests\TestCase;

class TransferAssetsToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return resolve(TransferAssetsTool::class)->handle(new Request($args));
    }

    public function test_dry_run_returns_preview_without_moving_anything()
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $asset = Asset::factory()->assignedToUser($from)->create();

        $content = $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver'])
            ->getStructuredContent();

        $this->assertTrue($content['dry_run']);
        $this->assertCount(1, $content['preview']['assets_to_transfer']);
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'assigned_to' => $from->id]);
    }

    public function test_dry_run_is_default()
    {
        User::factory()->create(['username' => 'a']);
        User::factory()->create(['username' => 'b']);

        $content = $this->handle(['from_username' => 'a', 'to_username' => 'b'])->getStructuredContent();

        $this->assertTrue($content['dry_run']);
    }

    public function test_execute_moves_assets()
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $asset = Asset::factory()->assignedToUser($from)->create();

        $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        $fresh = $asset->fresh();
        $this->assertSame($to->id, $fresh->assigned_to);
    }

    public function test_execute_moves_license_seats()
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $license = License::factory()->create();
        $seat = LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'assigned_to' => $from->id,
        ]);

        $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        $this->assertSame($to->id, $seat->fresh()->assigned_to);
    }

    public function test_execute_moves_accessory_checkouts()
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $accessory = Accessory::factory()->create();
        $checkout = AccessoryCheckout::factory()->create([
            'accessory_id' => $accessory->id,
            'assigned_type' => User::class,
            'assigned_to' => $from->id,
        ]);

        $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        $this->assertSame($to->id, $checkout->fresh()->assigned_to);
    }

    public function test_rejects_cross_company_asset_transfer_under_fmcs()
    {
        // Same bug class as the asset-model bulk-fulfill route a
        // reporter filed: the per-asset checkOut() needs a
        // canCheckoutTo() guard under FMCS strict mode so a
        // company-A asset doesn't land on a user who only belongs
        // to company B.
        [$from, $to, $operator] = $this->fmcsFixture($companyA, $companyB);
        $this->actingAs($operator);

        $asset = Asset::factory()->assignedToUser($from)->create(['company_id' => $companyA->id]);

        $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        $this->assertSame($from->id, (int) $asset->fresh()->assigned_to, 'FMCS cross-company transfer must leave the asset with the source user.');
    }

    public function test_rejects_cross_company_accessory_transfer_under_fmcs()
    {
        // Accessory branch of the tool was reassigning the
        // AccessoryCheckout row directly, with no FMCS guard. Per-
        // type test (no asset or license fixtures) so the
        // transaction-wide rollback from an asset-guard failure
        // doesn't mask a missing accessory guard.
        [$from, $to, $operator] = $this->fmcsFixture($companyA, $companyB);
        $this->actingAs($operator);

        $accessory = Accessory::factory()->for($companyA, 'company')->create();
        $checkout = AccessoryCheckout::factory()->create([
            'accessory_id' => $accessory->id,
            'assigned_type' => User::class,
            'assigned_to' => $from->id,
        ]);

        $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        $this->assertSame($from->id, (int) $checkout->fresh()->assigned_to, 'FMCS cross-company transfer must leave the accessory checkout with the source user.');
    }

    public function test_rejects_cross_company_license_seat_transfer_under_fmcs()
    {
        // License-seat branch had the same missing guard: the seat's
        // assigned_to was being rewritten directly without checking
        // whether the license's company allowed the target user.
        [$from, $to, $operator] = $this->fmcsFixture($companyA, $companyB);
        $this->actingAs($operator);

        $license = License::factory()->for($companyA, 'company')->create();
        $seat = LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'assigned_to' => $from->id,
        ]);

        $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        $this->assertSame($from->id, (int) $seat->fresh()->assigned_to, 'FMCS cross-company transfer must leave the license seat with the source user.');
    }

    /**
     * Shared fixture for the per-type FMCS tests. Enables FMCS,
     * builds two companies, a source user in A, a target user in
     * only B, and a non-superuser operator with the per-item
     * checkin / checkout permissions (superusers bypass FMCS by
     * design so canCheckoutTo would always allow them).
     *
     * @return array{0: User, 1: User, 2: User}
     */
    private function fmcsFixture(?Company &$companyA, ?Company &$companyB): array
    {
        $this->settings->enableMultipleFullCompanySupport();
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

    public function test_rejects_same_user_transfer()
    {
        User::factory()->create(['username' => 'solo']);

        $response = $this->handle(['from_username' => 'solo', 'to_username' => 'solo']);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_returns_ambiguous_match_set_when_from_name_resolves_to_multiple()
    {
        User::factory()->count(2)->create(['first_name' => 'John', 'last_name' => 'Doe']);
        User::factory()->create(['username' => 'receiver']);

        $content = $this->handle([
            'from_first_name' => 'John',
            'from_last_name' => 'Doe',
            'to_username' => 'receiver',
        ])->getStructuredContent();

        $this->assertFalse($content['success']);
        $this->assertSame('ambiguous_user', $content['error']);
        $this->assertSame('from', $content['side']);
    }

    public function test_returns_ambiguous_match_set_when_to_name_resolves_to_multiple()
    {
        User::factory()->create(['username' => 'source']);
        User::factory()->count(2)->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

        $content = $this->handle([
            'from_username' => 'source',
            'to_first_name' => 'Jane',
            'to_last_name' => 'Smith',
        ])->getStructuredContent();

        $this->assertFalse($content['success']);
        $this->assertSame('to', $content['side']);
    }

    public function test_returns_error_when_from_user_not_found()
    {
        User::factory()->create(['username' => 'receiver']);

        $response = $this->handle(['from_username' => 'nobody', 'to_username' => 'receiver']);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_preview_captures_the_before_state_on_both_sides()
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);

        $content = $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver'])
            ->getStructuredContent();

        $this->assertSame($from->id, $content['preview']['from']['id']);
        $this->assertSame($to->id, $content['preview']['to']['id']);
    }
}
