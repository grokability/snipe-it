<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

test('checking in asset requires correct permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('hardware.checkin.store', [Asset::factory()->assignedToUser()->create()]))
        ->assertForbidden();
});

test('cannot check in asset that is not checked out', function () {
    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [Asset::factory()->create()]))
        ->assertStatus(302)
        ->assertSessionHas('error')
        ->assertRedirect(route('hardware.index'));
});

test('cannot store asset checkin that is not checked out', function () {
    $this->actingAs(User::factory()->checkinAssets()->create())
        ->get(route('hardware.checkin.store', [Asset::factory()->create()]))
        ->assertStatus(302)
        ->assertSessionHas('error')
        ->assertRedirect(route('hardware.index'));
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.checkin.create', Asset::factory()->assignedToUser()->create()))
        ->assertOk();
});

test('requestable toggle is hidden on checkin page without old status selection', function () {
    $deployableStatus = Statuslabel::factory()->readyToDeploy()->create();
    $asset = Asset::factory()->assignedToUser()->create([
        'status_id' => $deployableStatus->id,
    ]);

    $response = $this->actingAs(User::factory()->checkinAssets()->create())
        ->get(route('hardware.checkin.create', $asset))
        ->assertOk();

    $content = $response->getContent();

    $this->assertStringContainsString('id="requestable-wrapper"', $content);
    expect($content)->toMatch('/id="requestable-wrapper"(?:(?!>).)*style="display:\s*none;"/s');
});

test('requestable toggle is hidden on checkin page for non deployable status', function () {
    $nonDeployableStatus = Statuslabel::factory()->create(['deployable' => 0]);
    $asset = Asset::factory()->assignedToUser()->create([
        'status_id' => $nonDeployableStatus->id,
    ]);

    $response = $this->actingAs(User::factory()->checkinAssets()->create())
        ->get(route('hardware.checkin.create', $asset))
        ->assertOk();

    $content = $response->getContent();

    expect($content)->toMatch('/id="requestable-wrapper"(?:(?!>).)*style="display:\s*none;"/s');
});

test('requestable toggle visibility prefers old input status id when present', function () {
    $deployableStatus = Statuslabel::factory()->readyToDeploy()->create();
    $nonDeployableStatus = Statuslabel::factory()->create(['deployable' => 0]);

    $asset = Asset::factory()->assignedToUser()->create([
        'status_id' => $nonDeployableStatus->id,
    ]);

    $responseWithDeployableOldInput = $this->actingAs(User::factory()->checkinAssets()->create())
        ->withSession(['_old_input' => ['status_id' => (string) $deployableStatus->id]])
        ->get(route('hardware.checkin.create', $asset))
        ->assertOk();

    $this->assertDoesNotMatchRegularExpression(
        '/id="requestable-wrapper"(?:(?!>).)*style="display:\s*none;"/s',
        $responseWithDeployableOldInput->getContent()
    );

    $assetWithDeployableStatus = Asset::factory()->assignedToUser()->create([
        'status_id' => $deployableStatus->id,
    ]);

    $responseWithNonDeployableOldInput = $this->actingAs(User::factory()->checkinAssets()->create())
        ->withSession(['_old_input' => ['status_id' => (string) $nonDeployableStatus->id]])
        ->get(route('hardware.checkin.create', $assetWithDeployableStatus))
        ->assertOk();

    expect($responseWithNonDeployableOldInput->getContent())->toMatch('/id="requestable-wrapper"(?:(?!>).)*style="display:\s*none;"/s');
});

test('asset can be checked in', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $user = User::factory()->create();
    $location = Location::factory()->create();
    $status = Statuslabel::first() ?? Statuslabel::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create([
        'expected_checkin' => now()->addDay(),
        'last_checkin' => null,
        'accepted' => 'accepted',
    ]);

    expect($asset->assignedTo->is($user))->toBeTrue();

    $currentTimestamp = now();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(
            route('hardware.checkin.store', [$asset]),
            [
                'name' => 'Changed Name',
                'status_id' => $status->id,
                'location_id' => $location->id,
            ],
        );

    expect($asset->refresh()->assignedTo)->toBeNull();
    expect($asset->expected_checkin)->toBeNull();
    expect($asset->last_checkin)->not->toBeNull();
    expect($asset->assignedTo)->toBeNull();
    expect($asset->assigned_type)->toBeNull();
    expect($asset->accepted)->toBeNull();
    expect($asset->name)->toEqual('Changed Name');
    expect($asset->status_id)->toEqual($status->id);
    expect($asset->location()->is($location))->toBeTrue();

    Event::assertDispatched(function (CheckoutableCheckedIn $event) use ($currentTimestamp) {
        // this could be better mocked but is ok for now.
        return (int) Carbon::parse($event->action_date)->diffInSeconds($currentTimestamp, true) < 2;
    }, 1);
});

