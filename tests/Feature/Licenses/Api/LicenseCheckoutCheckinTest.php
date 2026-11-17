<?php

namespace Tests\Feature\Licenses\Api;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LicenseCheckoutCheckinTest extends TestCase
{
    // ---------------------------------------------------------------------------
    // Checkout
    // ---------------------------------------------------------------------------

    #[Test]
    public function checkout_requires_checkout_permission(): void
    {
        $license = License::factory()->create(['seats' => 1]);

        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => User::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function checkout_to_user_assigns_free_seat(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 1]);
        $target = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => $target->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $seat = $license->licenseseats()->first();
        $this->assertEquals($target->id, $seat->assigned_to);

        Event::assertDispatched(CheckoutableCheckedOut::class);
    }

    #[Test]
    public function checkout_to_asset_assigns_free_seat(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 1]);
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'asset',
                'asset_id' => $asset->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $seat = $license->licenseseats()->first();
        $this->assertEquals($asset->id, $seat->asset_id);

        Event::assertDispatched(CheckoutableCheckedOut::class);
    }

    #[Test]
    public function checkout_to_specific_seat_by_id(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 3]);
        $seats = $license->licenseseats()->orderBy('id')->get();
        $target = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'seat_id' => $seats[1]->id,
                'target_type' => 'user',
                'assigned_to' => $target->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertEquals($target->id, $seats[1]->fresh()->assigned_to);
        $this->assertNull($seats[0]->fresh()->assigned_to);
        $this->assertNull($seats[2]->fresh()->assigned_to);

        Event::assertDispatched(CheckoutableCheckedOut::class);
    }

    #[Test]
    public function checkout_fails_when_no_seats_available(): void
    {
        $license = License::factory()->create(['seats' => 1]);
        LicenseSeat::where('license_id', $license->id)->update(['assigned_to' => User::factory()->create()->id]);

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => User::factory()->create()->id,
            ])
            ->assertJson(['status' => 'error']);
    }

    #[Test]
    public function checkout_returns_error_for_nonexistent_user(): void
    {
        $license = License::factory()->create(['seats' => 1]);

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => 99999,
            ])
            ->assertJson(['status' => 'error']);
    }

    #[Test]
    public function checkout_returns_error_for_nonexistent_asset(): void
    {
        $license = License::factory()->create(['seats' => 1]);

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'asset',
                'asset_id' => 99999,
            ])
            ->assertJson(['status' => 'error']);
    }

    #[Test]
    public function sequential_checkouts_each_receive_a_distinct_seat(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 2]);
        $actor = User::factory()->checkoutLicenses()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => $user1->id,
            ])
            ->assertJson(['status' => 'success']);

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => $user2->id,
            ])
            ->assertJson(['status' => 'success']);

        $assignedTo = $license->licenseseats()->pluck('assigned_to');
        $this->assertCount(2, $assignedTo->filter());
        $this->assertEquals(2, $assignedTo->unique()->count());

        Event::assertDispatched(CheckoutableCheckedOut::class, 2);
    }

    // ---------------------------------------------------------------------------
    // Checkin
    // ---------------------------------------------------------------------------

    #[Test]
    public function checkin_requires_checkin_permission(): void
    {
        $license = License::factory()->create(['seats' => 1]);
        $seat = $license->licenseseats()->first();
        $seat->update(['assigned_to' => User::factory()->create()->id]);

        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function checkin_clears_assigned_user(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
        $user = User::factory()->create();
        $seat = $license->licenseseats()->first();
        $seat->update(['assigned_to' => $user->id]);

        $this->actingAsForApi(User::factory()->checkinLicenses()->create())
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNull($seat->fresh()->assigned_to);
        $this->assertFalse((bool) $seat->fresh()->unreassignable_seat);

        Event::assertDispatched(CheckoutableCheckedIn::class);
    }

    #[Test]
    public function checkin_clears_assigned_asset(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
        $asset = Asset::factory()->create();
        $seat = $license->licenseseats()->first();
        $seat->update(['asset_id' => $asset->id]);

        $this->actingAsForApi(User::factory()->checkinLicenses()->create())
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNull($seat->fresh()->asset_id);

        Event::assertDispatched(CheckoutableCheckedIn::class);
    }

    #[Test]
    public function checkin_marks_seat_unreassignable_when_license_is_not_reassignable(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 1, 'reassignable' => false]);
        $user = User::factory()->create();
        $seat = $license->licenseseats()->first();
        $seat->update(['assigned_to' => $user->id]);

        $this->actingAsForApi(User::factory()->checkinLicenses()->create())
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNull($seat->fresh()->assigned_to);
        $this->assertTrue((bool) $seat->fresh()->unreassignable_seat);

        Event::assertDispatched(CheckoutableCheckedIn::class);
    }

    #[Test]
    public function checkin_returns_error_for_unassigned_seat(): void
    {
        $license = License::factory()->create(['seats' => 1]);
        $seat = $license->licenseseats()->first();

        $this->actingAsForApi(User::factory()->checkinLicenses()->create())
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertJson(['status' => 'error']);
    }

    #[Test]
    public function checkin_returns_error_for_seat_not_belonging_to_license(): void
    {
        $license1 = License::factory()->create(['seats' => 1]);
        $license2 = License::factory()->create(['seats' => 1]);
        $seat2 = $license2->licenseseats()->first();
        $seat2->update(['assigned_to' => User::factory()->create()->id]);

        $this->actingAsForApi(User::factory()->checkinLicenses()->create())
            ->postJson(route('api.licenses.checkin', $license1->id), [
                'seat_id' => $seat2->id,
            ])
            ->assertJson(['status' => 'error']);
    }

    #[Test]
    public function checkout_then_checkin_frees_the_seat(): void
    {
        Event::fake([CheckoutableCheckedOut::class, CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
        $user = User::factory()->create();
        $actor = User::factory()->checkoutLicenses()->checkinLicenses()->create();

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkout', $license->id), [
                'target_type' => 'user',
                'assigned_to' => $user->id,
            ])
            ->assertJson(['status' => 'success']);

        $seat = $license->licenseseats()->first();
        $this->assertEquals($user->id, $seat->fresh()->assigned_to);

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkin', $license->id), [
                'seat_id' => $seat->id,
            ])
            ->assertJson(['status' => 'success']);

        $this->assertNull($seat->fresh()->assigned_to);

        Event::assertDispatched(CheckoutableCheckedOut::class);
        Event::assertDispatched(CheckoutableCheckedIn::class);
    }

    // ---------------------------------------------------------------------------
    // GHSA-r25g-f428-466r regression coverage.
    //
    // Explicit-seat-id checkouts must reject retired-unreassignable seats and
    // must reject already-occupied seats by default. Occupied-seat checkout is
    // only allowed when the caller opts in with `reassign: true`, in which
    // case the API does a proper checkin-then-checkout (fires
    // CheckoutableCheckedIn for the displaced holder, deletes their pending
    // acceptance, then fires CheckoutableCheckedOut for the new target).
    // Non-reassignable licenses reject even with the flag set.
    // ---------------------------------------------------------------------------

    #[Test]
    public function checkout_by_explicit_seat_id_rejects_retired_unreassignable_seat(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        // Non-reassignable license: checking a seat back in retires it by
        // flipping unreassignable_seat=1 and leaving assigned_to null.
        $license = License::factory()->create(['seats' => 2, 'reassignable' => 0]);
        $seats = $license->licenseseats()->orderBy('id')->get();
        $retiredSeat = $seats[0];
        // unreassignable_seat is not fillable, so ->update() would silently
        // skip it. Direct property assignment + save persists it.
        $retiredSeat->unreassignable_seat = true;
        $retiredSeat->assigned_to = null;
        $retiredSeat->save();

        $target = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'seat_id' => $retiredSeat->id,
                'target_type' => 'user',
                'assigned_to' => $target->id,
            ])
            ->assertJson(['status' => 'error']);

        $this->assertNull($retiredSeat->fresh()->assigned_to);
        $this->assertTrue((bool) $retiredSeat->fresh()->unreassignable_seat);

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
    }

    #[Test]
    public function checkout_by_explicit_seat_id_rejects_seat_already_assigned_to_user_by_default(): void
    {
        Event::fake([CheckoutableCheckedOut::class, CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 2]);
        $seats = $license->licenseseats()->orderBy('id')->get();

        $originalHolder = User::factory()->create();
        $seats[0]->update(['assigned_to' => $originalHolder->id]);

        $newTarget = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'seat_id' => $seats[0]->id,
                'target_type' => 'user',
                'assigned_to' => $newTarget->id,
            ])
            ->assertJson(['status' => 'error']);

        $this->assertEquals(
            $originalHolder->id,
            $seats[0]->fresh()->assigned_to,
            'Default checkout must not silently displace the current holder.',
        );

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
        Event::assertNotDispatched(CheckoutableCheckedIn::class);
    }

    #[Test]
    public function checkout_by_explicit_seat_id_rejects_seat_already_assigned_to_asset_by_default(): void
    {
        Event::fake([CheckoutableCheckedOut::class]);

        $license = License::factory()->create(['seats' => 2]);
        $seats = $license->licenseseats()->orderBy('id')->get();

        $asset = Asset::factory()->create();
        $seats[0]->update(['asset_id' => $asset->id]);

        $newTarget = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'seat_id' => $seats[0]->id,
                'target_type' => 'user',
                'assigned_to' => $newTarget->id,
            ])
            ->assertJson(['status' => 'error']);

        $this->assertEquals(
            $asset->id,
            $seats[0]->fresh()->asset_id,
            'A seat already assigned to an asset must not be silently reassigned to a user.',
        );

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
    }

    #[Test]
    public function checkout_with_reassign_flag_reassigns_occupied_seat_and_fires_both_events(): void
    {
        Event::fake([CheckoutableCheckedOut::class, CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 2, 'reassignable' => 1]);
        $seats = $license->licenseseats()->orderBy('id')->get();

        $originalHolder = User::factory()->create();
        $seats[0]->update(['assigned_to' => $originalHolder->id]);

        $newTarget = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'seat_id' => $seats[0]->id,
                'target_type' => 'user',
                'assigned_to' => $newTarget->id,
                'reassign' => true,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertEquals(
            $newTarget->id,
            $seats[0]->fresh()->assigned_to,
            'With reassign flag, the seat must transfer to the new target.',
        );

        Event::assertDispatched(
            CheckoutableCheckedIn::class,
            fn (CheckoutableCheckedIn $event) => $event->checkedOutTo->is($originalHolder),
        );
        Event::assertDispatched(
            CheckoutableCheckedOut::class,
            fn (CheckoutableCheckedOut $event) => $event->checkedOutTo->is($newTarget),
        );
    }

    #[Test]
    public function checkout_with_reassign_flag_rejects_when_license_is_non_reassignable(): void
    {
        Event::fake([CheckoutableCheckedOut::class, CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 2, 'reassignable' => 0]);
        $seats = $license->licenseseats()->orderBy('id')->get();

        $originalHolder = User::factory()->create();
        $seats[0]->update(['assigned_to' => $originalHolder->id]);

        $newTarget = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'seat_id' => $seats[0]->id,
                'target_type' => 'user',
                'assigned_to' => $newTarget->id,
                'reassign' => true,
            ])
            ->assertJson(['status' => 'error']);

        $this->assertEquals(
            $originalHolder->id,
            $seats[0]->fresh()->assigned_to,
            'Non-reassignable licenses must reject the reassign flag entirely.',
        );

        Event::assertNotDispatched(CheckoutableCheckedOut::class);
        Event::assertNotDispatched(CheckoutableCheckedIn::class);
    }

    #[Test]
    public function checkout_with_reassign_flag_deletes_pending_acceptance_for_displaced_holder(): void
    {
        $license = License::factory()->create(['seats' => 2, 'reassignable' => 1]);
        $seats = $license->licenseseats()->orderBy('id')->get();

        $originalHolder = User::factory()->create();
        $seats[0]->update(['assigned_to' => $originalHolder->id]);

        // Seed a pending acceptance for the original holder against this
        // seat. The reassign flow must delete it as part of the displacement.
        // Direct property assignment because these columns are not in the
        // model's fillable list. The acceptance table only records
        // assigned_to_id (user-scoped), so no type discriminator here.
        $pendingAcceptance = new CheckoutAcceptance;
        $pendingAcceptance->checkoutable_type = LicenseSeat::class;
        $pendingAcceptance->checkoutable_id = $seats[0]->id;
        $pendingAcceptance->assigned_to_id = $originalHolder->id;
        $pendingAcceptance->save();

        $newTarget = User::factory()->create();

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'seat_id' => $seats[0]->id,
                'target_type' => 'user',
                'assigned_to' => $newTarget->id,
                'reassign' => true,
            ])
            ->assertOk();

        $this->assertNull(
            CheckoutAcceptance::find($pendingAcceptance->id),
            'Reassign must delete the displaced holder\'s pending acceptance row so the audit trail does not carry a stale acceptance for an item they no longer hold.',
        );
    }

    // GHSA-73jx-c32h-c3w9: failed reassignment used to displace the current
    // holder anyway because the transaction committed on a plain `return`
    // from inside its closure. All fallible validation now runs before any
    // mutation.

    #[Test]
    public function reassign_to_nonexistent_user_does_not_displace_current_holder(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
        $holder = User::factory()->create();
        $seat = $license->licenseseats()->first();
        $seat->update(['assigned_to' => $holder->id]);

        $pendingAcceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
            'assigned_to_id' => $holder->id,
        ]);

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'seat_id' => $seat->id,
                'target_type' => 'user',
                'assigned_to' => 999999,
                'reassign' => true,
            ])
            ->assertJson(['status' => 'error']);

        $this->assertSame(
            $holder->id,
            (int) $seat->fresh()->assigned_to,
            'Holder must remain assigned when the replacement target does not exist.',
        );
        $this->assertNotNull(
            CheckoutAcceptance::find($pendingAcceptance->id),
            'Pending acceptance must survive a failed reassignment.',
        );
        Event::assertNotDispatched(CheckoutableCheckedIn::class);
    }

    #[Test]
    public function reassign_to_cross_company_user_does_not_displace_current_holder_under_fmcs(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        [$companyA, $companyB] = \App\Models\Company::factory()->count(2)->create();
        $license = License::factory()->create([
            'seats' => 1,
            'reassignable' => true,
            'company_id' => $companyA->id,
        ]);
        $holder = $companyA->users()->save(User::factory()->make());
        $seat = $license->licenseseats()->first();
        $seat->update(['assigned_to' => $holder->id]);

        // Enable FMCS AFTER setup so the CompanyableChildTrait scope does not
        // hide the seat from the fixture lookups above.
        $this->settings->enableMultipleFullCompanySupport();

        $pendingAcceptance = CheckoutAcceptance::factory()->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $seat->id,
            'assigned_to_id' => $holder->id,
        ]);

        $crossCompanyTarget = $companyB->users()->save(User::factory()->make());
        $actor = $companyA->users()->save(User::factory()->checkoutLicenses()->make());

        $this->actingAsForApi($actor)
            ->postJson(route('api.licenses.checkout', $license->id), [
                'seat_id' => $seat->id,
                'target_type' => 'user',
                'assigned_to' => $crossCompanyTarget->id,
                'reassign' => true,
            ])
            ->assertJson(['status' => 'error']);

        $this->assertSame(
            $holder->id,
            (int) $seat->fresh()->assigned_to,
            'Holder must remain assigned when the FMCS company check rejects the replacement target.',
        );
        $this->assertNotNull(
            CheckoutAcceptance::find($pendingAcceptance->id),
            'Pending acceptance must survive a failed reassignment under FMCS.',
        );
        Event::assertNotDispatched(CheckoutableCheckedIn::class);
    }

    #[Test]
    public function reassign_to_nonexistent_asset_does_not_displace_current_holder(): void
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $license = License::factory()->create(['seats' => 1, 'reassignable' => true]);
        $holder = User::factory()->create();
        $seat = $license->licenseseats()->first();
        $seat->update(['assigned_to' => $holder->id]);

        $this->actingAsForApi(User::factory()->checkoutLicenses()->create())
            ->postJson(route('api.licenses.checkout', $license->id), [
                'seat_id' => $seat->id,
                'target_type' => 'asset',
                'asset_id' => 999999,
                'reassign' => true,
            ])
            ->assertJson(['status' => 'error']);

        $this->assertSame(
            $holder->id,
            (int) $seat->fresh()->assigned_to,
            'Holder must remain assigned when the replacement asset does not exist.',
        );
        Event::assertNotDispatched(CheckoutableCheckedIn::class);
    }
}
