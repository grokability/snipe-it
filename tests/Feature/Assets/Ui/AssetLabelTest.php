<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class AssetLabelTest extends TestCase
{
    public function test_user_with_permissions_can_access_page()
    {
        $assets = Asset::factory()->count(20)->create();
        $id_array = $assets->pluck('id')->toArray();

        $this->actingAs(User::factory()->viewAssets()->create())->post('/hardware/bulkedit', [
            'ids' => $id_array,
            'bulk_actions' => 'labels',
        ])->assertStatus(200);
    }

    public function test_redirect_of_no_assets_selected()
    {
        $id_array = [];
        $this->actingAs(User::factory()->viewAssets()->create())
            ->from(route('hardware.index'))
            ->post('/hardware/bulkedit', [
                'ids' => $id_array,
                'bulk_actions' => 'Labels',
            ])->assertStatus(302)
            ->assertRedirect(route('hardware.index'));
    }
}
