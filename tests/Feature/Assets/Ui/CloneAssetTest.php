<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class CloneAssetTest extends TestCase
{
    public function test_permission_required_to_clone_asset()
    {
        $asset = Asset::factory()->create();
        $this->actingAs(User::factory()->create())
            ->get(route('clone/hardware', $asset))
            ->assertForbidden();
    }

    public function test_page_can_be_accessed(): void
    {
        $asset = Asset::factory()->create();
        $response = $this->actingAs(User::factory()->createAssets()->create())
            ->get(route('clone/hardware', $asset));
        $response->assertStatus(200);
    }

    public function test_asset_can_be_cloned()
    {
        $asset_to_clone = Asset::factory()->create(['name' => 'Asset to clone']);
        $this->actingAs(User::factory()->createAssets()->create())
            ->get(route('clone/hardware', $asset_to_clone))
            ->assertOk()
            ->assertSee([
                'Asset to clone',
            ], false);
    }
}
