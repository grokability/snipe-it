<?php

namespace Tests\Feature\Assets\Ui;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class AssetBySerialTest extends TestCase
{
    public function test_redirects_to_asset_when_serial_in_path()
    {
        $asset = Asset::factory()->create(['serial' => 'TEST-SERIAL-123']);
        $user = User::factory()->viewAssets()->create();

        $this->actingAs($user)
            ->get(route('findbyserial/hardware', ['any' => 'TEST-SERIAL-123']))
            ->assertRedirectToRoute('hardware.show', $asset->id);
    }

    public function test_redirects_to_asset_when_serial_in_query_string()
    {
        $asset = Asset::factory()->create(['serial' => 'TEST-SERIAL-456']);
        $user = User::factory()->viewAssets()->create();

        $this->actingAs($user)
            ->get(route('findbyserial/hardware').'?serial=TEST-SERIAL-456')
            ->assertRedirectToRoute('hardware.show', $asset->id);
    }

    public function test_redirects_to_index_when_serial_not_found()
    {
        $user = User::factory()->viewAssets()->create();

        $this->actingAs($user)
            ->get(route('findbyserial/hardware', ['any' => 'DOES-NOT-EXIST']))
            ->assertRedirectToRoute('hardware.index');
    }

    public function test_requires_permission()
    {
        Asset::factory()->create(['serial' => 'TEST-SERIAL-789']);

        $this->actingAs(User::factory()->create())
            ->get(route('findbyserial/hardware', ['any' => 'TEST-SERIAL-789']))
            ->assertForbidden();
    }
}
