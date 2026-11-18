<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('requires checkin permission', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => [$asset->id],
        ])
        ->assertForbidden();
});

test('show page requires checkin permission', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('hardware.bulkcheckin.show'))
        ->assertForbidden();
});

test('show page renders', function () {
    $this->actingAs(User::factory()->checkinAssets()->create())
        ->get(route('hardware.bulkcheckin.show'))
        ->assertOk();
});

test('bulk edit routing redirects to bulk checkin show', function () {
    $assets = Asset::factory()->assignedToUser()->count(2)->create();

    $this->actingAs(User::factory()->checkinAssets()->viewAssets()->create())
        ->post(route('hardware.bulkedit.show'), [
            'ids' => $assets->pluck('id')->toArray(),
            'bulk_actions' => 'checkin',
            'sort' => 'id',
            'order' => 'asc',
        ])
        ->assertRedirectToRoute('hardware.bulkcheckin.show');
});

test('can bulk checkin assets', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $user = User::factory()->create();
    $assets = Asset::factory()->assignedToUser($user)->count(2)->create([
        'expected_checkin' => now()->addDay(),
        'accepted' => 'accepted',
    ]);

    $response = $this->actingAs(User::factory()->checkinAssets()->viewAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
        ])
        ->assertStatus(302);

    $assets->each(function (Asset $asset) {
        $fresh = $asset->fresh();
        expect($fresh->assigned_to)->toBeNull();
        expect($fresh->assigned_type)->toBeNull();
        expect($fresh->expected_checkin)->toBeNull();
        expect($fresh->accepted)->toBeNull();
        expect($fresh->last_checkin)->not->toBeNull();
    });

    Event::assertDispatched(CheckoutableCheckedIn::class, 2);

    $this->followRedirects($response)->assertSee('alert-success');
});

test('skips assets not checked out', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $assignedAsset = Asset::factory()->assignedToUser()->create();
    $unassignedAsset = Asset::factory()->create();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => [$assignedAsset->id, $unassignedAsset->id],
        ])
        ->assertStatus(302);

    expect($assignedAsset->fresh()->assigned_to)->toBeNull();
    expect($unassignedAsset->fresh()->id)->not->toBeNull();

    Event::assertDispatched(CheckoutableCheckedIn::class, 1);
});

test('status can be changed upon bulk checkin', function () {
    $rtdStatus = Statuslabel::factory()->readyToDeploy()->create();
    $assets = Asset::factory()->assignedToUser()->count(2)->create();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'status_id' => $rtdStatus->id,
        ]);

    $assets->each(function (Asset $asset) use ($rtdStatus) {
        expect($asset->fresh()->status_id)->toEqual($rtdStatus->id);
    });
});

test('location is set to rtd location upon bulk checkin', function () {
    $rtdLocation = Location::factory()->create();
    $checkedOutLocation = Location::factory()->create();

    $assets = Asset::factory()->assignedToUser()->count(2)->create([
        'location_id' => $checkedOutLocation->id,
        'rtd_location_id' => $rtdLocation->id,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
        ]);

    $assets->each(function (Asset $asset) use ($rtdLocation) {
        expect($asset->fresh()->location_id)->toEqual($rtdLocation->id);
    });
});

test('bulk checkin rejects nonexistent location id', function () {
    $rtdLocation = Location::factory()->create();
    $assets = Asset::factory()->assignedToUser()->count(2)->create(['rtd_location_id' => $rtdLocation->id]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'location_id' => PHP_INT_MAX,
        ])
        ->assertRedirectToRoute('hardware.bulkcheckin.show')
        ->assertSessionHas('error');

    $assets->each(function (Asset $asset) {
        expect($asset->fresh()->assigned_to)->not->toBeNull();
    });
});

test('bulk checkin rejects location from another company under fmcs', function () {
    // Location tenant-scoping under FMCS is opt-in via
    // scope_locations_fmcs (off by default). This assertion depends
    // on that scoping actively hiding the cross-company location,
    // so enable it explicitly.
    $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $foreignLocation = Location::factory()->create(['company_id' => $companyB->id]);
    $rtdLocation = Location::factory()->create(['company_id' => $companyA->id]);
    $assets = Asset::factory()->assignedToUser()->count(2)->create([
        'company_id' => $companyA->id,
        'rtd_location_id' => $rtdLocation->id,
    ]);
    $actor = User::factory()->checkinAssets()->viewAssets()->forCompany($companyA->id)->create();

    $this->actingAs($actor)
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'location_id' => $foreignLocation->id,
        ])
        ->assertRedirectToRoute('hardware.bulkcheckin.show')
        ->assertSessionHas('error');

    $assets->each(function (Asset $asset) {
        expect($asset->fresh()->assigned_to)->not->toBeNull();
    });
});

test('submitted location overrides rtd location without updating default', function () {
    $rtdLocation = Location::factory()->create();
    $submittedLocation = Location::factory()->create();

    $assets = Asset::factory()->assignedToUser()->count(2)->create([
        'rtd_location_id' => $rtdLocation->id,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'location_id' => $submittedLocation->id,
            'update_default_location' => '1',
        ]);

    $assets->each(function (Asset $asset) use ($submittedLocation, $rtdLocation) {
        $fresh = $asset->fresh();
        expect($fresh->location_id)->toEqual($submittedLocation->id);
        expect($fresh->rtd_location_id)->toEqual($rtdLocation->id);
    });
});

