<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\LocationsTransformer;
use App\Models\AccessoryCheckout;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre LocationsTransformer (antes 56%): transformLocation(s), compact y
 * los checkouts de accesorios.
 */
class LocationsTransformerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_transform_single_location(): void
    {
        $parent = Location::factory()->create();
        $location = Location::factory()->create(['parent_id' => $parent->id]);

        $result = (new LocationsTransformer)->transformLocation($location);

        $this->assertSame($location->id, $result['id']);
        $this->assertArrayHasKey('available_actions', $result);
    }

    public function test_transform_null_location(): void
    {
        $this->assertNull((new LocationsTransformer)->transformLocation(null));
    }

    public function test_transform_locations_collection(): void
    {
        $locations = Location::factory()->count(3)->create();

        $result = (new LocationsTransformer)->transformLocations($locations, $locations->count());

        $this->assertArrayHasKey('rows', $result);
        $this->assertEquals($locations->count(), $result['total']);
    }

    public function test_transform_location_compact(): void
    {
        $location = Location::factory()->create();

        $result = (new LocationsTransformer)->transformLocationCompact($location);

        $this->assertSame($location->id, $result['id']);
    }

    public function test_transform_location_compact_null(): void
    {
        $this->assertNull((new LocationsTransformer)->transformLocationCompact(null));
    }

    public function test_transform_checkedout_accessories(): void
    {
        $checkouts = AccessoryCheckout::factory()->count(2)->create();

        $result = (new LocationsTransformer)->transformCheckedoutAccessories($checkouts, $checkouts->count());

        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('total', $result);
    }
}
