<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('checked out asset is checked in and soft deleted', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    expect($asset->assigned_to)->not->toBeNull();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--force' => true])
        ->assertExitCode(0);

    $this->assertSoftDeleted($asset);
    expect($asset->fresh()->assigned_to)->toBeNull();
});

test('unassigned asset is soft deleted without checkin log', function () {
    $asset = Asset::factory()->create();

    expect($asset->assigned_to)->toBeNull();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--force' => true])
        ->assertExitCode(0);

    $this->assertSoftDeleted($asset);
});

test('asset checkin creates action log', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--force' => true, '--no-notifications' => true])
        ->assertExitCode(0);

    $this->assertHasTheseActionLogs($asset, ['create', 'checkin from', 'delete']);
});

test('event is fired for checked out asset when notifications enabled', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    User::factory()->superuser()->create();
    $asset = Asset::factory()->assignedToUser()->create();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--force' => true])
        ->assertExitCode(0);

    Event::assertDispatched(CheckoutableCheckedIn::class, function ($event) use ($asset) {
        return $event->checkoutable->is($asset);
    });
});

test('admin id option is used as checked in by user', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $admin = User::factory()->superuser()->create();
    $asset = Asset::factory()->assignedToUser()->create();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--force' => true, '--admin-id' => $admin->id])
        ->assertExitCode(0);

    Event::assertDispatched(CheckoutableCheckedIn::class, function ($event) use ($admin) {
        return $event->checkedInBy->is($admin);
    });
});

test('invalid admin id returns exit code 1', function () {
    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--force' => true, '--admin-id' => 999999])
        ->assertExitCode(1);
});

test('event is not fired when no notifications option set', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $asset = Asset::factory()->assignedToUser()->create();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--force' => true, '--no-notifications' => true])
        ->assertExitCode(0);

    Event::assertNotDispatched(CheckoutableCheckedIn::class);
});

test('company id option scopes assets', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $assetA = Asset::factory()->for($companyA)->assignedToUser()->create();
    $assetB = Asset::factory()->for($companyB)->assignedToUser()->create();

    $this->artisan('snipeit:checkin-delete-all', [
        '--type' => 'assets',
        '--company-id' => $companyA->id,
        '--force' => true,
        '--no-notifications' => true,
    ])->assertExitCode(0);

    $this->assertSoftDeleted($assetA);
    $this->assertNotSoftDeleted($assetB);
});

test('asset license seats are freed on checkin', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();
    $seat = LicenseSeat::factory()->create(['asset_id' => $asset->id, 'assigned_to' => $user->id]);

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--force' => true, '--no-notifications' => true])
        ->assertExitCode(0);

    expect($seat->fresh()->assigned_to)->toBeNull();
});

test('assigned license seat is checked in and license soft deleted', function () {
    $license = License::factory()->create();
    $seat = LicenseSeat::factory()->for($license)->assignedToUser()->create();

    expect($seat->assigned_to)->not->toBeNull();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'licenses', '--force' => true, '--no-notifications' => true])
        ->assertExitCode(0);

    $this->assertSoftDeleted($license);
    expect($seat->fresh()->assigned_to)->toBeNull();
    expect($seat->fresh()->asset_id)->toBeNull();
});

test('unassigned license is soft deleted', function () {
    $license = License::factory()->create();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'licenses', '--force' => true, '--no-notifications' => true])
        ->assertExitCode(0);

    $this->assertSoftDeleted($license);
});

test('company id option scopes licenses', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $licenseA = License::factory()->for($companyA)->create();
    $licenseB = License::factory()->for($companyB)->create();

    $this->artisan('snipeit:checkin-delete-all', [
        '--type' => 'licenses',
        '--company-id' => $companyA->id,
        '--force' => true,
        '--no-notifications' => true,
    ])->assertExitCode(0);

    $this->assertSoftDeleted($licenseA);
    $this->assertNotSoftDeleted($licenseB);
});