test('submitted location updates both current and default when radio set to zero', function () {
    $rtdLocation = Location::factory()->create();
    $submittedLocation = Location::factory()->create();

    $assets = Asset::factory()->assignedToUser()->count(2)->create([
        'rtd_location_id' => $rtdLocation->id,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => $assets->pluck('id')->toArray(),
            'location_id' => $submittedLocation->id,
            'update_default_location' => '0',
        ]);

    $assets->each(function (Asset $asset) use ($submittedLocation) {
        $fresh = $asset->fresh();
        expect($fresh->location_id)->toEqual($submittedLocation->id);
        expect($fresh->rtd_location_id)->toEqual($submittedLocation->id);
    });
});

test('license seats are cleared when checkbox is checked', function () {
    $asset = Asset::factory()->assignedToUser()->create();
    LicenseSeat::factory()->assignedToUser()->for($asset)->create();

    expect($asset->licenseseats->first()->assigned_to)->not->toBeNull();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => [$asset->id],
            'checkin_licenses' => '1',
        ]);

    expect($asset->fresh()->licenseseats->first()->assigned_to)->toBeNull();
});

test('license seats are retained when checkbox is unchecked', function () {
    $asset = Asset::factory()->assignedToUser()->create();
    LicenseSeat::factory()->assignedToUser()->for($asset)->create();

    $originalAssignedTo = $asset->licenseseats->first()->assigned_to;
    expect($originalAssignedTo)->not->toBeNull();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => [$asset->id],
            'checkin_licenses' => '0',
        ]);

    expect($asset->fresh()->licenseseats->first()->assigned_to)->toEqual($originalAssignedTo);
});

test('pending checkout acceptances are cleared upon bulk checkin', function () {
    $asset = Asset::factory()->assignedToUser()->create();
    $acceptance = CheckoutAcceptance::factory()->for($asset, 'checkoutable')->pending()->create();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => [$asset->id],
        ]);

    expect($acceptance->exists())->toBeFalse('Pending acceptance was not deleted upon bulk checkin');
});

test('checkin date and note are passed to event', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $asset = Asset::factory()->assignedToUser()->create();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => [$asset->id],
            'checkin_at' => '2024-06-15',
            'note' => 'bulk checkin note',
        ]);

    Event::assertDispatched(function (CheckoutableCheckedIn $event) {
        return $event->action_date === '2024-06-15'
            && $event->note === 'bulk checkin note';
    });
});

test('child asset locations are updated when checkbox is checked', function () {
    $originalLocation = Location::factory()->create();
    $checkedOutLocation = Location::factory()->create();

    $parentAsset = Asset::factory()->assignedToLocation($checkedOutLocation)->create([
        'location_id' => $checkedOutLocation->id,
        'rtd_location_id' => $originalLocation->id,
    ]);

    $childAsset = Asset::factory()->create([
        'assigned_to' => $parentAsset->id,
        'assigned_type' => Asset::class,
        'location_id' => $checkedOutLocation->id,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => [$parentAsset->id],
            'checkin_child_assets' => '1',
        ]);

    expect($parentAsset->fresh()->location_id)->toEqual($originalLocation->id);
    expect($childAsset->fresh()->location_id)->toEqual($originalLocation->id);
});

test('child asset locations are retained when checkbox is unchecked', function () {
    $originalLocation = Location::factory()->create();
    $checkedOutLocation = Location::factory()->create();

    $parentAsset = Asset::factory()->assignedToLocation($checkedOutLocation)->create([
        'location_id' => $checkedOutLocation->id,
        'rtd_location_id' => $originalLocation->id,
    ]);

    $childAsset = Asset::factory()->create([
        'assigned_to' => $parentAsset->id,
        'assigned_type' => Asset::class,
        'location_id' => $checkedOutLocation->id,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => [$parentAsset->id],
            'checkin_child_assets' => '0',
        ]);

    expect($parentAsset->fresh()->location_id)->toEqual($originalLocation->id);
    expect($childAsset->fresh()->location_id)->toEqual($checkedOutLocation->id);
});

test('origin url is stored when routing to bulk checkin', function () {
    $assets = Asset::factory()->assignedToUser()->count(2)->create();
    $originUrl = route('hardware.index').'?status_type=Deployed';

    $this->actingAs(User::factory()->checkinAssets()->viewAssets()->create())
        ->from($originUrl)
        ->post(route('hardware.bulkedit.show'), [
            'ids' => $assets->pluck('id')->toArray(),
            'bulk_actions' => 'checkin',
            'sort' => 'id',
            'order' => 'asc',
        ])
        ->assertSessionHas('url.intended', $originUrl);
});

test('successful checkin redirects to origin url', function () {
    $asset = Asset::factory()->assignedToUser()->create();
    $originUrl = route('hardware.index').'?status_type=Deployed';

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->withSession(['url.intended' => $originUrl])
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => [$asset->id],
        ])
        ->assertRedirect($originUrl);
});

test('external referer is not stored as intended url', function () {
    $assets = Asset::factory()->assignedToUser()->count(2)->create();

    $this->actingAs(User::factory()->checkinAssets()->viewAssets()->create())
        ->from('https://evil.example.com/phish')
        ->post(route('hardware.bulkedit.show'), [
            'ids' => $assets->pluck('id')->toArray(),
            'bulk_actions' => 'checkin',
            'sort' => 'id',
            'order' => 'asc',
        ])
        ->assertSessionMissing('url.intended');
});

test('successful checkin falls back to hardware index without origin url', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => [$asset->id],
        ])
        ->assertRedirect(route('hardware.index'));
});

test('returns error when no assets selected', function () {
    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.bulkcheckin.store'), [
            'selected_assets' => null,
        ])
        ->assertRedirectToRoute('hardware.bulkcheckin.show')
        ->assertSessionHas('error');
});