test('location is set to rtd location by default upon checkin', function () {
    $rtdLocation = Location::factory()->create();
    $asset = Asset::factory()->assignedToUser()->create([
        'location_id' => Location::factory()->create()->id,
        'rtd_location_id' => $rtdLocation->id,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [$asset]));

    expect($asset->refresh()->location()->is($rtdLocation))->toBeTrue();
    $this->assertHasTheseActionLogs($asset, ['create', 'checkin from']);
});

/**
 * Regression coverage for #19401. Pre-fix, submitting the checkin form
 * with the location dropdown blank (which is how a browser submits an
 * empty select) wiped the asset's current location to null instead of
 * resetting it to rtd_location_id like the field's own helper text
 * implied. The new form ships both pickers pre-populated with
 * rtd_location_id, so a no-touch submit sends both values back and
 * the asset lands at rtd. This test posts the same shape as the browser
 * would after a no-touch submit.
 */
test('checkin with prepopulated location lands at rtd not null', function () {
    $rtdLocation = Location::factory()->create();
    $userLocation = Location::factory()->create();
    $asset = Asset::factory()->assignedToUser()->create([
        'location_id' => $userLocation->id,
        'rtd_location_id' => $rtdLocation->id,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [$asset]), [
            'location_id' => $rtdLocation->id,
            'rtd_location_id' => $rtdLocation->id,
        ]);

    $fresh = $asset->refresh();
    expect($fresh->location_id)->not->toBeNull('Current location must not be wiped to null on checkin (#19401).');
    expect($fresh->location()->is($rtdLocation))->toBeTrue('Current location should land at rtd_location on default checkin.');
    expect($fresh->defaultLoc()->is($rtdLocation))->toBeTrue('Default location should be unchanged when picker submitted with same rtd value.');
});

test('checkin clears location when picker submitted empty', function () {
    $rtdLocation = Location::factory()->create();
    $asset = Asset::factory()->assignedToUser()->create([
        'location_id' => Location::factory()->create()->id,
        'rtd_location_id' => $rtdLocation->id,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [$asset]), [
            'location_id' => '',
            'rtd_location_id' => $rtdLocation->id,
        ]);

    $fresh = $asset->refresh();
    expect($fresh->location_id)->toBeNull('Explicit empty submission of the picker should clear the location.');
    expect($fresh->defaultLoc()->is($rtdLocation))->toBeTrue('Default location should be untouched.');
});

test('checkin rejects nonexistent location id', function () {
    $rtdLocation = Location::factory()->create();
    $asset = Asset::factory()->assignedToUser()->create(['rtd_location_id' => $rtdLocation->id]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [$asset]), [
            'location_id' => PHP_INT_MAX,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($asset->fresh()->assigned_to)->not->toBeNull();
});

test('checkin rejects location from another company under fmcs', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $foreignLocation = Location::factory()->create(['company_id' => $companyB->id]);
    $rtdLocation = Location::factory()->create(['company_id' => $companyA->id]);
    $asset = Asset::factory()->assignedToUser()->create([
        'company_id' => $companyA->id,
        'rtd_location_id' => $rtdLocation->id,
    ]);
    $actor = User::factory()->checkinAssets()->viewAssets()->forCompany($companyA->id)->create();

    $this->actingAs($actor)
        ->post(route('hardware.checkin.store', [$asset]), [
            'location_id' => $foreignLocation->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($asset->fresh()->assigned_to)->not->toBeNull();
});

test('default location can be updated upon checkin', function () {
    $location = Location::factory()->create();
    $asset = Asset::factory()->assignedToUser()->create();

    // Post-#19401 the checkin form ships both `location_id` and
    // `rtd_location_id` as independent pickers, so updating the default
    // location just means submitting the desired value on that field.
    // Replaces the older `update_default_location=0` flag semantic.
    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [$asset]), [
            'location_id' => $location->id,
            'rtd_location_id' => $location->id,
        ]);

    expect($asset->refresh()->defaultLoc()->is($location))->toBeTrue();
});

test('assets license seats are cleared upon checkin', function () {
    $asset = Asset::factory()->assignedToUser()->create();
    LicenseSeat::factory()->assignedToUser()->for($asset)->create();

    expect($asset->licenseseats->first()->assigned_to)->not->toBeNull();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [$asset]));

    expect($asset->refresh()->licenseseats->first()->assigned_to)->toBeNull();
});

