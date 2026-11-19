<?php

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

use function Pest\Laravel\actingAs;

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
beforeEach(function () {
    Mail::fake();
    $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();
});

test('checking in the only unit clears the acceptance', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 1);

    checkIn(oneHeldCheckoutOf($accessory, $user));

    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
});

test('checking in one of two single unit checkouts leaves the other outstanding', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 1);
    checkOut($accessory, $user, qty: 1);

    checkIn(oneHeldCheckoutOf($accessory, $user));

    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
});

test('checking in one unit of a two unit checkout decrements that acceptance', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 2);

    // One row worth 2 units: no delete-a-row strategy can be right here.
    expect(pendingRowCount($accessory, $user))->toBe(1);

    checkIn(oneHeldCheckoutOf($accessory, $user));

    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
});

test('checking in one unit across two checkouts of different sizes', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 2);
    checkOut($accessory, $user, qty: 1);

    checkIn(oneHeldCheckoutOf($accessory, $user));

    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
});

test('checking in every unit of a multi unit checkout clears the acceptance', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 2);

    checkIn(oneHeldCheckoutOf($accessory, $user));
    checkIn(oneHeldCheckoutOf($accessory, $user));

    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
});

test('retiring more units than the oldest acceptance holds spills into the next', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 3);
    checkOut($accessory, $user, qty: 2);

    // Retiring four of five units has to exhaust the first row and bite
    // into the second, which "one row per checkin" cannot do.
    for ($i = 0; $i < 4; $i++) {
        checkIn(oneHeldCheckoutOf($accessory, $user));
    }

    expect(unitsHeld($accessory, $user))->toBe(1);
    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
});

test('checking in a location held unit leaves a users acceptance alone', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 2);

    // Acceptances only ever belong to users, so assigned_to_id holds a user
    // id. An accessory can be held by a user and a location at once, so a
    // location id equal to a user id must not match that user's rows.
    $location = locationWithIdCollidingWith($user);

    $locationCheckout = AccessoryCheckout::create([
        'accessory_id' => $accessory->id,
        'assigned_to' => $location->id,
        'assigned_type' => Location::class,
        'created_by' => User::factory()->create()->id,
    ]);

    checkIn($locationCheckout->id);

    expect(unitsHeld($accessory, $user))->toBe(2, 'The user\'s own units were not touched.');
    expect(pendingQtySum($accessory, $user))->toBe(2, 'Checking in a location-held unit cleared the user\'s acceptance by id collision.');
});

test('checking in when one of three single unit checkouts is already accepted', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 1);
    checkOut($accessory, $user, qty: 1);
    checkOut($accessory, $user, qty: 1);

    $accepted = acceptPendingRow($accessory, $user, index: 1);

    checkIn(oneHeldCheckoutOf($accessory, $user));

    expect(unitsHeld($accessory, $user))->toBe(2);
    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
    assertAcceptedRowUntouched($accepted, expectedQty: 1);
});

test('checking in when a middle sized checkout is already accepted', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 2);
    checkOut($accessory, $user, qty: 1);
    checkOut($accessory, $user, qty: 3);

    // Rows of differing size, so nothing passes by conflating rows/units.
    expect(unitsHeld($accessory, $user))->toBe(6);

    $accepted = acceptPendingRow($accessory, $user, index: 1);

    checkIn(oneHeldCheckoutOf($accessory, $user));

    expect(unitsHeld($accessory, $user))->toBe(5);
    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
    assertAcceptedRowUntouched($accepted, expectedQty: 1);
});

test('checking in two units when the largest checkout is already accepted', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 2);
    checkOut($accessory, $user, qty: 1);
    checkOut($accessory, $user, qty: 3);

    $accepted = acceptPendingRow($accessory, $user, index: 2);

    checkIn(oneHeldCheckoutOf($accessory, $user));
    checkIn(oneHeldCheckoutOf($accessory, $user));

    expect(unitsHeld($accessory, $user))->toBe(4);
    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
    assertAcceptedRowUntouched($accepted, expectedQty: 3);
});

test('checking in against a fully accepted checkout leaves nothing to retire', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 3);

    $accepted = acceptPendingRow($accessory, $user, index: 0);
    expect(pendingQtySum($accessory, $user))->toBe(0, 'Accepting the only row should leave nothing pending.');

    checkIn(oneHeldCheckoutOf($accessory, $user));

    // Nothing outstanding, so nothing to retire. The accepted row must not
    // be decremented to balance the books.
    expect(unitsHeld($accessory, $user))->toBe(2);
    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
    assertAcceptedRowUntouched($accepted, expectedQty: 3);
});

