<?php

namespace Tests\Feature\Checkins\Api;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class AssetCheckinByTagTest extends TestCase
{
    public function test_checking_in_asset_by_tag_requires_correct_permission()
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.asset.checkinbytag'), ['asset_tag' => $asset->asset_tag])
            ->assertForbidden();
    }

    public function test_asset_can_be_checked_in_by_tag()
    {
        $asset = Asset::factory()->assignedToUser()->create();

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkinbytag'), ['asset_tag' => $asset->asset_tag])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertNull($asset->refresh()->assignedTo);
    }

    public function test_checkin_by_tag_returns_error_for_unknown_tag()
    {
        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkinbytag'), ['asset_tag' => 'DOES-NOT-EXIST'])
            ->assertOk()
            ->assertStatusMessageIs('error');
    }

    public function test_asset_name_is_cleared_on_checkin_by_tag_when_clear_name_is_set()
    {
        $asset = Asset::factory()->assignedToUser()->create(['name' => 'My Asset Name']);

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkinbytag'), [
                'asset_tag' => $asset->asset_tag,
                'clear_name' => '1',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertNull($asset->refresh()->name);
    }

    public function test_asset_name_is_not_cleared_on_checkin_by_tag_when_clear_name_is_not_set()
    {
        $asset = Asset::factory()->assignedToUser()->create(['name' => 'My Asset Name']);

        $this->actingAsForApi(User::factory()->checkinAssets()->create())
            ->postJson(route('api.asset.checkinbytag'), ['asset_tag' => $asset->asset_tag])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertEquals('My Asset Name', $asset->refresh()->name);
    }
}