test('checking in asset updates location of assets assigned to it', function () {
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
        'rtd_location_id' => $originalLocation->id,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [$parentAsset]), [
            'location_id' => $originalLocation->id,
        ]);

    expect($parentAsset->fresh()->location_id)->toEqual($originalLocation->id);
    expect($childAsset->fresh()->location_id)->toEqual($originalLocation->id);
    expect($childAsset->fresh()->assigned_to)->toEqual($parentAsset->id);
    expect($childAsset->fresh()->assigned_type)->toEqual(Asset::class);
});

test('legacy location values set to zero are updated', function () {
    $asset = Asset::factory()->canBeInvalidUponCreation()->assignedToUser()->create([
        'rtd_location_id' => 0,
        'location_id' => 0,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [$asset]));

    expect($asset->refresh()->rtd_location_id)->toBeNull();
    expect($asset->rtd_location_id)->toEqual($asset->location_id);
});

test('pending checkout acceptances are cleared upon checkin', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    $acceptance = CheckoutAcceptance::factory()->for($asset, 'checkoutable')->pending()->create();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [$asset]));

    expect($acceptance->exists())->toBeFalse('Acceptance was not deleted');
});

test('checkin time and action log note can be set', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route(
            'hardware.checkin.store', [Asset::factory()->assignedToUser()->create()]
        ), [
            'checkin_at' => '2023-01-02',
            'note' => 'hello',
        ]);

    Event::assertDispatched(function (CheckoutableCheckedIn $event) {
        return $event->action_date === '2023-01-02' && $event->note === 'hello';
    }, 1);
});

test('checkin can set asset to requestable when status is deployable', function () {
    $deployableStatus = Statuslabel::factory()->readyToDeploy()->create();
    $asset = Asset::factory()->assignedToUser()->create([
        'requestable' => 0,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [$asset]), [
            'status_id' => $deployableStatus->id,
            'requestable' => 1,
        ]);

    expect((bool) $asset->fresh()->requestable)->toBeTrue();

    $log = Actionlog::query()
        ->where('item_type', Asset::class)
        ->where('item_id', $asset->id)
        ->where('action_type', 'checkin from')
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->log_meta)->not->toBeNull();

    $logMeta = json_decode($log->log_meta, true);
    expect($logMeta)->toHaveKey('requestable');
    expect((int) $logMeta['requestable']['old'])->toEqual(0);
    expect((int) $logMeta['requestable']['new'])->toEqual(1);
});

test('checkin does not set asset to requestable when status is not deployable', function () {
    $undeployableStatus = Statuslabel::factory()->create(['deployable' => 0]);
    $asset = Asset::factory()->assignedToUser()->create([
        'requestable' => 0,
    ]);

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', [$asset]), [
            'status_id' => $undeployableStatus->id,
            'requestable' => 1,
        ]);

    expect((bool) $asset->fresh()->requestable)->toBeFalse();
});

test('asset checkin page is redirected if model is invalid', function () {
    $asset = Asset::factory()->assignedToUser()->create();
    $asset->model_id = 0;
    $asset->forceSave();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('hardware.checkin.create', [$asset]))
        ->assertStatus(302)
        ->assertSessionHas('error')
        ->assertRedirect(route('hardware.show', $asset));
});

test('asset checkin page post is redirected if model is invalid', function () {
    $asset = Asset::factory()->assignedToUser()->create();
    $asset->model_id = 0;
    $asset->forceSave();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('hardware.checkin.store', $asset))
        ->assertStatus(302)
        ->assertSessionHas('error')
        ->assertRedirect(route('hardware.show', $asset));
});

test('asset checkin page post is redirected if redirect selection is index', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('hardware.index'))
        ->post(route('hardware.checkin.store', $asset), [
            'redirect_option' => 'index',
        ])
        ->assertStatus(302)
        ->assertRedirect(route('hardware.index'));
});

test('asset checkin page post is redirected if redirect selection is item', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('hardware.index'))
        ->post(route('hardware.checkin.store', $asset), [
            'redirect_option' => 'item',
        ])
        ->assertStatus(302)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('hardware.show', $asset));
});

test('deleted checked out asset checkin page renders', function () {
    $asset = Asset::factory()->deleted()->assignedToUser()->create();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->get(route('hardware.checkin.create', $asset))
        ->assertOk();
});

test('deleted checked out asset can be checked in', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $user = User::factory()->create();
    $asset = Asset::factory()->deleted()->assignedToUser($user)->create();

    expect($asset->assignedTo->is($user))->toBeTrue();
    expect($asset->deleted_at)->not->toBeNull();

    $this->actingAs(User::factory()->checkinAssets()->create())
        ->post(route('hardware.checkin.store', $asset))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $asset->refresh();

    expect($asset->assignedTo)->toBeNull();
    expect($asset->assigned_to)->toBeNull();
    expect($asset->deleted_at)->not->toBeNull('Asset should remain deleted after checkin');

    Event::assertDispatched(CheckoutableCheckedIn::class);
});
