<?php

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;

test('location assigned asset does not borrow jobtitle from a coincidentally numbered user', function () {
    // Seed a user and a location whose IDs happen to collide. The
    // unconstrained leftJoin would match the location-assigned
    // asset against this user's jobtitle. The constrained join
    // leaves it as NULL and sorts it to the end on DESC.
    $sharedId = firstFreeIdSharedBy(User::class, Location::class);

    $decoyUser = User::factory()->create([
        'id' => $sharedId,
        'jobtitle' => 'ZZZ Should Not Leak',
    ]);
    $location = Location::factory()->create([
        'id' => $sharedId,
        'name' => 'Headquarters',
    ]);

    $alphaAssignee = User::factory()->create(['jobtitle' => 'Alpha Analyst']);
    $zuluAssignee = User::factory()->create(['jobtitle' => 'Zulu Zoologist']);

    $alphaAsset = Asset::factory()->assignedToUser($alphaAssignee)->create();
    $zuluAsset = Asset::factory()->assignedToUser($zuluAssignee)->create();
    $locationAsset = Asset::factory()->assignedToLocation($location)->create();

    $ordered = Asset::OrderByJobTitle('desc')
        ->whereIn('assets.id', [$alphaAsset->id, $zuluAsset->id, $locationAsset->id])
        ->get();

    expect($ordered->pluck('id')->map(fn ($id) => (int) $id)->all())->toBe([
        (int) $zuluAsset->id,
        (int) $alphaAsset->id,
        (int) $locationAsset->id,
    ], 'Location-assigned asset must sort as NULL (last on DESC), not borrow the jobtitle of the user whose id happens to match the location id.');
});

test('null jobtitles sort last on desc', function () {
    $titled = User::factory()->create(['jobtitle' => 'Mid-level Something']);
    $untitled = User::factory()->create(['jobtitle' => null]);

    $titledAsset = Asset::factory()->assignedToUser($titled)->create();
    $untitledAsset = Asset::factory()->assignedToUser($untitled)->create();

    $ordered = Asset::OrderByJobTitle('desc')
        ->whereIn('assets.id', [$titledAsset->id, $untitledAsset->id])
        ->get();

    expect($ordered->pluck('id')->map(fn ($id) => (int) $id)->all())->toBe([(int) $titledAsset->id, (int) $untitledAsset->id]);
});

test('order assigned location asset does not borrow name from a coincidentally numbered user', function () {
    // Same shape of bug as the OrderByJobTitle case: the assigned-to
    // sort was leftJoining on assets.assigned_to = users_sort.id
    // without the assigned_type guard, so a Location-assigned asset
    // would pick up a user's name as the sort key when the ids
    // coincided. Fix must send location-assigned rows to the end
    // on DESC (NULL last), not interleave them with real users.
    $sharedId = firstFreeIdSharedBy(User::class, Location::class);

    $decoyUser = User::factory()->create([
        'id' => $sharedId,
        'first_name' => 'ZZZ',
        'last_name' => 'Decoy',
    ]);
    $location = Location::factory()->create([
        'id' => $sharedId,
        'name' => 'Field Office',
    ]);

    $alphaUser = User::factory()->create(['first_name' => 'Alpha', 'last_name' => 'Analyst']);
    $zuluUser = User::factory()->create(['first_name' => 'Zulu', 'last_name' => 'Zoologist']);

    $alphaAsset = Asset::factory()->assignedToUser($alphaUser)->create();
    $zuluAsset = Asset::factory()->assignedToUser($zuluUser)->create();
    $locationAsset = Asset::factory()->assignedToLocation($location)->create();

    $ordered = Asset::OrderAssigned('desc')
        ->whereIn('assets.id', [$alphaAsset->id, $zuluAsset->id, $locationAsset->id])
        ->get();

    expect($ordered->pluck('id')->map(fn ($id) => (int) $id)->all())->toBe([
        (int) $zuluAsset->id,
        (int) $alphaAsset->id,
        (int) $locationAsset->id,
    ], 'Location-assigned asset must sort as NULL (last on DESC), not borrow the name of the user whose id happens to match the location id.');
});

/**
 * Find an id value that both factories can assign so we can create
 * a user and a location with the same numeric id, which is what
 * reproduces the bogus-join sort bug.
 */
function firstFreeIdSharedBy(string $classA, string $classB): int
{
    $candidate = max(
        (int) $classA::max('id'),
        (int) $classB::max('id'),
    ) + 100;

    while (
        $classA::whereKey($candidate)->exists()
        || $classB::whereKey($candidate)->exists()
    ) {
        $candidate++;
    }

    return $candidate;
}
