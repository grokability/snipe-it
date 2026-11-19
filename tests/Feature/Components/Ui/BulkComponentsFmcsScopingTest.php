<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\ComponentAssignment;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;

function pivotId(Component $component, Asset $asset): int
{
    $component->assets()->attach($asset->id, [
        'assigned_qty' => 1,
        'created_at' => now(),
        'created_by' => 1,
    ]);

    return (int) ComponentAssignment::where('component_id', $component->id)
        ->where('asset_id', $asset->id)
        ->latest('id')
        ->value('id');
}

test('bulk delete cannot delete cross company component under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editComponents()->deleteComponents()->viewComponents()->make());
    $target = Component::factory()->for($companyB)->create();

    $this->actingAs($actor)
        ->post(route('components.bulkdelete.store'), ['ids' => [$target->id]])
        ->assertRedirect(route('components.index'));

    expect($target->fresh()->deleted_at)->toBeNull();
});

test('bulk edit dispatcher does not leak cross company component under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->viewComponents()->editComponents()->checkinComponents()->make());

    $visible = Component::factory()->for($companyA)->create(['name' => 'Visible']);
    $hidden = Component::factory()->for($companyB)->create(['name' => 'Hidden']);

    $response = $this->actingAs($actor)
        ->post(route('components.bulkedit.show'), [
            'ids' => [$visible->id, $hidden->id],
            'bulk_actions' => 'edit',
        ])
        ->assertOk()
        ->assertViewIs('components.bulk-edit');

    $components = $response->viewData('components');
    expect($components->contains('id', $visible->id))->toBeTrue();
    expect($components->contains('id', $hidden->id))->toBeFalse();
});

test('bulk update cannot write cross company component under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editComponents()->viewComponents()->make());

    $target = Component::factory()->for($companyB)->create();
    $originalName = $target->name;

    $this->actingAs($actor)
        ->post(route('components.bulkedit.store'), [
            'ids' => [$target->id],
            'archived' => '1',
        ])
        ->assertRedirect(route('components.index'));

    $fresh = $target->fresh();
    expect($fresh->name)->toBe($originalName);
    expect((bool) $fresh->archived)->toBeFalse();
});

test('bulk checkin cannot check in cross company pivot under fmcs', function () {
    // Floater off: company A actor should not be able to check in
    // pivots whose component belongs to company B, even if they
    // post the pivot id by hand (the row isn't in their table).
    Event::fake([CheckoutableCheckedIn::class]);

    $this->settings->enableMultipleFullCompanySupport();
    $this->settings->disableFloaterMode();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->viewComponents()->editComponents()->checkinComponents()->make());

    $foreignComponent = Component::factory()->for($companyB)->create(['qty' => 5]);
    $pivot = pivotId($foreignComponent, Asset::factory()->create());

    $this->actingAs($actor)
        ->post(route('components.bulkcheckin.store'), ['ids' => [$pivot]])
        ->assertRedirect();

    $this->assertDatabaseHas('components_assets', ['id' => $pivot]);
    Event::assertNothingDispatched();
});

test('bulk checkin show does not leak cross company pivot under fmcs', function () {
    // The interstitial view must also filter: posting a cross-tenant
    // id to .show should redirect with warning (empty after scoping)
    // instead of rendering the foreign pivot in the confirmation UI.
    $this->settings->enableMultipleFullCompanySupport();
    $this->settings->disableFloaterMode();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->viewComponents()->checkinComponents()->make());

    $foreignComponent = Component::factory()->for($companyB)->create(['qty' => 5]);
    $pivot = pivotId($foreignComponent, Asset::factory()->create());

    $this->actingAs($actor)
        ->post(route('components.bulkcheckin.show'), ['ids' => [$pivot]])
        ->assertRedirect()
        ->assertSessionHas('warning');
});

test('bulk edit rejects write to cross company company id under fmcs', function () {
    // Even if a non-superuser hand-crafted a POST with a company_id
    // outside their pivot set, the fmcs_company validator + policy
    // layer should block the write. Pin so the picker-level
    // filtering can't be the only defense.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editComponents()->viewComponents()->make());
    $target = Component::factory()->for($companyA)->create();

    $this->actingAs($actor)
        ->post(route('components.bulkedit.store'), [
            'ids' => [$target->id],
            'company_id' => (string) $companyB->id,
        ])
        ->assertRedirect(route('components.index'));

    expect($target->fresh()->company_id)->toBe($companyA->id);
});

test('bulk edit rejects cross company location under scope locations fmcs', function () {
    // scope_locations_fmcs=1 means a location "belongs" to the
    // companies in its parent chain. fmcs_location validator must
    // reject a bulk-edit that pairs location X with company Y when
    // X doesn't live in Y.
    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $actor = $companyA->users()->save(User::factory()->editComponents()->viewComponents()->make());
    $homeLocation = Location::factory()->for($companyA)->create();
    $target = Component::factory()->for($companyA)->create(['location_id' => $homeLocation->id]);
    $foreignLocation = Location::factory()->for($companyB)->create();

    // Flip scope_locations_fmcs AFTER factory setup so cross-company
    // location-vs-company validation is in force only for the actual
    // bulk-update request, not during the test's own data seeding.
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    $this->actingAs($actor)
        ->post(route('components.bulkedit.store'), [
            'ids' => [$target->id],
            'location_id' => (string) $foreignLocation->id,
        ])
        ->assertRedirect(route('components.index'));

    expect($target->fresh()->location_id)->toBe($homeLocation->id);
});

test('bulk checkin floater can checkin null company pivot', function () {
    // Floater ON: company A actor is allowed to touch null-company
    // (floater) components. Pin so a future refactor that over-
    // tightens the subquery doesn't block a legitimate flow.
    Event::fake([CheckoutableCheckedIn::class]);

    $this->settings->enableFloaterMode();

    $companyA = Company::factory()->create();
    $actor = $companyA->users()->save(User::factory()->viewComponents()->editComponents()->checkinComponents()->make());

    $floater = Component::factory()->create(['company_id' => null, 'qty' => 5]);
    $pivot = pivotId($floater, Asset::factory()->create());

    $this->actingAs($actor)
        ->post(route('components.bulkcheckin.store'), ['ids' => [$pivot]])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('components_assets', ['id' => $pivot]);
    Event::assertDispatched(CheckoutableCheckedIn::class);
});
