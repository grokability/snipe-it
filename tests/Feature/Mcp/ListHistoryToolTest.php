<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListHistoryTool;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListHistoryToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewAssets()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new ListHistoryTool)->handle(new Request($args));
    }

    public function test_returns_history_for_asset()
    {
        $asset = Asset::factory()->create();

        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'update',
        ]);

        $content = $this->handle(['object_type' => 'asset', 'id' => $asset->id])->getStructuredContent();

        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertEquals('asset', $content['object_type']);
        $this->assertEquals($asset->id, $content['object_id']);
    }

    public function test_returns_empty_history_for_new_asset()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['object_type' => 'asset', 'id' => $asset->id])->getStructuredContent();

        $this->assertEquals('asset', $content['object_type']);
        $this->assertIsArray($content['history']);
    }

    public function test_filters_by_action_type()
    {
        $asset = Asset::factory()->create();

        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'update',
        ]);

        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'note added',
            'note' => 'A note',
        ]);

        $content = $this->handle([
            'object_type' => 'asset',
            'id' => $asset->id,
            'action_type' => 'note added',
        ])->getStructuredContent();

        foreach ($content['history'] as $entry) {
            $this->assertEquals('note added', $entry['action_type']);
        }
    }

    public function test_returns_error_when_object_not_found()
    {
        $this->assertTrue(
            $this->handle(['object_type' => 'asset', 'id' => 999999])->responses()->first()->isError()
        );
    }

    public function test_returns_error_when_user_lacks_history_permission()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->create();

        $this->assertTrue(
            $this->handle(['object_type' => 'asset', 'id' => $asset->id])->responses()->first()->isError()
        );
    }

    public function test_supports_user_object_type()
    {
        $this->actingAs(User::factory()->viewUsers()->create());
        $user = User::factory()->create();

        $content = $this->handle(['object_type' => 'user', 'id' => $user->id])->getStructuredContent();

        $this->assertEquals('user', $content['object_type']);
        $this->assertEquals($user->id, $content['object_id']);
    }
}
