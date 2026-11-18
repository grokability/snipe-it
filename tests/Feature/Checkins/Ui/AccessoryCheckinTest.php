<?php

use App\Events\CheckoutableCheckedIn;
use App\Mail\CheckinAccessoryMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use App\Notifications\CheckinAccessoryNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertSoftDeleted;

test('checking in accessory requires correct permission', function () {
    $accessory = Accessory::factory()->checkedOutToUser()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('accessories.checkin.store', $accessory->checkouts->first()->id))
        ->assertForbidden();
});

test('page renders', function () {
    $accessory = Accessory::factory()->checkedOutToUser()->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('accessories.checkin.show', $accessory->checkouts->first()->id))
        ->assertOk();
});

test('accessory can be checked in', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $user = User::factory()->create();
    $accessory = Accessory::factory()->checkedOutToUser($user)->create();

    expect($accessory->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0)->toBeTrue();

    $this->actingAs(User::factory()->checkinAccessories()->create())
        ->post(route('accessories.checkin.store', $accessory->checkouts->first()->id));

    expect($accessory->fresh()->checkouts()->where('assigned_type', User::class)->where('assigned_to', $user->id)->count() > 0)->toBeFalse();

    Event::assertDispatched(CheckoutableCheckedIn::class, 1);
});

test('email sent to user if setting enabled', function () {
    Mail::fake();

    $user = User::factory()->create();
    $accessory = Accessory::factory()->checkedOutToUser($user)->create();

    $accessory->category->update(['checkin_email' => true]);

    event(new CheckoutableCheckedIn(
        $accessory,
        $user,
        User::factory()->checkinAccessories()->create(),
        '',
    ));
    Mail::assertSent(CheckinAccessoryMail::class, function (CheckinAccessoryMail $mail) use ($user) {
        return $mail->hasTo($user->email);

    });
});

test('email not sent to user if setting disabled', function () {
    Mail::fake();

    $user = User::factory()->create();
    $accessory = Accessory::factory()->checkedOutToUser($user)->create();

    $accessory->category->update([
        'checkin_email' => false,
        'require_acceptance' => false,
        'eula_text' => null,
    ]);

    event(new CheckoutableCheckedIn(
        $accessory,
        $user,
        User::factory()->checkinAccessories()->create(),
        '',
    ));

    Mail::assertNotSent(CheckinAccessoryMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('accessory checkin clears pending acceptance when notifications are fully disabled', function () {
    $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();

    $user = User::factory()->create();
    $accessory = Accessory::factory()->checkedOutToUser($user)->create();

    $accessory->category->update([
        'require_acceptance' => true,
        'checkin_email' => false,
    ]);

    $acceptance = CheckoutAcceptance::factory()->forAccessory()->pending()->create([
        'checkoutable_id' => $accessory->id,
        'assigned_to_id' => $user->id,
    ]);

    expect($acceptance->isPending())->toBeTrue();

    checkInAccessoryFrom($accessory, $user);

    assertSoftDeleted($acceptance);
});

test('accessory checkin clears pending acceptance when only a webhook is configured', function () {
    Notification::fake();

    $this->settings->disableAdminCC()->disableAdminCCAlways()->enableSlackWebhook();

    $user = User::factory()->create();
    $accessory = Accessory::factory()->checkedOutToUser($user)->create();

    $accessory->category->update([
        'require_acceptance' => true,
        'checkin_email' => false,
    ]);

    $acceptance = CheckoutAcceptance::factory()->forAccessory()->pending()->create([
        'checkoutable_id' => $accessory->id,
        'assigned_to_id' => $user->id,
    ]);

    expect($acceptance->isPending())->toBeTrue();

    checkInAccessoryFrom($accessory, $user);

    $this->assertSlackNotificationSent(CheckinAccessoryNotification::class);

    assertSoftDeleted($acceptance);
});

test('accessory checkin does not clear a same id pending acceptance of another type', function () {
    Mail::fake();

    $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();

    $user = User::factory()->create();

    [$accessory, $asset] = createAccessoryAndAssetSharingAnId($user);

    // The asset's acceptance is created FIRST so it is the older row.
    // Accessory checkins retire only the oldest matching pending row, so
    // with the accessory's own row older this test would pass whether or
    // not the query filters on checkoutable_type — the asset's row would
    // never be reached either way.
    $assetAcceptance = CheckoutAcceptance::factory()->pending()->create([
        'checkoutable_id' => $asset->id,
        'assigned_to_id' => $user->id,
    ]);

    $accessoryAcceptance = CheckoutAcceptance::factory()->forAccessory()->pending()->create([
        'checkoutable_id' => $accessory->id,
        'assigned_to_id' => $user->id,
    ]);

    checkInAccessoryFrom($accessory, $user);

    assertSoftDeleted($accessoryAcceptance);

    expect(CheckoutAcceptance::withTrashed()->findOrFail($assetAcceptance->id)->deleted_at)->toBeNull('Checking in the accessory should have left the asset acceptance alone. It was soft-deleted too, because the listener matches on checkoutable_id and assigned_to_id without filtering on checkoutable_type, and the asset happens to share the accessory\'s id.');
});

function checkInAccessoryFrom(Accessory $accessory, User $user): void
{
    $checkout = $accessory->checkouts()
        ->where('assigned_type', User::class)
        ->where('assigned_to', $user->id)
        ->firstOrFail();

    actingAs(User::factory()->checkinAccessories()->create())
        ->post(route('accessories.checkin.store', $checkout->id))
        ->assertRedirect();

    assertDatabaseMissing('accessories_checkout', ['id' => $checkout->id]);
}

function createAccessoryAndAssetSharingAnId(User $user): array
{
    $sharedId = firstIdFreeInBoth('accessories', 'assets');

    $accessory = Accessory::factory()->checkedOutToUser($user)->create(['id' => $sharedId]);
    $asset = Asset::factory()->create(['id' => $sharedId]);

    // Without the collision there is no morph bug to trigger and the test
    // passes for the wrong reason, so prove the ids really do line up.
    expect($asset->id)->toBe($accessory->id);

    return [$accessory, $asset];
}
