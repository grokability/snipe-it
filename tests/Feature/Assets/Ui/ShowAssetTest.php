<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class ShowAssetTest extends TestCase
{
    public function test_permission_required_to_view_asset()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('hardware.show', Asset::factory()->create()))
            ->assertForbidden();
    }

    public function test_can_view_asset()
    {
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->viewAssets()->create())
            ->get(route('hardware.show', $asset))
            ->assertSeeText($asset->asset_tag)
            ->assertOk();
    }

    public function test_page_for_asset_with_missing_model_still_renders()
    {
        $asset = Asset::factory()->create();

        $asset->model_id = null;
        $asset->forceSave();

        $asset->refresh();

        $this->assertNull($asset->fresh()->model_id, 'This test needs model_id to be null to be helpful.');

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('hardware.show', $asset))
            ->assertOk();
    }
}
