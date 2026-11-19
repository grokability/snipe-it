<?php

use App\Models\Company;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Location;
use App\Models\User;

test('bulk delete cannot delete cross company consumable under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editConsumables()->deleteConsumables()->viewConsumables()->make());
    $target = Consumable::factory()->for($companyB)->create();

    $this->actingAs($actor)
        ->post(route('consumables.bulkdelete.store'), ['ids' => [$target->id]]);

    expect($target->fresh()->deleted_at)->toBeNull();
});

test('bulk edit dispatcher does not leak cross company consumable under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->viewConsumables()->editConsumables()->checkinConsumables()->make());

    $visible = Consumable::factory()->for($companyA)->create(['name' => 'Visible']);
    $hidden = Consumable::factory()->for($companyB)->create(['name' => 'Hidden']);

    $response = $this->actingAs($actor)
        ->post(route('consumables.bulkedit.show'), [
            'ids' => [$visible->id, $hidden->id],
            'bulk_actions' => 'edit',
        ])
        ->assertOk()
        ->assertViewIs('consumables.bulk-edit');

    $consumables = $response->viewData('consumables');
    expect($consumables->contains('id', $visible->id))->toBeTrue();
    expect($consumables->contains('id', $hidden->id))->toBeFalse();
});

test('bulk update cannot write cross company consumable under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editConsumables()->viewConsumables()->make());

    $target = Consumable::factory()->for($companyB)->create();
    $originalName = $target->name;

    $this->actingAs($actor)
        ->post(route('consumables.bulkedit.store'), [
            'ids' => [$target->id],
            'archived' => '1',
        ]);

    $fresh = $target->fresh();
    expect($fresh->name)->toBe($originalName);
    expect((bool) $fresh->archived)->toBeFalse();
});

test('bulk edit rejects write to cross company company id under fmcs', function () {
    // Even if a non-superuser hand-crafted a POST with a company_id
    // outside their pivot set, the fmcs_company validator should
    // block the write.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editConsumables()->viewConsumables()->make());
    $target = Consumable::factory()->for($companyA)->create();

    $this->actingAs($actor)
        ->post(route('consumables.bulkedit.store'), [
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
    $actor = $companyA->users()->save(User::factory()->editConsumables()->viewConsumables()->make());
    $homeLocation = Location::factory()->for($companyA)->create();
    $target = Consumable::factory()->for($companyA)->create(['location_id' => $homeLocation->id]);
    $foreignLocation = Location::factory()->for($companyB)->create();

    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $this->actingAs($actor)
        ->post(route('consumables.bulkedit.store'), [
            'ids' => [$target->id],
            'location_id' => (string) $foreignLocation->id,
        ]);

    expect($target->fresh()->location_id)->toBe($homeLocation->id);
});

test('single consume cannot touch cross company assignment under fmcs', function () {
    // Mark-consumed is gated on consumables.checkout plus the
    // $consumable route-model binding, which runs through the
    // CompanyableScope. A company A actor should not be able to
    // close out a pivot row attached to a company B consumable.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->viewConsumables()->checkoutConsumables()->make());

    $foreignConsumable = Consumable::factory()->for($companyB)->checkedOutToUser()->create();
    $foreignAssignment = ConsumableAssignment::where('consumable_id', $foreignConsumable->id)->firstOrFail();

    $this->actingAs($actor)
        ->post(route('consumables.consume', [
            'consumable' => $foreignConsumable->id,
            'assignment' => $foreignAssignment->id,
        ]));

    expect($foreignAssignment->fresh()->consumed_at)->toBeNull();
});

test('bulk consume cannot mark cross company assignment under fmcs', function () {
    // bulkConsumeStore resolves the parent consumable from the
    // ConsumableAssignment lookup, then authorizes 'checkout' on
    // it. A company A actor targeting company B assignments
    // should fail the authorize step.
    $this->settings->enableMultipleFullCompanySupport();
    $this->settings->disableFloaterMode();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->viewConsumables()->checkoutConsumables()->make());

    $foreignConsumable = Consumable::factory()->for($companyB)->checkedOutToUser()->create();
    $foreignAssignment = ConsumableAssignment::where('consumable_id', $foreignConsumable->id)->firstOrFail();

    $this->actingAs($actor)
        ->post(route('consumables.bulkconsume.store'), [
            'ids' => [$foreignAssignment->id],
        ])
        ->assertForbidden();

    expect($foreignAssignment->fresh()->consumed_at)->toBeNull();
});

test('bulk consume show blocks cross company assignment under fmcs', function () {
    // The interstitial view runs through the same auth gate as the
    // commit step. A company A actor hand-posting a company B
    // assignment id should either redirect with a warning or be
    // forbidden outright. The pivot row must not surface in the
    // confirmation view either way.
    $this->settings->enableMultipleFullCompanySupport();
    $this->settings->disableFloaterMode();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->viewConsumables()->checkoutConsumables()->make());

    $foreignConsumable = Consumable::factory()->for($companyB)->checkedOutToUser()->create();
    $foreignAssignment = ConsumableAssignment::where('consumable_id', $foreignConsumable->id)->firstOrFail();

    $this->actingAs($actor)
        ->post(route('consumables.bulkconsume.show'), [
            'ids' => [$foreignAssignment->id],
        ])
        ->assertForbidden();

    expect($foreignAssignment->fresh()->consumed_at)->toBeNull();
});

test('bulk consume floater can consume null company assignment', function () {
    // Floater ON: company A actor is allowed to touch null-company
    // (floater) consumables. Pin so a future refactor that over-
    // tightens the subquery does not block a legitimate flow.
    $this->settings->enableFloaterMode();

    $companyA = Company::factory()->create();
    $actor = $companyA->users()->save(User::factory()->viewConsumables()->checkoutConsumables()->make());

    $floater = Consumable::factory()->create(['company_id' => null]);
    $floater->users()->attach(User::factory()->create(), [
        'consumable_id' => $floater->id,
        'created_by' => $actor->id,
        'created_at' => now(),
    ]);
    $assignment = ConsumableAssignment::where('consumable_id', $floater->id)->firstOrFail();

    $this->actingAs($actor)
        ->post(route('consumables.bulkconsume.store'), [
            'ids' => [$assignment->id],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($assignment->fresh()->consumed_at)->not->toBeNull();
});

test('unarchive cannot touch cross company consumable under fmcs', function () {
    // postUnarchive relies on route-model binding (which runs the
    // Consumable CompanyableScope) + authorize('update', $consumable).
    // A cross-tenant target is either scoped out at binding time or
    // forbidden by the policy. Either way the archived bit must
    // not flip.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editConsumables()->viewConsumables()->make());
    $foreign = Consumable::factory()->for($companyB)->create(['archived' => true]);

    $this->actingAs($actor)
        ->post(route('consumables.unarchive', $foreign));

    expect((bool) $foreign->fresh()->archived)->toBeTrue();
});
