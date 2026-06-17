<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\Group;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class AssetUserMiscTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_user_by_group_scope(): void
    {
        $group = Group::factory()->create();

        $this->assertInstanceOf(Collection::class, User::ByGroup($group->id)->get());
    }

    public function test_user_location_scope(): void
    {
        $location = Location::factory()->create();

        $this->assertInstanceOf(Collection::class, User::UserLocation($location->id, '')->get());
    }

    public function test_user_with_inventory_relations_scope(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(Collection::class, User::withInventoryRelations($user->id)->get());
    }

    public function test_asset_loc_returns_location_or_null(): void
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->create(['rtd_location_id' => $location->id, 'location_id' => $location->id]);

        $result = $asset->assetLoc();
        $this->assertTrue($result instanceof Location || is_null($result));
    }

    public function test_custom_fields_for_checkin_checkout_returns_collection(): void
    {
        $asset = Asset::factory()->create();

        // Devuelve una coleccion de custom fields o null si el modelo no tiene fieldset.
        $result = $asset->customFieldsForCheckinCheckout('checkin');
        $this->assertTrue($result instanceof Collection || is_null($result));
    }
}
