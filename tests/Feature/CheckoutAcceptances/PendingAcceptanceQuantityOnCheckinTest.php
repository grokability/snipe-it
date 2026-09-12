<?php

namespace Tests\Feature\CheckoutAcceptances;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CheckoutAcceptance;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * After checking an item in, the holder should be left owing an acceptance for
 * exactly the units they still hold and have not already accepted. No more, or
 * they are prompted to accept units they gave back. No fewer, or a unit they
 * still hold has no acceptance record and they are never prompted at all.
 *
 * Written as an assertion, that is:
 *
 *     SUM(pending qty) == max(0, units held - units accepted)
 *
 * The subtraction only matters once something has been accepted, so most tests
 * below are really just "pending qty == units held".
 *
 * Why this is not trivially true: two tables look 1:1 and are not.
 * accessories_checkout holds one row per UNIT, while checkout_acceptances holds
 * one row per CHECKOUT ACTION carrying its qty — so checking out 3 gives three
 * pivot rows but a single acceptance row. Checking one unit back in therefore
 * has to retire one unit, not a row that may be worth three.
 *
 * Accessory units are fungible — no serial, no tag — so there is no fact about
 * WHICH unit came back. A checkin is therefore DEFINED to retire an unaccepted
 * unit, oldest acceptance first, which is what makes the assertion above
 * well-defined at all.
 *
 * Acceptance is all-or-nothing per row, so a qty-3 row cannot be partially
 * accepted and mixed accepted/unaccepted states only arise from separate
 * checkouts.
 */
class PendingAcceptanceQuantityOnCheckinTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();
    }

    // ─────────────────────────────────────────────────────────────────────
    // ACCESSORIES — the only checkin-able quantity type
    // ─────────────────────────────────────────────────────────────────────

    #[Test]
    public function checking_in_the_only_unit_clears_the_acceptance(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 1);

        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));

        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
    }

    #[Test]
    public function checking_in_one_of_two_single_unit_checkouts_leaves_the_other_outstanding(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 1);
        $this->checkOut($accessory, $user, qty: 1);

        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));

        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
    }

    #[Test]
    public function checking_in_one_unit_of_a_two_unit_checkout_decrements_that_acceptance(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 2);

        // One row worth 2 units: no delete-a-row strategy can be right here.
        $this->assertSame(1, $this->pendingRowCount($accessory, $user));

        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));

        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
    }

    #[Test]
    public function checking_in_one_unit_across_two_checkouts_of_different_sizes(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 2);
        $this->checkOut($accessory, $user, qty: 1);

        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));

        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
    }

    #[Test]
    public function checking_in_every_unit_of_a_multi_unit_checkout_clears_the_acceptance(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 2);

        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));
        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));

        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
    }

    #[Test]
    public function retiring_more_units_than_the_oldest_acceptance_holds_spills_into_the_next(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 3);
        $this->checkOut($accessory, $user, qty: 2);

        // Retiring four of five units has to exhaust the first row and bite
        // into the second, which "one row per checkin" cannot do.
        for ($i = 0; $i < 4; $i++) {
            $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));
        }

        $this->assertSame(1, $this->unitsHeld($accessory, $user));
        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
    }

    #[Test]
    public function checking_in_a_location_held_unit_leaves_a_users_acceptance_alone(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 2);

        // Acceptances only ever belong to users, so assigned_to_id holds a user
        // id. An accessory can be held by a user and a location at once, so a
        // location id equal to a user id must not match that user's rows.
        $location = $this->locationWithIdCollidingWith($user);

        $locationCheckout = AccessoryCheckout::create([
            'accessory_id' => $accessory->id,
            'assigned_to' => $location->id,
            'assigned_type' => Location::class,
            'created_by' => User::factory()->create()->id,
        ]);

        $this->checkIn($locationCheckout->id);

        $this->assertSame(2, $this->unitsHeld($accessory, $user), 'The user\'s own units were not touched.');
        $this->assertSame(2, $this->pendingQtySum($accessory, $user),
            'Checking in a location-held unit cleared the user\'s acceptance by id collision.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // MIXED accepted / pending — accepted rows are history and stay untouched
    // ─────────────────────────────────────────────────────────────────────

    #[Test]
    public function checking_in_when_one_of_three_single_unit_checkouts_is_already_accepted(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 1);
        $this->checkOut($accessory, $user, qty: 1);
        $this->checkOut($accessory, $user, qty: 1);

        $accepted = $this->acceptPendingRow($accessory, $user, index: 1);

        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));

        $this->assertSame(2, $this->unitsHeld($accessory, $user));
        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
        $this->assertAcceptedRowUntouched($accepted, expectedQty: 1);
    }

    #[Test]
    public function checking_in_when_a_middle_sized_checkout_is_already_accepted(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 2);
        $this->checkOut($accessory, $user, qty: 1);
        $this->checkOut($accessory, $user, qty: 3);

        // Rows of differing size, so nothing passes by conflating rows/units.
        $this->assertSame(6, $this->unitsHeld($accessory, $user));

        $accepted = $this->acceptPendingRow($accessory, $user, index: 1);

        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));

        $this->assertSame(5, $this->unitsHeld($accessory, $user));
        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
        $this->assertAcceptedRowUntouched($accepted, expectedQty: 1);
    }

    #[Test]
    public function checking_in_two_units_when_the_largest_checkout_is_already_accepted(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 2);
        $this->checkOut($accessory, $user, qty: 1);
        $this->checkOut($accessory, $user, qty: 3);

        $accepted = $this->acceptPendingRow($accessory, $user, index: 2);

        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));
        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));

        $this->assertSame(4, $this->unitsHeld($accessory, $user));
        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
        $this->assertAcceptedRowUntouched($accepted, expectedQty: 3);
    }

    #[Test]
    public function checking_in_against_a_fully_accepted_checkout_leaves_nothing_to_retire(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 3);

        $accepted = $this->acceptPendingRow($accessory, $user, index: 0);
        $this->assertSame(0, $this->pendingQtySum($accessory, $user), 'Accepting the only row should leave nothing pending.');

        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));

        // Nothing outstanding, so nothing to retire. The accepted row must not
        // be decremented to balance the books.
        $this->assertSame(2, $this->unitsHeld($accessory, $user));
        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
        $this->assertAcceptedRowUntouched($accepted, expectedQty: 3);
    }

    #[Test]
    public function an_accepted_acceptance_is_not_consumed_while_an_unaccepted_unit_remains(): void
    {
        [$accessory, $user] = $this->accessoryAndUser();
        $this->checkOut($accessory, $user, qty: 2);
        $this->checkOut($accessory, $user, qty: 1);

        $accepted = $this->acceptPendingRow($accessory, $user, index: 0);
        $this->assertSame(1, $this->pendingQtySum($accessory, $user));

        $this->checkIn($this->oneHeldCheckoutOf($accessory, $user));

        $this->assertSame(2, $this->unitsHeld($accessory, $user));
        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
        $this->assertAcceptedRowUntouched($accepted, expectedQty: 2);
    }

    // ─────────────────────────────────────────────────────────────────────
    // ACCESSORIES via the user-item transfer feature
    // ─────────────────────────────────────────────────────────────────────

    #[Test]
    public function transferring_one_of_two_single_unit_checkouts_leaves_the_other_outstanding(): void
    {
        [$accessory, $source] = $this->accessoryAndUser();
        $target = User::factory()->create();

        $this->checkOut($accessory, $source, qty: 1);
        $this->checkOut($accessory, $source, qty: 1);

        $this->transfer($source, $target, $this->oneHeldCheckoutOf($accessory, $source));

        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $source);
    }

    #[Test]
    public function transferring_one_unit_of_a_two_unit_checkout_decrements_that_acceptance(): void
    {
        [$accessory, $source] = $this->accessoryAndUser();
        $target = User::factory()->create();

        $this->checkOut($accessory, $source, qty: 2);

        $this->transfer($source, $target, $this->oneHeldCheckoutOf($accessory, $source));

        $this->assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $source);
    }

    #[Test]
    public function transferring_retires_exactly_one_unit_not_two(): void
    {
        [$accessory, $source] = $this->accessoryAndUser();
        $target = User::factory()->create();

        $this->checkOut($accessory, $source, qty: 3);

        // Transfer used to clear pendings inline AND fire the checkin event, so
        // both paths retired a unit. Holding three exposes it: over-retire
        // lands on 1, not 2.
        $this->transfer($source, $target, $this->oneHeldCheckoutOf($accessory, $source));

        $this->assertSame(2, $this->unitsHeld($accessory, $source));
        $this->assertSame(2, $this->pendingQtySum($accessory, $source), 'A single transfer must retire exactly one unit of pending qty.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONTROLS — 1:1 types. These should already be correct.
    // ─────────────────────────────────────────────────────────────────────

    #[Test]
    public function checking_in_one_license_seat_leaves_the_other_seats_acceptance_alone(): void
    {
        $user = User::factory()->create();
        $license = License::factory()->create([
            'reassignable' => 1,
            'seats' => 5,
            'category_id' => Category::factory()->forLicenses()->requiresAcceptance()->doesNotSendCheckinEmail(),
        ]);

        $seatA = $this->checkOutSeat($license, $user);
        $seatB = $this->checkOutSeat($license, $user);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('licenses.checkin.save', $seatA->id))
            ->assertRedirect();

        $this->assertSame(0, $this->pendingRowCountFor(LicenseSeat::class, $seatA->id, $user),
            'Checked-in seat should have no pending acceptance left.');
        $this->assertSame(1, $this->pendingRowCountFor(LicenseSeat::class, $seatB->id, $user),
            'The seat the user still holds must keep its pending acceptance.');
    }

    #[Test]
    public function checking_in_an_asset_clears_its_single_acceptance(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create([
            'model_id' => AssetModel::factory()->create([
                'category_id' => Category::factory()->forAssets()->requiresAcceptance()->doesNotSendCheckinEmail(),
            ]),
        ]);

        CheckoutAcceptance::factory()->withoutActionLog()->pending()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $user->id,
            'qty' => 1,
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('hardware.checkin.store', $asset->id))
            ->assertRedirect();

        $this->assertSame(0, $this->pendingRowCountFor(Asset::class, $asset->id, $user),
            'Asset checkin should clear its single pending acceptance.');
    }

    #[Test]
    public function checking_in_an_asset_whose_acceptance_has_a_null_qty_still_clears_it(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create([
            'model_id' => AssetModel::factory()->create([
                'category_id' => Category::factory()->forAssets()->requiresAcceptance()->doesNotSendCheckinEmail(),
            ]),
        ]);

        // AssetCheckoutController never passes a qty, so every real asset
        // acceptance is null. Null must read as one unit or 1:1 stops clearing.
        $acceptance = CheckoutAcceptance::factory()->withoutActionLog()->pending()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $user->id,
            'qty' => null,
        ]);
        $this->assertNull($acceptance->qty);

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('hardware.checkin.store', $asset->id))
            ->assertRedirect();

        $this->assertSame(0, $this->pendingRowCountFor(Asset::class, $asset->id, $user));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────

    /** @return array{0: Accessory, 1: User} */
    private function accessoryAndUser(): array
    {
        $accessory = Accessory::factory()->create([
            'category_id' => Category::factory()->forAccessories()->requiresAcceptance()->doesNotSendCheckinEmail(),
            'qty' => 20,
        ]);

        return [$accessory, User::factory()->create()];
    }

    private function checkOut(Accessory $accessory, User $user, int $qty): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.checkout.store', $accessory), [
                'assigned_user' => $user->id,
                'checkout_to_type' => 'user',
                'checkout_qty' => $qty,
            ])
            ->assertRedirect();
    }

    private function checkOutSeat(License $license, User $user): LicenseSeat
    {
        $seat = $license->licenseseats()->whereNull('assigned_to')->whereNull('asset_id')->firstOrFail();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('licenses.checkout.save', [$license->id, $seat->id]), [
                'assigned_user' => $user->id,
            ])
            ->assertRedirect();

        $seat->refresh();

        $this->assertSame(
            $user->id,
            $seat->assigned_to,
            'The seat was not checked out as expected.'
        );

        return $seat;
    }

    /**
     * Ids are per-table, so a location sharing a user's id is ordinary in
     * production but has to be forced here.
     */
    private function locationWithIdCollidingWith(User $user): Location
    {
        $location = Location::factory()->create();
        DB::table('locations')->where('id', $location->id)->update(['id' => $user->id]);

        return Location::findOrFail($user->id);
    }

    private function oneHeldCheckoutOf(Accessory $accessory, User $user): int
    {
        return $accessory->checkouts()
            ->where('assigned_type', User::class)
            ->where('assigned_to', $user->id)
            ->firstOrFail()
            ->id;
    }

    private function checkIn(int $accessoryCheckoutId): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('accessories.checkin.store', $accessoryCheckoutId))
            ->assertRedirect();
    }

    private function transfer(User $source, User $target, int $accessoryCheckoutId): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users.transfer.store', $source), [
                'target_user_id' => $target->id,
                'note' => 'matrix',
                'accessory_checkout_ids' => [$accessoryCheckoutId],
            ])
            ->assertRedirect();
    }

    /**
     * Accept one of the pair's pending rows, oldest first, returning it so the
     * caller can prove it was left alone.
     */
    private function acceptPendingRow(Accessory $accessory, User $user, int $index): CheckoutAcceptance
    {
        $acceptance = $this->pendingRows($accessory, $user)->get($index);

        $this->assertNotNull($acceptance, sprintf('No pending acceptance at index %d to accept.', $index));

        $acceptance->accept('signature.png', 'the eula text as signed', 'accepted.pdf', 'matrix');

        return $acceptance->refresh();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, CheckoutAcceptance> */
    private function pendingRows(Accessory $accessory, User $user)
    {
        return CheckoutAcceptance::pending()
            ->where('checkoutable_type', Accessory::class)
            ->where('checkoutable_id', $accessory->id)
            ->where('assigned_to_id', $user->id)
            ->orderBy('id')
            ->get();
    }

    private function unitsHeld(Accessory $accessory, User $user): int
    {
        return $accessory->checkouts()
            ->where('assigned_type', User::class)
            ->where('assigned_to', $user->id)
            ->count();
    }

    /** Null qty means one unit, as in AcceptanceController and LogListener. */
    private function pendingQtySum(Accessory $accessory, User $user): int
    {
        return $this->pendingRows($accessory, $user)->sum(fn (CheckoutAcceptance $a) => $a->qty ?? 1);
    }

    private function acceptedQtySum(Accessory $accessory, User $user): int
    {
        return CheckoutAcceptance::whereNotNull('accepted_at')
            ->where('checkoutable_type', Accessory::class)
            ->where('checkoutable_id', $accessory->id)
            ->where('assigned_to_id', $user->id)
            ->get()
            ->sum(fn (CheckoutAcceptance $a) => $a->qty ?? 1);
    }

    private function pendingRowCount(Accessory $accessory, User $user): int
    {
        return $this->pendingRowCountFor(Accessory::class, $accessory->id, $user);
    }

    private function pendingRowCountFor(string $type, int $id, User $user): int
    {
        return CheckoutAcceptance::pending()
            ->where('checkoutable_type', $type)
            ->where('checkoutable_id', $id)
            ->where('assigned_to_id', $user->id)
            ->count();
    }

    private function assertPendingQtyMatchesUnacceptedUnitsHeld(Accessory $accessory, User $user): void
    {
        $held = $this->unitsHeld($accessory, $user);
        $accepted = $this->acceptedQtySum($accessory, $user);
        $expected = max(0, $held - $accepted);
        $pending = $this->pendingQtySum($accessory, $user);

        $this->assertSame($expected, $pending, sprintf(
            'Invariant broken: user holds %d unit(s) with %d already accepted, so %d acknowledgement(s) should be outstanding — but pending acceptances total qty %d across %d row(s).',
            $held,
            $accepted,
            $expected,
            $pending,
            $this->pendingRowCount($accessory, $user)
        ));
    }

    /** An accepted row is the signed record: never renumber or remove it. */
    private function assertAcceptedRowUntouched(CheckoutAcceptance $accepted, int $expectedQty): void
    {
        $row = CheckoutAcceptance::withTrashed()->find($accepted->id);

        $this->assertNotNull($row, 'The accepted acceptance row was deleted.');
        $this->assertNull($row->deleted_at, 'The accepted acceptance row was soft-deleted; accepted rows are history and must survive.');
        $this->assertNotNull($row->accepted_at, 'The accepted acceptance row lost its accepted_at.');
        $this->assertSame($expectedQty, (int) $row->qty, 'The accepted acceptance row was renumbered; its qty records what the user agreed to.');
        $this->assertSame('the eula text as signed', $row->stored_eula, 'The accepted acceptance row lost the EULA text it was signed under.');
    }
}
