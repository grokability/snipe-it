<?php

namespace Tests\Feature\Mcp;

use App\Events\CheckoutableCheckedIn;
use App\Mcp\Tools\DeleteAssetTool;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class DeleteAssetToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->deleteAssets()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new DeleteAssetTool)->handle(new Request($args));
    }

    public function test_deletes_asset_by_asset_tag()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['asset_tag' => $asset->asset_tag])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_deletes_asset_by_numeric_id()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['id' => $asset->id])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_deletes_asset_by_serial()
    {
        $asset = Asset::factory()->create(['serial' => 'SN-DELETE-001']);

        $content = $this->handle(['serial' => 'SN-DELETE-001'])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_returns_error_when_asset_not_found()
    {
        $this->assertTrue($this->handle(['asset_tag' => 'DOES-NOT-EXIST'])->responses()->first()->isError());
    }

    public function test_checks_in_asset_before_deleting_when_checked_out()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $user = User::factory()->create();
        $asset = Asset::factory()->assignedToUser($user)->create();

        $this->handle(['asset_tag' => $asset->asset_tag]);

        Event::assertDispatched(CheckoutableCheckedIn::class, function (CheckoutableCheckedIn $event) use ($asset) {
            return $event->checkoutable->id === $asset->id;
        });

        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_deletes_unassigned_asset_without_firing_checkin_event()
    {
        Event::fake([CheckoutableCheckedIn::class]);

        $asset = Asset::factory()->create();

        $this->handle(['asset_tag' => $asset->asset_tag]);

        Event::assertNotDispatched(CheckoutableCheckedIn::class);
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_response_includes_asset_tag()
    {
        $asset = Asset::factory()->create(['asset_tag' => 'TAG-DEL-001']);

        $content = $this->handle(['asset_tag' => 'TAG-DEL-001'])->getStructuredContent();

        $this->assertEquals('TAG-DEL-001', $content['asset_tag']);
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle(['asset_tag' => $asset->asset_tag])->responses()->first()->isError());
        $this->assertNotSoftDeleted($asset);
    }
}
