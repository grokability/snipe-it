<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class AssetLabelTest extends TestCase
{
    public function testUserWithPermissionsCanAccessPage()
    {
        $assets = Asset::factory()->count(20)->create();
        $id_array = $assets->pluck('id')->toArray();

        $this->actingAs(User::factory()->viewAssets()->create())->post('/hardware/bulkedit', [
            'ids'          => $id_array,
            'bulk_actions'        => 'labels',
        ])->assertStatus(200);
    }

    public function testRedirectOfNoAssetsSelected()
    {
        $id_array = [];
        $this->actingAs(User::factory()->viewAssets()->create())
            ->from(route('hardware.index'))
            ->post('/hardware/bulkedit', [
            'ids'          => $id_array,
            'bulk_actions'        => 'Labels',
        ])->assertStatus(302)
       ->assertRedirect(route('hardware.index'));
    }

}
