<?php

use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

beforeEach(function () {
    $this->actingAs(User::factory()->superuser()->create());
});

function handle(array $args): ResponseFactory
{
    return handle(new Request($args));
}

test('dry run returns preview without writing', function () {
    $user = User::factory()->create(['username' => 'leaver']);
    $asset = Asset::factory()->assignedToUser($user)->create();

    $content = handle(['username' => 'leaver'])->getStructuredContent();

    expect($content['dry_run'])->toBeTrue();
    expect($content['preview']['assets_to_checkin'])->toHaveCount(1);

    // Nothing should have changed in the DB
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'assigned_to' => $user->id]);
    expect($user->fresh()->deleted_at)->toBeNull();
});

test('dry run is default when not specified', function () {
    $user = User::factory()->create(['username' => 'leaver']);

    $content = handle(['username' => 'leaver'])->getStructuredContent();

    expect($content['dry_run'])->toBeTrue();
});

test('execute checks in all assigned assets', function () {
    $user = User::factory()->create(['username' => 'leaver']);
    $assetA = Asset::factory()->assignedToUser($user)->create();
    $assetB = Asset::factory()->assignedToUser($user)->create();

    handle(['username' => 'leaver', 'dry_run' => false]);

    expect($assetA->fresh()->assigned_to)->toBeNull();
    expect($assetB->fresh()->assigned_to)->toBeNull();
});

test('execute releases assigned license seats', function () {
    $user = User::factory()->create(['username' => 'leaver']);
    $license = License::factory()->create();
    $seat = LicenseSeat::factory()->create([
        'license_id' => $license->id,
        'assigned_to' => $user->id,
    ]);

    handle(['username' => 'leaver', 'dry_run' => false]);

    expect($seat->fresh()->assigned_to)->toBeNull();
});

test('execute nulls manager id on dependent users', function () {
    $manager = User::factory()->create(['username' => 'themanager']);
    $report = User::factory()->create(['manager_id' => $manager->id]);

    handle(['username' => 'themanager', 'dry_run' => false]);

    expect($report->fresh()->manager_id)->toBeNull();
});

test('execute nulls manager id on dependent locations', function () {
    $manager = User::factory()->create(['username' => 'themanager']);
    $location = Location::factory()->create(['manager_id' => $manager->id]);

    handle(['username' => 'themanager', 'dry_run' => false]);

    expect($location->fresh()->manager_id)->toBeNull();
});

test('execute soft deletes the user', function () {
    $user = User::factory()->create(['username' => 'leaver']);

    handle(['username' => 'leaver', 'dry_run' => false]);

    expect(User::withTrashed()->find($user->id)->deleted_at)->not->toBeNull();
});

test('execute does not touch activated flag directly', function () {
    // Many orgs never set `activated` on real users, so the tool must
    // use soft-delete as the exit signal and not just flip activated=0.
    $user = User::factory()->create(['username' => 'leaver', 'activated' => 0]);

    handle(['username' => 'leaver', 'dry_run' => false]);

    expect(User::withTrashed()->find($user->id)->deleted_at)->not->toBeNull();
});

test('resolves user by first and last name', function () {
    $user = User::factory()->create([
        'first_name' => 'Taylor',
        'last_name' => 'Otwell',
        'username' => 'taylor',
    ]);

    $content = handle(['first_name' => 'Taylor', 'last_name' => 'Otwell'])
        ->getStructuredContent();

    expect($content['dry_run'])->toBeTrue();
    expect($content['preview']['user']['id'])->toBe($user->id);
});

test('returns ambiguous match set when name matches multiple users', function () {
    User::factory()->count(2)->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $content = handle(['first_name' => 'John', 'last_name' => 'Doe'])
        ->getStructuredContent();

    expect($content['success'])->toBeFalse();
    expect($content['error'])->toBe('ambiguous_user');
    expect($content['matches'])->toHaveCount(2);
});

test('returns error when no identifier provided', function () {
    expect(handle(['dry_run' => false])->responses()->first()->isError())->toBeTrue();
});

test('returns error when user not found', function () {
    expect(handle(['username' => 'nobody'])->responses()->first()->isError())->toBeTrue();
});

test('returns error when acting user lacks permission', function () {
    $this->actingAs(User::factory()->create());

    $user = User::factory()->create(['username' => 'leaver']);
    $asset = Asset::factory()->assignedToUser($user)->create();

    expect(handle(['username' => 'leaver'])->responses()->first()->isError())->toBeTrue();

    // No changes made.
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'assigned_to' => $user->id]);
    expect($user->fresh()->deleted_at)->toBeNull();
});

test('preview includes managed users and locations', function () {
    $manager = User::factory()->create(['username' => 'themanager']);
    $report = User::factory()->create(['manager_id' => $manager->id]);
    $location = Location::factory()->create(['manager_id' => $manager->id]);

    $content = handle(['username' => 'themanager'])->getStructuredContent();

    $managedUserIds = array_column($content['preview']['managed_users_to_unparent'], 'id');
    $managedLocationIds = array_column($content['preview']['managed_locations_to_unparent'], 'id');

    expect($managedUserIds)->toContain($report->id);
    expect($managedLocationIds)->toContain($location->id);
});
