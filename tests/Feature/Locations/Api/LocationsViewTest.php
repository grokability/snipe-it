<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Asset;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocationsViewTest extends TestCase
{
    public function test_viewing_location_requires_permission()
    {
        $location = Location::factory()->create();
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.locations.show', $location->id))
            ->assertForbidden();
    }

    public function test_viewing_location_asset_index_requires_permission()
    {
        $location = Location::factory()->create();
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.locations.viewassets', $location->id))
            ->assertForbidden();
    }

    public function test_viewing_location_asset_index()
    {
        $location = Location::factory()->create();
        Asset::factory()->count(3)->create(['location_id' => $location->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.locations.viewassets', $location->id))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson([
                'total' => 3,
            ]);
    }

    public function test_viewing_assigned_location_asset_index()
    {
        $location = Location::factory()->create();
        Asset::factory()->count(3)->assignedToLocation($location)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.locations.assigned_assets', $location->id))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson([
                'total' => 3,
            ]);
    }

    /**
     * Regression for #17565. rtd_assets_count in the API used to ignore
     * archived assets while the tab count on the location detail view
     * excluded them, producing mismatched numbers between UI and API.
     * The show endpoint now threads AssetsForShow into the withCount
     * closure so both surfaces agree.
     */
    public function test_show_rtd_assets_count_excludes_archived_when_setting_off()
    {
        $this->settings->set(['show_archived_in_list' => 0]);

        $location = Location::factory()->create();
        $deployableStatus = Statuslabel::factory()->rtd()->create();
        $archivedStatus = Statuslabel::factory()->archived()->create();

        Asset::factory()->count(4)->create([
            'rtd_location_id' => $location->id,
            'status_id' => $deployableStatus->id,
        ]);
        Asset::factory()->count(1)->create([
            'rtd_location_id' => $location->id,
            'status_id' => $archivedStatus->id,
        ]);

        Statuslabel::clearIdCache();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.locations.show', $location->id))
            ->assertOk()
            ->assertJson(['rtd_assets_count' => 4]);
    }

    public function test_show_rtd_assets_count_includes_archived_when_setting_on()
    {
        $this->settings->set(['show_archived_in_list' => 1]);

        $location = Location::factory()->create();
        $deployableStatus = Statuslabel::factory()->rtd()->create();
        $archivedStatus = Statuslabel::factory()->archived()->create();

        Asset::factory()->count(4)->create([
            'rtd_location_id' => $location->id,
            'status_id' => $deployableStatus->id,
        ]);
        Asset::factory()->count(1)->create([
            'rtd_location_id' => $location->id,
            'status_id' => $archivedStatus->id,
        ]);

        Statuslabel::clearIdCache();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.locations.show', $location->id))
            ->assertOk()
            ->assertJson(['rtd_assets_count' => 5]);
    }

    public function test_show_includes_direct_parent_and_children()
    {
        $grandparent = Location::factory()->create();
        $parent = Location::factory()->create(['parent_id' => $grandparent->id]);
        $child = Location::factory()->create(['parent_id' => $parent->id]);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.locations.show', $parent->id))
            ->assertOk()
            ->assertJsonPath('parent.id', $grandparent->id);

        $this->assertEquals([$child->id], array_column($response->json('children'), 'id'));
    }

    public function test_show_does_not_crash_when_location_is_in_a_parent_cycle()
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        DB::table('locations')->where('id', $locationA->id)->update(['parent_id' => $locationB->id]);
        DB::table('locations')->where('id', $locationB->id)->update(['parent_id' => $locationA->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.locations.show', $locationA->id))
            ->assertOk()
            ->assertJsonPath('parent.id', $locationB->id);
    }
}
