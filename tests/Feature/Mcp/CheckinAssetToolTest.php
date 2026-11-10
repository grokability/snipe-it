<?php

namespace Tests\Feature\Mcp;

use App\Events\CheckoutableCheckedIn;
use App\Mcp\Tools\CheckinAssetTool;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class CheckinAssetToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new CheckinAssetTool)->handle(new Request($args));
    }

    public function test_checks_in_asset_by_asset_tag()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        $content = $this->handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'assigned_to' => null,
            'assigned_type' => null,
            'accepted' => null,
        ]);
    }

    public function test_checks_in_asset_by_numeric_id()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        $content = $this->handle(['id' => $asset->id])->getStructuredContent();

        $this->assertTrue($content['success']);
    }

    public function test_returns_error_when_asset_tag_not_found()
    {
        $this->assertTrue($this->handle(['asset_tag' => 'DOES-NOT-EXIST'])->responses()->first()->isError());
    }

    public function test_returns_error_when_asset_not_checked_out()
    {
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle(['asset_tag' => $asset->asset_tag])->responses()->first()->isError());
    }

    public function test_asset_location_resets_to_rtd_location_on_checkin()
    {
        $rtdLocation = Location::factory()->create();
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create([
            'rtd_location_id' => $rtdLocation->id,
        ]);

        $this->handle(['asset_tag' => $asset->asset_tag]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'location_id' => $rtdLocation->id,
        ]);
    }

    public function test_clears_expected_checkin_date_on_checkin()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create([
            'expected_checkin' => now()->addDays(7),
        ]);

        $this->handle(['asset_tag' => $asset->asset_tag]);

        $this->assertNull($asset->fresh()->expected_checkin);
    }

    public function test_fires_checkin_event()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        $this->handle(['asset_tag' => $asset->asset_tag]);

        Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($asset) {
            return $event->checkoutable->id === $asset->id;
        });
    }

    public function test_response_includes_asset_tag_and_location()
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        $content = $this->handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

        $this->assertEquals($asset->asset_tag, $content['asset_tag']);
        $this->assertArrayHasKey('model', $content);
        $this->assertArrayHasKey('location', $content);
    }
}
