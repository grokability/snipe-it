<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListAssetNotesTool;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListAssetNotesToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->viewAssets()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new ListAssetNotesTool)->handle(new Request($args));
    }

    public function test_returns_notes_for_asset()
    {
        $asset = Asset::factory()->create();
        $author = User::factory()->create();

        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'note added',
            'note' => 'Test note content',
            'created_by' => $author->id,
        ]);

        $content = $this->handle(['id' => $asset->id])->getStructuredContent();

        $this->assertEquals(1, $content['total']);
        $this->assertEquals('Test note content', $content['notes'][0]['note']);
    }

    public function test_returns_empty_list_when_no_notes()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['id' => $asset->id])->getStructuredContent();

        $this->assertEquals(0, $content['total']);
        $this->assertEmpty($content['notes']);
    }

    public function test_resolves_asset_by_asset_tag()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['asset_tag' => (string) $asset->asset_tag])->getStructuredContent();

        $this->assertEquals($asset->id, $content['asset_id']);
    }

    public function test_resolves_asset_by_serial()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['serial' => $asset->serial])->getStructuredContent();

        $this->assertEquals($asset->id, $content['asset_id']);
    }

    public function test_returns_error_when_asset_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_view_permission()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle(['id' => $asset->id])->responses()->first()->isError());
    }
}