test('an accepted acceptance is not consumed while an unaccepted unit remains', function () {
    [$accessory, $user] = accessoryAndUser();
    checkOut($accessory, $user, qty: 2);
    checkOut($accessory, $user, qty: 1);

    $accepted = acceptPendingRow($accessory, $user, index: 0);
    expect(pendingQtySum($accessory, $user))->toBe(1);

    checkIn(oneHeldCheckoutOf($accessory, $user));

    expect(unitsHeld($accessory, $user))->toBe(2);
    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $user);
    assertAcceptedRowUntouched($accepted, expectedQty: 2);
});

test('transferring one of two single unit checkouts leaves the other outstanding', function () {
    [$accessory, $source] = accessoryAndUser();
    $target = User::factory()->create();

    checkOut($accessory, $source, qty: 1);
    checkOut($accessory, $source, qty: 1);

    transfer($source, $target, oneHeldCheckoutOf($accessory, $source));

    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $source);
});

test('transferring one unit of a two unit checkout decrements that acceptance', function () {
    [$accessory, $source] = accessoryAndUser();
    $target = User::factory()->create();

    checkOut($accessory, $source, qty: 2);

    transfer($source, $target, oneHeldCheckoutOf($accessory, $source));

    assertPendingQtyMatchesUnacceptedUnitsHeld($accessory, $source);
});

test('transferring retires exactly one unit not two', function () {
    [$accessory, $source] = accessoryAndUser();
    $target = User::factory()->create();

    checkOut($accessory, $source, qty: 3);

    // Transfer used to clear pendings inline AND fire the checkin event, so
    // both paths retired a unit. Holding three exposes it: over-retire
    // lands on 1, not 2.
    transfer($source, $target, oneHeldCheckoutOf($accessory, $source));

    expect(unitsHeld($accessory, $source))->toBe(2);
    expect(pendingQtySum($accessory, $source))->toBe(2, 'A single transfer must retire exactly one unit of pending qty.');
});

test('checking in one license seat leaves the other seats acceptance alone', function () {
    $user = User::factory()->create();
    $license = License::factory()->create([
        'reassignable' => 1,
        'seats' => 5,
        'category_id' => Category::factory()->forLicenses()->requiresAcceptance()->doesNotSendCheckinEmail(),
    ]);

    $seatA = checkOutSeat($license, $user);
    $seatB = checkOutSeat($license, $user);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('licenses.checkin.save', $seatA->id))
        ->assertRedirect();

    expect(pendingRowCountFor(LicenseSeat::class, $seatA->id, $user))->toBe(0, 'Checked-in seat should have no pending acceptance left.');
    expect(pendingRowCountFor(LicenseSeat::class, $seatB->id, $user))->toBe(1, 'The seat the user still holds must keep its pending acceptance.');
});

test('checking in an asset clears its single acceptance', function () {
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

    expect(pendingRowCountFor(Asset::class, $asset->id, $user))->toBe(0, 'Asset checkin should clear its single pending acceptance.');
});

test('checking in an asset whose acceptance has a null qty still clears it', function () {
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
    expect($acceptance->qty)->toBeNull();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkin.store', $asset->id))
        ->assertRedirect();

    expect(pendingRowCountFor(Asset::class, $asset->id, $user))->toBe(0);
});

// ─────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────
/** @return array{0: Accessory, 1: User} */
function accessoryAndUser(): array
{
    $accessory = Accessory::factory()->create([
        'category_id' => Category::factory()->forAccessories()->requiresAcceptance()->doesNotSendCheckinEmail(),
        'qty' => 20,
    ]);

    return [$accessory, User::factory()->create()];
}

function checkOut(Accessory $accessory, User $user, int $qty): void
{
    actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.checkout.store', $accessory), [
            'assigned_user' => $user->id,
            'checkout_to_type' => 'user',
            'checkout_qty' => $qty,
        ])
        ->assertRedirect();
}

function checkOutSeat(License $license, User $user): LicenseSeat
{
    $seat = $license->licenseseats()->whereNull('assigned_to')->whereNull('asset_id')->firstOrFail();

    actingAs(User::factory()->superuser()->create())
        ->post(route('licenses.checkout.save', [$license->id, $seat->id]), [
            'assigned_user' => $user->id,
        ])
        ->assertRedirect();

    $seat->refresh();

    expect($seat->assigned_to)->toBe($user->id, 'The seat was not checked out as expected.');

    return $seat;
}

