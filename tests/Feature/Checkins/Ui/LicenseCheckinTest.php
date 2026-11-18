<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\Category;
use App\Models\CheckoutAcceptance;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CheckinLicenseSeatNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;

test('checking in license requires correct permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('licenses.checkin.save', [
            'licenseId' => LicenseSeat::factory()->assignedToUser()->create()->id,
        ]))
        ->assertForbidden();
});

test('non reassignable seat remains unreassignable after checkin', function () {
    $licenseSeat = LicenseSeat::factory()
        ->notReassignable()
        ->assignedToUser()
        ->create();

    $this->actingAs(User::factory()->checkinLicenses()->create())
        ->post(route('licenses.checkin.save', $licenseSeat));

    $licenseSeat->refresh();

    expect($licenseSeat->unreassignable_seat)->toEqual(true);
});

test('cannot checkin license that is not assigned', function () {
    $licenseSeat = LicenseSeat::factory()
        ->reassignable()
        ->create();

    expect($licenseSeat->assigned_to)->toBeNull();
    expect($licenseSeat->asset_id)->toBeNull();

    $this->actingAs(User::factory()->checkinLicenses()->create())
        ->post(route('licenses.checkin.save', $licenseSeat), [
            'notes' => 'my note',
            'redirect_option' => 'index',
        ])
        ->assertSessionHas('error', trans('admin/licenses/message.checkin.error'));
});

test('can check in license assigned to asset', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $asset = Asset::factory()->create();

    $licenseSeat = LicenseSeat::factory()
        ->reassignable()
        ->assignedToAsset($asset)
        ->create();

    $actor = User::factory()->checkinLicenses()->create();

    $this->actingAs($actor)
        ->post(route('licenses.checkin.save', $licenseSeat), [
            'notes' => 'my note',
            'redirect_option' => 'index',
        ])
        ->assertRedirect(route('licenses.index'));

    expect($licenseSeat->fresh()->asset_id)->toBeNull();
    expect($licenseSeat->fresh()->assigned_to)->toBeNull();
    expect($licenseSeat->fresh()->notes)->toEqual('my note');

    Event::assertDispatchedTimes(CheckoutableCheckedIn::class, 1);
    Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($actor, $asset, $licenseSeat) {
        return $event->checkoutable->is($licenseSeat)
            && $event->checkedOutTo->is($asset)
            && $event->checkedInBy->is($actor)
            && $event->note === 'my note';
    });
});

test('can check in license assigned to user', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $user = User::factory()->create();

    $licenseSeat = LicenseSeat::factory()
        ->reassignable()
        ->assignedToUser($user)
        ->create();

    $actor = User::factory()->checkinLicenses()->create();

    $this->actingAs($actor)
        ->post(route('licenses.checkin.save', $licenseSeat), [
            'notes' => 'my note',
            'redirect_option' => 'index',
        ])
        ->assertRedirect(route('licenses.index'));

    expect($licenseSeat->fresh()->asset_id)->toBeNull();
    expect($licenseSeat->fresh()->assigned_to)->toBeNull();
    expect($licenseSeat->fresh()->notes)->toEqual('my note');

    Event::assertDispatchedTimes(CheckoutableCheckedIn::class, 1);
    Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($actor, $licenseSeat, $user) {
        return $event->checkoutable->is($licenseSeat)
            && $event->checkedOutTo->is($user)
            && $event->checkedInBy->is($actor)
            && $event->note === 'my note';
    });
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('licenses.checkin', LicenseSeat::factory()->assignedToUser()->create()->id))
        ->assertOk();
});

test('license seat checkin clears pending acceptance when notifications are fully disabled', function () {
    $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();

    $user = User::factory()->create();
    $seat = seatAssignedTo($user);

    $acceptance = pendingSeatAcceptance($seat, $user);

    assertNoNotificationChannelIsConfigured($seat);

    checkInSeat($seat);

    assertAcceptanceWasSoftDeleted($acceptance, 'The pending acceptance should have been soft-deleted when the seat was checked in, but it survived: with no email and no webhook configured the listener returns before it reaches the delete.');
});

test('license seat checkin clears pending acceptance when only a webhook is configured', function () {
    Notification::fake();

    $this->settings->disableAdminCC()->disableAdminCCAlways()->enableSlackWebhook();

    $user = User::factory()->create();
    $seat = seatAssignedTo($user);

    $acceptance = pendingSeatAcceptance($seat, $user);

    checkInSeat($seat);

    $this->assertSlackNotificationSent(CheckinLicenseSeatNotification::class);

    assertAcceptanceWasSoftDeleted($acceptance, 'The pending acceptance should have been soft-deleted when the seat was checked in, but it survived: a webhook-only install never enters the notification branch that holds the delete.');
});

