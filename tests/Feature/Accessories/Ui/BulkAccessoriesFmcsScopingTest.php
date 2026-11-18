<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('bulk delete cannot delete cross company accessory under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editAccessories()->deleteAccessories()->viewAccessories()->make());
    $target = Accessory::factory()->for($companyB)->create();

    $this->actingAs($actor)
        ->post(route('accessories.bulkdelete.store'), ['ids' => [$target->id]]);

    expect($target->fresh()->deleted_at)->toBeNull();
});

test('bulk edit dispatcher does not leak cross company accessory under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->viewAccessories()->editAccessories()->checkinAccessories()->make());

    $visible = Accessory::factory()->for($companyA)->create(['name' => 'Visible']);
    $hidden = Accessory::factory()->for($companyB)->create(['name' => 'Hidden']);

    $response = $this->actingAs($actor)
        ->post(route('accessories.bulkedit.show'), [
            'ids' => [$visible->id, $hidden->id],
            'bulk_actions' => 'edit',
        ])
        ->assertOk()
        ->assertViewIs('accessories.bulk-edit');

    $accessories = $response->viewData('accessories');
    expect($accessories->contains('id', $visible->id))->toBeTrue();
    expect($accessories->contains('id', $hidden->id))->toBeFalse();
});

test('bulk update cannot write cross company accessory under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editAccessories()->viewAccessories()->make());

    $target = Accessory::factory()->for($companyB)->create();
    $originalName = $target->name;

    $this->actingAs($actor)
        ->post(route('accessories.bulkedit.store'), [
            'ids' => [$target->id],
            'archived' => '1',
        ]);

    $fresh = $target->fresh();
    expect($fresh->name)->toBe($originalName);
    expect((bool) $fresh->archived)->toBeFalse();
});

test('bulk edit rejects write to cross company company id under fmcs', function () {
    // Even if a non-superuser hand-crafted a POST with a company_id
    // outside their pivot set, the fmcs_company validator + policy
    // layer should block the write.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editAccessories()->viewAccessories()->make());
    $target = Accessory::factory()->for($companyA)->create();

    $this->actingAs($actor)
        ->post(route('accessories.bulkedit.store'), [
            'ids' => [$target->id],
            'company_id' => (string) $companyB->id,
        ]);

    expect($target->fresh()->company_id)->toBe($companyA->id);
});

test('bulk edit rejects cross company location under scope locations fmcs', function () {
    // scope_locations_fmcs=1 means a location "belongs" to the
    // companies in its parent chain. fmcs_location validator must
    // reject a bulk-edit that pairs location X with company Y when
    // X doesn't live in Y.
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editAccessories()->viewAccessories()->make());
    $homeLocation = Location::factory()->for($companyA)->create();
    $target = Accessory::factory()->for($companyA)->create(['location_id' => $homeLocation->id]);
    $foreignLocation = Location::factory()->for($companyB)->create();

    // Flip scope_locations_fmcs AFTER factory setup so cross-company
    // location-vs-company validation is in force only for the actual
    // bulk-update request, not during the test's own data seeding.
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $this->actingAs($actor)
        ->post(route('accessories.bulkedit.store'), [
            'ids' => [$target->id],
            'location_id' => (string) $foreignLocation->id,
        ]);

    expect($target->fresh()->location_id)->toBe($homeLocation->id);
});

test('bulk checkin cannot check in cross company checkout under fmcs', function () {
    // Floater off: company A actor should not be able to check in
    // checkouts whose accessory belongs to company B, even if they
    // post the checkout id by hand (the row isn't in their table).
    Event::fake([CheckoutableCheckedIn::class]);

    $this->settings->enableMultipleFullCompanySupport();
    $this->settings->disableFloaterMode();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->viewAccessories()->editAccessories()->checkinAccessories()->make());

    $foreignAccessory = Accessory::factory()->for($companyB)->create();
    $foreignCheckout = AccessoryCheckout::factory()->for($foreignAccessory, 'accessory')->create();

    $this->actingAs($actor)
        ->post(route('accessories.bulkcheckin.store'), ['ids' => [$foreignCheckout->id]]);

    $this->assertDatabaseHas('accessories_checkout', ['id' => $foreignCheckout->id]);
    Event::assertNothingDispatched();
});

test('bulk checkin show does not leak cross company checkout under fmcs', function () {
    // The interstitial view must also filter the selection: posting
    // a cross-tenant id to .show should either redirect with a
    // warning or render an interstitial WITHOUT the foreign row.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->viewAccessories()->checkinAccessories()->make());

    $foreignAccessory = Accessory::factory()->for($companyB)->create();
    $foreignCheckout = AccessoryCheckout::factory()->for($foreignAccessory, 'accessory')->create();

    $this->actingAs($actor)
        ->post(route('accessories.bulkcheckin.show'), ['ids' => [$foreignCheckout->id]])
        ->assertRedirect()
        ->assertSessionHas('warning');
});

test('bulk checkin floater can checkin null company checkout', function () {
    // Floater ON: company A actor is allowed to touch null-company
    // (floater) accessories. Pin so a future refactor that over-
    // tightens the subquery doesn't block a legitimate flow.
    Event::fake([CheckoutableCheckedIn::class]);

    $this->settings->enableFloaterMode();

    $companyA = Company::factory()->create();
    $actor = $companyA->users()->save(User::factory()->viewAccessories()->editAccessories()->checkinAccessories()->make());

    $floater = Accessory::factory()->create(['company_id' => null]);
    $checkout = AccessoryCheckout::factory()->for($floater, 'accessory')->create();

    $this->actingAs($actor)
        ->post(route('accessories.bulkcheckin.store'), ['ids' => [$checkout->id]])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('accessories_checkout', ['id' => $checkout->id]);
    Event::assertDispatched(CheckoutableCheckedIn::class);
});

test('unarchive cannot touch cross company accessory under fmcs', function () {
    // postUnarchive relies on route-model binding (which runs the
    // Accessory CompanyableScope) + $this->authorize('update', $accessory).
    // A cross-tenant target is either scoped out at binding time or
    // forbidden by the policy. Either way the archived bit must not
    // flip - that's the actual contract.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editAccessories()->viewAccessories()->make());
    $foreign = Accessory::factory()->for($companyB)->create(['archived' => true]);

    $this->actingAs($actor)
        ->post(route('accessories.unarchive', $foreign));

    expect((bool) $foreign->fresh()->archived)->toBeTrue();
});
