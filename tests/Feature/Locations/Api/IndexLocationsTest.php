<?php

namespace Tests\Feature\Locations\Api;

use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class IndexLocationsTest extends TestCase
{
    public function testViewingLocationIndexRequiresAuthentication()
    {
        $this->getJson(route('api.locations.index'))->assertRedirect();
    }

    public function testViewingLocationIndexRequiresPermission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.locations.index'))
            ->assertForbidden();
    }

    public function testLocationIndexReturnsExpectedLocations()
    {
        Location::factory()->count(3)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.locations.index', [
                    'sort' => 'name',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '20',
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn(AssertableJson $json) => $json->has('rows', 3)->etc());
    }

}
