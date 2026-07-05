<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\User;
use Tests\TestCase;

class AssetBySerialTest extends TestCase
{
    public function test_returns_asset_by_serial()
    {
        $asset = Asset::factory()->create(['serial' => 'TEST-API-SERIAL-123']);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.show.byserial', ['any' => 'TEST-API-SERIAL-123']))
            ->assertOk()
            ->assertJsonFragment(['serial' => 'TEST-API-SERIAL-123'])
            ->assertJsonStructure(['total', 'rows']);
    }

    public function test_returns_multiple_assets_with_same_serial()
    {
        Asset::factory()->count(3)->create(['serial' => 'DUPE-SERIAL']);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.show.byserial', ['any' => 'DUPE-SERIAL']))
            ->assertOk();

        $this->assertEquals(3, $response->json('total'));
    }

    public function test_returns_error_when_serial_not_found()
    {
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.show.byserial', ['any' => 'DOES-NOT-EXIST']))
            ->assertOk()
            ->assertJson(['status' => 'error']);
    }

    public function test_requires_permission()
    {
        Asset::factory()->create(['serial' => 'TEST-API-SERIAL-AUTH']);

        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.assets.show.byserial', ['any' => 'TEST-API-SERIAL-AUTH']))
            ->assertForbidden();
    }

    public function test_does_not_return_deleted_assets_by_default()
    {
        $asset = Asset::factory()->create(['serial' => 'DELETED-SERIAL']);
        $asset->delete();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.show.byserial', ['any' => 'DELETED-SERIAL']))
            ->assertOk()
            ->assertJson(['status' => 'error']);
    }

    public function test_returns_deleted_assets_when_requested()
    {
        $asset = Asset::factory()->create(['serial' => 'DELETED-SERIAL-2']);
        $asset->delete();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.show.byserial', ['any' => 'DELETED-SERIAL-2']).'?deleted=true')
            ->assertOk()
            ->assertJsonFragment(['serial' => 'DELETED-SERIAL-2']);
    }

    public function test_serial_with_slashes_works_in_path()
    {
        $asset = Asset::factory()->create(['serial' => 'SN/WITH/SLASHES']);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.show.byserial', ['any' => 'SN/WITH/SLASHES']))
            ->assertOk()
            ->assertJsonFragment(['serial' => 'SN/WITH/SLASHES']);
    }
}