test('checked out accessory is checked in and soft deleted', function () {
    $accessory = Accessory::factory()->checkedOutToUser()->create();

    expect($accessory->checkouts->count())->toEqual(1);

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'accessories', '--force' => true, '--no-notifications' => true])
        ->assertExitCode(0);

    $this->assertSoftDeleted($accessory);
    expect($accessory->fresh()->checkouts->count())->toEqual(0);
});

test('company id option scopes accessories', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $accessoryA = Accessory::factory()->for($companyA)->checkedOutToUser()->create();
    $accessoryB = Accessory::factory()->for($companyB)->checkedOutToUser()->create();

    $this->artisan('snipeit:checkin-delete-all', [
        '--type' => 'accessories',
        '--company-id' => $companyA->id,
        '--force' => true,
        '--no-notifications' => true,
    ])->assertExitCode(0);

    $this->assertSoftDeleted($accessoryA);
    $this->assertNotSoftDeleted($accessoryB);
});

test('checked out component is checked in and soft deleted', function () {
    $component = Component::factory()->checkedOutToAsset()->create();

    $this->assertDatabaseHas('components_assets', ['component_id' => $component->id]);

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'components', '--force' => true, '--no-notifications' => true])
        ->assertExitCode(0);

    $this->assertSoftDeleted($component);
    $this->assertDatabaseMissing('components_assets', ['component_id' => $component->id]);
});

test('company id option scopes components', function () {
    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $componentA = Component::factory()->for($companyA)->checkedOutToAsset()->create();
    $componentB = Component::factory()->for($companyB)->checkedOutToAsset()->create();

    $this->artisan('snipeit:checkin-delete-all', [
        '--type' => 'components',
        '--company-id' => $companyA->id,
        '--force' => true,
        '--no-notifications' => true,
    ])->assertExitCode(0);

    $this->assertSoftDeleted($componentA);
    $this->assertNotSoftDeleted($componentB);
});

test('type option limits processing to specified type', function () {
    $asset = Asset::factory()->assignedToUser()->create();
    $accessory = Accessory::factory()->checkedOutToUser()->create();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--force' => true, '--no-notifications' => true])
        ->assertExitCode(0);

    $this->assertSoftDeleted($asset);
    $this->assertNotSoftDeleted($accessory);
});

test('invalid type returns exit code 1', function () {
    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'invalid', '--force' => true])
        ->assertExitCode(1);
});

test('confirmation is required without force', function () {
    Asset::factory()->create();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets'])
        ->expectsConfirmation('This will check in and soft-delete all [assets] for [all companies]. Continue?', 'no')
        ->assertExitCode(0);

    $this->assertDatabaseCount('assets', 1);
});

test('force option skips confirmation', function () {
    $asset = Asset::factory()->create();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--force' => true, '--no-notifications' => true])
        ->assertExitCode(0);

    $this->assertSoftDeleted($asset);
});

test('dry run does not delete or checkin assets', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($user)->create();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--dry-run' => true])
        ->assertExitCode(0);

    $this->assertNotSoftDeleted($asset);
    expect($asset->fresh()->assigned_to)->not->toBeNull();
});

test('dry run skips confirmation prompt', function () {
    Asset::factory()->create();

    // Would hang waiting for input if confirmation were shown
    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--dry-run' => true])
        ->assertExitCode(0);
});

test('dry run outputs would be messaging', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    $this->artisan('snipeit:checkin-delete-all', ['--type' => 'assets', '--dry-run' => true])
        ->expectsOutputToContain('DRY RUN')
        ->expectsOutputToContain('Would check in asset')
        ->expectsOutputToContain('Would delete asset')
        ->expectsOutputToContain('Dry run complete')
        ->assertExitCode(0);
});

test('custom note is used in action log', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    $this->artisan('snipeit:checkin-delete-all', [
        '--type' => 'assets',
        '--force' => true,
        '--no-notifications' => true,
        '--note' => 'Bulk removal for decommission',
    ])->assertExitCode(0);

    $this->assertDatabaseHas('action_logs', [
        'item_id' => $asset->id,
        'action_type' => 'checkin from',
        'note' => 'Bulk removal for decommission',
    ]);
});
