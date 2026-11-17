<?php

namespace Tests\Feature\Assets;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class OrderByJobTitleTest extends TestCase
{
    public function test_location_assigned_asset_does_not_borrow_jobtitle_from_a_coincidentally_numbered_user(): void
    {
        // Seed a user and a location whose IDs happen to collide. The
        // unconstrained leftJoin would match the location-assigned
        // asset against this user's jobtitle. The constrained join
        // leaves it as NULL and sorts it to the end on DESC.
        $sharedId = $this->firstFreeIdSharedBy(User::class, Location::class);

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

        $this->assertSame(
            [
                (int) $zuluAsset->id,
                (int) $alphaAsset->id,
                (int) $locationAsset->id,
            ],
            $ordered->pluck('id')->map(fn($id) => (int) $id)->all(),
            'Location-assigned asset must sort as NULL (last on DESC), not borrow the jobtitle of the user whose id happens to match the location id.',
        );
    }

    public function test_null_jobtitles_sort_last_on_desc(): void
    {
        $titled = User::factory()->create(['jobtitle' => 'Mid-level Something']);
        $untitled = User::factory()->create(['jobtitle' => null]);

        $titledAsset = Asset::factory()->assignedToUser($titled)->create();
        $untitledAsset = Asset::factory()->assignedToUser($untitled)->create();

        $ordered = Asset::OrderByJobTitle('desc')
            ->whereIn('assets.id', [$titledAsset->id, $untitledAsset->id])
            ->get();

        $this->assertSame(
            [(int) $titledAsset->id, (int) $untitledAsset->id],
            $ordered->pluck('id')->map(fn($id) => (int) $id)->all(),
        );
    }

    public function test_order_assigned_location_asset_does_not_borrow_name_from_a_coincidentally_numbered_user(): void
    {
        // Same shape of bug as the OrderByJobTitle case: the assigned-to
        // sort was leftJoining on assets.assigned_to = users_sort.id
        // without the assigned_type guard, so a Location-assigned asset
        // would pick up a user's name as the sort key when the ids
        // coincided. Fix must send location-assigned rows to the end
        // on DESC (NULL last), not interleave them with real users.
        $sharedId = $this->firstFreeIdSharedBy(User::class, Location::class);

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

        $this->assertSame(
            [
                (int) $zuluAsset->id,
                (int) $alphaAsset->id,
                (int) $locationAsset->id,
            ],
            $ordered->pluck('id')->map(fn($id) => (int) $id)->all(),
            'Location-assigned asset must sort as NULL (last on DESC), not borrow the name of the user whose id happens to match the location id.',
        );
    }

    /**
     * Find an id value that both factories can assign so we can create
     * a user and a location with the same numeric id, which is what
     * reproduces the bogus-join sort bug.
     */
    private function firstFreeIdSharedBy(string $classA, string $classB): int
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
}
