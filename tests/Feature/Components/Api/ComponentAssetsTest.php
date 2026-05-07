<?php

namespace Tests\Feature\Components\Api;

use App\Models\Asset;
use App\Models\Component;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ComponentAssetsTest extends TestCase
{
    public function test_requires_permission()
    {
        $component = Component::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.components.assets', $component))
            ->assertForbidden();
    }

    public function test_can_search_assets_assigned_to_specific_component()
    {
        $component = Component::factory()->create();
        $user = User::factory()->viewAssets()->create();

        $matchingAsset = Asset::factory()->create([
            'name' => 'Laptop 1331',
            'asset_tag' => 'ASSET-1331',
        ]);

        $nonMatchingAsset = Asset::factory()->create([
            'name' => 'Laptop 9999',
            'asset_tag' => 'ASSET-9999',
        ]);

        $component->assets()->attach($matchingAsset->id, [
            'component_id' => $component->id,
            'asset_id' => $matchingAsset->id,
            'assigned_qty' => 2,
            'created_at' => now(),
            'created_by' => $user->id,
        ]);

        $component->assets()->attach($nonMatchingAsset->id, [
            'component_id' => $component->id,
            'asset_id' => $nonMatchingAsset->id,
            'assigned_qty' => 1,
            'created_at' => now(),
            'created_by' => $user->id,
        ]);

        $this->actingAsForApi($user)
            ->getJson(route('api.components.assets', $component).'?search=1331')
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($matchingAsset) {
                $json->where('total', 1)
                    ->count('rows', 1)
                    ->where('rows.0.name.id', $matchingAsset->id)
                    ->where('rows.0.assigned_qty', 2)
                    ->etc();
            });
    }
}