/**
 * Ids are per-table, so a location sharing a user's id is ordinary in
 * production but has to be forced here.
 */
function locationWithIdCollidingWith(User $user): Location
{
    $location = Location::factory()->create();
    DB::table('locations')->where('id', $location->id)->update(['id' => $user->id]);

    return Location::findOrFail($user->id);
}

function oneHeldCheckoutOf(Accessory $accessory, User $user): int
{
    return $accessory->checkouts()
        ->where('assigned_type', User::class)
        ->where('assigned_to', $user->id)
        ->firstOrFail()
        ->id;
}

function checkIn(int $accessoryCheckoutId): void
{
    actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.checkin.store', $accessoryCheckoutId))
        ->assertRedirect();
}

function transfer(User $source, User $target, int $accessoryCheckoutId): void
{
    actingAs(User::factory()->superuser()->create())
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
function acceptPendingRow(Accessory $accessory, User $user, int $index): CheckoutAcceptance
{
    $acceptance = pendingRows($accessory, $user)->get($index);

    expect($acceptance)->not->toBeNull(sprintf('No pending acceptance at index %d to accept.', $index));

    $acceptance->accept('signature.png', 'the eula text as signed', 'accepted.pdf', 'matrix');

    return $acceptance->refresh();
}

/** @return \Illuminate\Database\Eloquent\Collection<int, CheckoutAcceptance> */
function pendingRows(Accessory $accessory, User $user)
{
    return CheckoutAcceptance::pending()
        ->where('checkoutable_type', Accessory::class)
        ->where('checkoutable_id', $accessory->id)
        ->where('assigned_to_id', $user->id)
        ->orderBy('id')
        ->get();
}

function unitsHeld(Accessory $accessory, User $user): int
{
    return $accessory->checkouts()
        ->where('assigned_type', User::class)
        ->where('assigned_to', $user->id)
        ->count();
}

/** Null qty means one unit, as in AcceptanceController and LogListener. */
function pendingQtySum(Accessory $accessory, User $user): int
{
    return pendingRows($accessory, $user)->sum(fn (CheckoutAcceptance $a) => $a->qty ?? 1);
}

function acceptedQtySum(Accessory $accessory, User $user): int
{
    return CheckoutAcceptance::whereNotNull('accepted_at')
        ->where('checkoutable_type', Accessory::class)
        ->where('checkoutable_id', $accessory->id)
        ->where('assigned_to_id', $user->id)
        ->get()
        ->sum(fn (CheckoutAcceptance $a) => $a->qty ?? 1);
}

function pendingRowCount(Accessory $accessory, User $user): int
{
    return pendingRowCountFor(Accessory::class, $accessory->id, $user);
}

function pendingRowCountFor(string $type, int $id, User $user): int
{
    return CheckoutAcceptance::pending()
        ->where('checkoutable_type', $type)
        ->where('checkoutable_id', $id)
        ->where('assigned_to_id', $user->id)
        ->count();
}

function assertPendingQtyMatchesUnacceptedUnitsHeld(Accessory $accessory, User $user): void
{
    $held = unitsHeld($accessory, $user);
    $accepted = acceptedQtySum($accessory, $user);
    $expected = max(0, $held - $accepted);
    $pending = pendingQtySum($accessory, $user);

    expect($pending)->toBe($expected, sprintf(
        'Invariant broken: user holds %d unit(s) with %d already accepted, so %d acknowledgement(s) should be outstanding — but pending acceptances total qty %d across %d row(s).',
        $held,
        $accepted,
        $expected,
        $pending,
        pendingRowCount($accessory, $user)
    ));
}

/** An accepted row is the signed record: never renumber or remove it. */
function assertAcceptedRowUntouched(CheckoutAcceptance $accepted, int $expectedQty): void
{
    $row = CheckoutAcceptance::withTrashed()->find($accepted->id);

    expect($row)->not->toBeNull('The accepted acceptance row was deleted.');
    expect($row->deleted_at)->toBeNull('The accepted acceptance row was soft-deleted; accepted rows are history and must survive.');
    expect($row->accepted_at)->not->toBeNull('The accepted acceptance row lost its accepted_at.');
    expect((int) $row->qty)->toBe($expectedQty, 'The accepted acceptance row was renumbered; its qty records what the user agreed to.');
    expect($row->stored_eula)->toBe('the eula text as signed', 'The accepted acceptance row lost the EULA text it was signed under.');
}
