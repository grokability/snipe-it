<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AssetCheckoutFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function deployableAsset(): Asset
    {
        $status = Statuslabel::factory()->create(['deployable' => 1, 'archived' => 0, 'pending' => 0]);

        return Asset::factory()->create(['assigned_to' => null, 'status_id' => $status->id]);
    }

    public function test_checkout_to_location(): void
    {
        $asset = $this->deployableAsset();
        $location = Location::factory()->create();

        $result = $asset->checkOut($location, null, now(), null, 'to location', 'Asset Name');

        $this->assertNotFalse($result);
        $this->assertEquals(Location::class, $asset->fresh()->assigned_type);
    }

    public function test_checkout_to_asset(): void
    {
        $asset = $this->deployableAsset();
        $targetAsset = $this->deployableAsset();

        $result = $asset->checkOut($targetAsset, null, now(), null, 'to asset');

        $this->assertNotFalse($result);
        $this->assertEquals(Asset::class, $asset->fresh()->assigned_type);
    }

    public function test_checkout_with_expected_checkin(): void
    {
        $asset = $this->deployableAsset();
        $user = User::factory()->create();

        $result = $asset->checkOut($user, null, now(), now()->addMonth()->toDateString(), 'with expected');

        $this->assertNotFalse($result);
        $this->assertNotNull($asset->fresh()->expected_checkin);
    }

    public function test_checkout_to_self_is_rejected(): void
    {
        $asset = $this->deployableAsset();

        $this->expectException(\App\Exceptions\CheckoutNotAllowed::class);
        $asset->checkOut($asset, null, now(), null, 'self');
    }
}