test('license seat checkin does not clear a same id pending acceptance of another type', function () {
    $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();

    $user = User::factory()->create();

    [$seat, $asset] = createSeatAndAssetSharingAnId($user);

    $seatAcceptance = pendingSeatAcceptance($seat, $user);

    $assetAcceptance = CheckoutAcceptance::factory()->pending()->create([
        'checkoutable_id' => $asset->id,
        'assigned_to_id' => $user->id,
    ]);
    expect($assetAcceptance->checkoutable_type)->toBe(Asset::class);

    checkInSeat($seat);

    assertAcceptanceWasSoftDeleted($seatAcceptance, 'The checked-in seat\'s own pending acceptance should have been soft-deleted.');

    expect(CheckoutAcceptance::withTrashed()->findOrFail($assetAcceptance->id)->deleted_at)->toBeNull('Checking in the license seat should have left the asset acceptance alone. It was soft-deleted too, because the listener matches on checkoutable_id and assigned_to_id without filtering on checkoutable_type, and the asset happens to share the seat\'s id.');
});

/**
 * Acceptance required, checkin email off — the configuration where a stale
 * pending acceptance can outlive the assignment it belongs to.
 */
function licenseRequiringAcceptanceWithoutCheckinEmail(): License
{
    return License::factory()->create([
        'reassignable' => 1,
        'category_id' => Category::factory()->create([
            'category_type' => 'license',
            'require_acceptance' => 1,
            'checkin_email' => 0,
        ])->id,
    ]);
}

function seatAssignedTo(User $user): LicenseSeat
{
    return LicenseSeat::factory()
        ->assignedToUser($user)
        ->create(['license_id' => licenseRequiringAcceptanceWithoutCheckinEmail()->id]);
}

/**
 * A real seat acceptance is keyed to the SEAT — LicenseSeat::class plus the
 * seat's own id — which is what CreateCheckoutAcceptanceAction writes. There
 * is no morph map aliasing License and LicenseSeat.
 */
function pendingSeatAcceptance(LicenseSeat $seat, User $user): CheckoutAcceptance
{
    $acceptance = CheckoutAcceptance::factory()
        ->forLicenseSeat()
        ->pending()
        ->create([
            'checkoutable_id' => $seat->id,
            'assigned_to_id' => $user->id,
        ]);

    expect($acceptance->checkoutable_type)->toBe(LicenseSeat::class);
    expect($acceptance->isPending())->toBeTrue();

    return $acceptance;
}

function checkInSeat(LicenseSeat $seat): void
{
    $this->actingAs(User::factory()->checkinLicenses()->create())
        ->post(route('licenses.checkin.save', $seat->id))
        ->assertRedirect();

    // Confirm the checkin actually happened before asserting anything about
    // what it did or didn't clean up.
    expect($seat->refresh()->assigned_to)->toBeNull('The seat was not checked in, so nothing downstream ran.');
}

function createSeatAndAssetSharingAnId(User $user): array
{
    // A license spawns its own seats when it saves, so the id is only free
    // to claim once those exist.
    $license = licenseRequiringAcceptanceWithoutCheckinEmail();

    $sharedId = firstIdFreeInBoth('license_seats', 'assets');

    $seat = LicenseSeat::factory()
        ->assignedToUser($user)
        ->create(['license_id' => $license->id, 'id' => $sharedId]);

    $asset = Asset::factory()->create(['id' => $sharedId]);

    // Without the collision there is no morph bug to trigger and the test
    // passes for the wrong reason, so prove the ids really do line up.
    expect($asset->id)->toBe($seat->id);

    return [$seat, $asset];
}

/**
 * An id no row in either table holds, so both can be created with it.
 * Taken above both maxima rather than read from the auto-increment
 * counters, which are engine-specific to query.
 */
function firstIdFreeInBoth(string $table, string $otherTable): int
{
    return max(
        (int) DB::table($table)->max('id'),
        (int) DB::table($otherTable)->max('id'),
    ) + 1;
}

function assertNoNotificationChannelIsConfigured(LicenseSeat $seat): void
{
    expect((bool) $seat->license->checkin_email())->toBeFalse();
    expect(Setting::getSettings()->admin_cc_email)->toBeEmpty();
    expect(Setting::getSettings()->webhook_endpoint)->toBeEmpty();
}

function assertAcceptanceWasSoftDeleted(CheckoutAcceptance $acceptance, string $message): void
{
    expect(CheckoutAcceptance::withTrashed()->findOrFail($acceptance->id)->deleted_at)->not->toBeNull($message);
}
