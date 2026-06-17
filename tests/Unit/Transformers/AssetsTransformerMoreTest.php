<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\AssetsTransformer;
use App\Models\Asset;
use App\Models\Component;
use App\Models\ComponentAssignment;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

/**
 * Cubre los metodos de AssetsTransformer que faltaban: requested, compact,
 * componentes asignados y licencias asignadas a un asset.
 */
class AssetsTransformerMoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_transform_requested_asset(): void
    {
        $asset = Asset::factory()->create();

        $result = (new AssetsTransformer)->transformRequestedAsset($asset);

        $this->assertSame($asset->id, $result['id']);
        $this->assertArrayHasKey('custom_fields', $result);
        $this->assertArrayHasKey('available_actions', $result);
    }

    public function test_transform_requested_assets_collection(): void
    {
        $assets = Asset::factory()->count(2)->create();

        $result = (new AssetsTransformer)->transformRequestedAssets($assets, $assets->count());

        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertEquals(2, $result['total']);
    }

    public function test_transform_asset_compact(): void
    {
        $asset = Asset::factory()->create();

        $result = (new AssetsTransformer)->transformAssetCompact($asset);

        $this->assertSame('asset', $result['type']);
        $this->assertSame($asset->id, $result['id']);
    }

    public function test_transform_licenses_checked_to_asset(): void
    {
        $asset = Asset::factory()->create();
        $license = License::factory()->create();
        $seat = LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'asset_id' => $asset->id,
        ]);

        $single = (new AssetsTransformer)->transformLicenseCheckedToAsset($seat);
        $this->assertArrayHasKey('license', $single);
        $this->assertArrayHasKey('available_actions', $single);

        $seats = LicenseSeat::where('asset_id', $asset->id)->get();
        $collection = (new AssetsTransformer)->transformLicensesCheckedToAsset($seats, $seats->count());
        $this->assertArrayHasKey('rows', $collection);
    }

    public function test_transform_checkedout_components(): void
    {
        $asset = Asset::factory()->create();
        $component = Component::factory()->create();

        // Crea la fila pivote components_assets.
        $component->assets()->attach($component->id, [
            'asset_id' => $asset->id,
            'created_by' => auth()->id(),
            'assigned_qty' => 1,
            'note' => 'prueba',
        ]);

        $checkouts = ComponentAssignment::where('asset_id', $asset->id)
            ->with('component')->with('adminuser')->get();

        $this->assertInstanceOf(Collection::class, $checkouts);

        $result = (new AssetsTransformer)->transformCheckedoutComponents($checkouts, $checkouts->count());

        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertEquals(1, $result['total']);
        $this->assertSame('component', $result['rows'][0]['name']['type']);
    }
}
