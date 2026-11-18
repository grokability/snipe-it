<?php

use App\Mail\CheckinAccessoryMail;
use App\Models\Accessory;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('requires permission', function () {
    $accessory = Accessory::factory()->checkedOutToUser()->create();
    $accessoryCheckout = $accessory->checkouts->first();

    $this->actingAsForApi(User::factory()->create())
        ->postJson(route('api.accessories.checkin', $accessoryCheckout))
        ->assertForbidden();
});

test('adheres to full multiple companies support scoping', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $superUser = $companyA->users()->save(User::factory()->superuser()->make());
    $userInCompanyA = User::factory()->forCompany($companyA)->checkinAccessories()->create();
    $accessoryForCompanyB = Accessory::factory()->for($companyB)->checkedOutToUser()->create();
    $anotherAccessoryForCompanyB = Accessory::factory()->for($companyB)->checkedOutToUser()->create();

    expect($accessoryForCompanyB->checkouts->count())->toEqual(1);
    expect($anotherAccessoryForCompanyB->checkouts->count())->toEqual(1);

    $this->settings->enableMultipleFullCompanySupport();

    // Cross-tenant checkout is hidden from the caller by the
    // CompanyableChildScope on AccessoryCheckout, so AccessoryCheckout::find()
    // in the controller returns null and we fall into the standard
    // does-not-exist error envelope rather than a 403. Line 50
    // asserts the checkin didn't actually happen, which is the
    // security property that matters.
    $this->actingAsForApi($userInCompanyA)
        ->postJson(route('api.accessories.checkin', $accessoryForCompanyB->checkouts->first()))
        ->assertStatusMessageIs('error');

    $this->actingAsForApi($superUser)
        ->postJson(route('api.accessories.checkin', $anotherAccessoryForCompanyB->checkouts->first()))
        ->assertStatusMessageIs('success');

    expect($accessoryForCompanyB->fresh()->checkouts->count())->toEqual(1, 'Accessory should not be checked in');
    expect($anotherAccessoryForCompanyB->fresh()->checkouts->count())->toEqual(0, 'Accessory should be checked in');
    $this->assertHasTheseActionLogs($anotherAccessoryForCompanyB, ['create', 'checkin from']);
});

test('can checkin accessory', function () {
    $accessory = Accessory::factory()->checkedOutToUser()->create();

    expect($accessory->checkouts->count())->toEqual(1);

    $accessoryCheckout = $accessory->checkouts->first();

    $this->actingAsForApi(User::factory()->checkinAccessories()->create())
        ->postJson(route('api.accessories.checkin', $accessoryCheckout))
        ->assertStatusMessageIs('success');

    expect($accessory->fresh()->checkouts->count())->toEqual(0, 'Accessory should be checked in');
    $this->assertHasTheseActionLogs($accessory, ['create'/* , 'checkout' */, 'checkin from']);
    // TODO - should be the 3 events!
});

test('checkin is logged', function () {
    $user = User::factory()->create();
    $actor = User::factory()->checkinAccessories()->create();

    $accessory = Accessory::factory()->checkedOutToUser($user)->create();
    $accessoryCheckout = $accessory->checkouts->first();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkin', $accessoryCheckout))
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'created_by' => $actor->id,
        'action_type' => 'checkin from',
        'target_id' => $user->id,
        'target_type' => User::class,
        'item_id' => $accessory->id,
        'item_type' => Accessory::class,
    ]);

    $this->assertHasTheseActionLogs($accessory, ['create', 'checkin from']);
});

test('checkin of accessory checked out to location is logged against the location', function () {
    $location = Location::factory()->create();
    $actor = User::factory()->checkinAccessories()->create();

    $accessory = Accessory::factory()->checkedOutToLocation($location)->create();
    $accessoryCheckout = $accessory->checkouts->first();

    $this->actingAsForApi($actor)
        ->postJson(route('api.accessories.checkin', $accessoryCheckout))
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'created_by' => $actor->id,
        'action_type' => 'checkin from',
        'target_id' => $location->id,
        'target_type' => Location::class,
        'item_id' => $accessory->id,
        'item_type' => Accessory::class,
    ]);
});

test('checkin sends checkin email to user when category enables it', function () {
    Mail::fake();

    $user = User::factory()->create();
    $accessory = Accessory::factory()->checkedOutToUser($user)->create();
    $accessory->category->update(['checkin_email' => true]);

    $accessoryCheckout = $accessory->checkouts->first();

    $this->actingAsForApi(User::factory()->checkinAccessories()->create())
        ->postJson(route('api.accessories.checkin', $accessoryCheckout))
        ->assertStatusMessageIs('success');

    Mail::assertSent(
        CheckinAccessoryMail::class,
        fn ($mail) => $mail->hasTo($user->email),
    );
});

test('checkin clears pending acceptance when notifications are fully disabled', function () {
    $this->settings->disableAdminCC()->disableAdminCCAlways()->disableSlackWebhook();

    $user = User::factory()->create();
    $accessory = Accessory::factory()->checkedOutToUser($user)->create();

    $accessory->category->update([
        'require_acceptance' => true,
        'checkin_email' => false,
    ]);

    $checkout = $accessory->checkouts()
        ->where('assigned_type', User::class)
        ->where('assigned_to', $user->id)
        ->firstOrFail();

    $acceptance = CheckoutAcceptance::factory()->forAccessory()->pending()->create([
        'checkoutable_id' => $accessory->id,
        'assigned_to_id' => $user->id,
    ]);

    expect($acceptance->isPending())->toBeTrue();

    $this->actingAsForApi(User::factory()->checkinAccessories()->create())
        ->postJson(route('api.accessories.checkin', $checkout))
        ->assertStatusMessageIs('success');

    expect(CheckoutAcceptance::withTrashed()->findOrFail($acceptance->id)->deleted_at)->not->toBeNull('Pending acceptance survived accessory check-in.');
});
