<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\GetActivityLogTool;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class GetActivityLogToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args = []): ResponseFactory
    {
        return (new GetActivityLogTool)->handle(new Request($args));
    }

    public function test_returns_activity_log()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $log = new Actionlog;
        $log->item_type = Asset::class;
        $log->item_id = $asset->id;
        $log->action_type = 'update';
        $log->target_id = auth()->id();
        $log->save();

        $content = $this->handle()->getStructuredContent();

        $this->assertArrayHasKey('activity', $content);
        $this->assertArrayHasKey('total', $content);
        $this->assertGreaterThanOrEqual(1, $content['total']);
    }

    public function test_filters_by_action_type()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $log = new Actionlog;
        $log->item_type = Asset::class;
        $log->item_id = $asset->id;
        $log->action_type = 'checkout';
        $log->target_id = auth()->id();
        $log->save();

        $content = $this->handle(['action_type' => 'checkout'])->getStructuredContent();

        $this->assertGreaterThanOrEqual(1, $content['total']);
        foreach ($content['activity'] as $entry) {
            $this->assertEquals('checkout', $entry['action_type']);
        }
    }

    public function test_filters_by_item_id()
    {
        $asset = Asset::factory()->laptopZenbook()->create();
        $log = new Actionlog;
        $log->item_type = Asset::class;
        $log->item_id = $asset->id;
        $log->action_type = 'update';
        $log->target_id = auth()->id();
        $log->save();

        $content = $this->handle([
            'item_id' => $asset->id,
            'item_type' => Asset::class,
        ])->getStructuredContent();

        $this->assertGreaterThanOrEqual(1, $content['total']);
        foreach ($content['activity'] as $entry) {
            $this->assertEquals($asset->id, $entry['item_id']);
        }
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $this->assertTrue($this->handle()->responses()->first()->isError());
    }
}
