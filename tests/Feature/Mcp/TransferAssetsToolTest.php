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
        $license = License::factory()->create(['reassignable' => true]);
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

        $license = License::factory()->for($companyA, 'company')->create(['reassignable' => true]);
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
            ->viewUsers()
            ->checkinAssets()
            ->checkoutAssets()
            ->checkinAccessories()
            ->checkoutAccessories()
            ->checkinLicenses()
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

    public function test_actor_without_view_on_source_user_is_refused(): void
    {
        // GHSA-6x3x. The tool read the source user's items with no view
        // check, letting a caller probe another user's assets, accessories
        // and license seats through the preview. Mirror the web transfer's
        // view guard on the source user.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $from = User::factory()->forCompany($companyA)->create(['username' => 'leaver']);
        $to = User::factory()->forCompany($companyB)->create(['username' => 'receiver']);
        $actor = User::factory()
            ->forCompany($companyB)
            ->viewUsers()
            ->checkinAssets()
            ->checkoutAssets()
            ->checkinAccessories()
            ->checkoutAccessories()
            ->create();
        $actor->companies()->sync([$companyB->id]);
        $this->actingAs($actor);

        $response = $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver']);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_checkout_only_actor_cannot_transfer_accessory(): void
    {
        // GHSA-6x3x. Prior behavior required only accessories.checkout to
        // move an accessory from one user to another. The web transfer and
        // REST accessory check-in require accessories.checkin for that
        // write. This test pins the MCP tool to the checkin + checkout
        // requirement.
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $accessory = Accessory::factory()->create();
        $checkout = AccessoryCheckout::factory()->create([
            'accessory_id' => $accessory->id,
            'assigned_type' => User::class,
            'assigned_to' => $from->id,
        ]);

        $this->actingAs(User::factory()
            ->viewUsers()
            ->checkoutAccessories()
            ->create());

        $response = $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertSame($from->id, (int) $checkout->fresh()->assigned_to);
    }

    public function test_checkout_only_actor_cannot_transfer_license_seat(): void
    {
        // Same shape as the accessory test for license seats.
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $license = License::factory()->create(['reassignable' => true]);
        $seat = LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'assigned_to' => $from->id,
        ]);

        $this->actingAs(User::factory()
            ->viewUsers()
            ->checkoutLicenses()
            ->create());

        $response = $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertSame($from->id, (int) $seat->fresh()->assigned_to);
    }

    public function test_accessory_transfer_fires_checkin_and_checkout_events(): void
    {
        // GHSA-6x3x. Prior behavior re-assigned the AccessoryCheckout row
        // directly with no events, so the move left no entry in the
        // activity log. The web transfer fires both CheckoutableCheckedIn
        // and CheckoutableCheckedOut. Pin the MCP tool to the same.
        \Illuminate\Support\Facades\Event::fake([
            \App\Events\CheckoutableCheckedIn::class,
            \App\Events\CheckoutableCheckedOut::class,
        ]);

        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $accessory = Accessory::factory()->create();
        AccessoryCheckout::factory()->create([
            'accessory_id' => $accessory->id,
            'assigned_type' => User::class,
            'assigned_to' => $from->id,
        ]);

        $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\CheckoutableCheckedIn::class);
        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\CheckoutableCheckedOut::class);
    }

    public function test_license_seat_transfer_fires_checkin_and_checkout_events(): void
    {
        \Illuminate\Support\Facades\Event::fake([
            \App\Events\CheckoutableCheckedIn::class,
            \App\Events\CheckoutableCheckedOut::class,
        ]);

        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $license = License::factory()->create(['reassignable' => true]);
        LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'assigned_to' => $from->id,
        ]);

        $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false]);

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\CheckoutableCheckedIn::class);
        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\CheckoutableCheckedOut::class);
    }

    public function test_non_reassignable_seats_without_decision_returns_a_prompt_and_makes_no_changes(): void
    {
        // Executing with non-reassignable seats present and no
        // checkin_non_reassignable_seats arg returns a prompt-style
        // response so the caller can decide check-in vs leave-with-source.
        // No DB writes happen until the caller makes a choice.
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $nonReassignable = License::factory()->create(['reassignable' => false]);
        $frozen = LicenseSeat::factory()->create([
            'license_id' => $nonReassignable->id,
            'assigned_to' => $from->id,
        ]);
        $reassignable = License::factory()->create(['reassignable' => true]);
        $movable = LicenseSeat::factory()->create([
            'license_id' => $reassignable->id,
            'assigned_to' => $from->id,
        ]);

        $content = $this->handle(['from_username' => 'leaver', 'to_username' => 'receiver', 'dry_run' => false])
            ->getStructuredContent();

        $this->assertFalse($content['success']);
        $this->assertSame('checkin_non_reassignable_seats', $content['requires_decision']);
        $this->assertCount(1, $content['non_reassignable_license_seats']);
        $this->assertSame($from->id, (int) $frozen->fresh()->assigned_to);
        $this->assertSame($from->id, (int) $movable->fresh()->assigned_to, 'No seat moves until the caller resolves the non-reassignable decision.');
    }

    public function test_non_reassignable_seats_checked_in_when_caller_opts_in(): void
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $nonReassignable = License::factory()->create(['reassignable' => false]);
        $frozen = LicenseSeat::factory()->create([
            'license_id' => $nonReassignable->id,
            'assigned_to' => $from->id,
        ]);
        $reassignable = License::factory()->create(['reassignable' => true]);
        $movable = LicenseSeat::factory()->create([
            'license_id' => $reassignable->id,
            'assigned_to' => $from->id,
        ]);

        $this->handle([
            'from_username' => 'leaver',
            'to_username' => 'receiver',
            'dry_run' => false,
            'checkin_non_reassignable_seats' => true,
        ]);

        $this->assertSame($to->id, (int) $movable->fresh()->assigned_to);
        $this->assertNull($frozen->fresh()->assigned_to, 'Non-reassignable seat should be checked in (assigned_to cleared).');
        $this->assertTrue((bool) $frozen->fresh()->unreassignable_seat, 'Checked-in non-reassignable seat must have unreassignable_seat=true so free-seat queries stop offering it.');
    }

    public function test_non_reassignable_seats_left_with_source_when_caller_opts_out(): void
    {
        $from = User::factory()->create(['username' => 'leaver']);
        $to = User::factory()->create(['username' => 'receiver']);
        $nonReassignable = License::factory()->create(['reassignable' => false]);
        $frozen = LicenseSeat::factory()->create([
            'license_id' => $nonReassignable->id,
            'assigned_to' => $from->id,
        ]);
        $reassignable = License::factory()->create(['reassignable' => true]);
        $movable = LicenseSeat::factory()->create([
            'license_id' => $reassignable->id,
            'assigned_to' => $from->id,
        ]);

        $this->handle([
            'from_username' => 'leaver',
            'to_username' => 'receiver',
            'dry_run' => false,
            'checkin_non_reassignable_seats' => false,
        ]);

        $this->assertSame($to->id, (int) $movable->fresh()->assigned_to);
        $this->assertSame($from->id, (int) $frozen->fresh()->assigned_to, 'Non-reassignable seat should stay with the source when the caller opts out.');
        $this->assertFalse((bool) $frozen->fresh()->unreassignable_seat, 'Seat left with source should not be flagged as unreassignable.');
    }
}
