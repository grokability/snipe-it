<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\AddAssetNoteTool;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class AddAssetNoteToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editAssets()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new AddAssetNoteTool)->handle(new Request($args));
    }

    public function test_adds_note_by_asset_tag()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'asset_tag' => (string) $asset->asset_tag,
            'note' => 'This is a test note.',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertEquals('This is a test note.', $content['note']);
        $this->assertDatabaseHas('action_logs', [
            'item_id' => $asset->id,
            'action_type' => 'note added',
            'note' => 'This is a test note.',
        ]);
    }

    public function test_adds_note_by_numeric_id()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'id' => $asset->id,
            'note' => 'Note via ID.',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('action_logs', [
            'item_id' => $asset->id,
            'action_type' => 'note added',
            'note' => 'Note via ID.',
        ]);
    }

    public function test_adds_note_by_serial()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'serial' => $asset->serial,
            'note' => 'Note via serial.',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('action_logs', [
            'item_id' => $asset->id,
            'action_type' => 'note added',
        ]);
    }

    public function test_returns_error_when_asset_not_found()
    {
        $this->assertTrue($this->handle(['id' => 999999, 'note' => 'test'])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle(['id' => $asset->id, 'note' => 'test'])->responses()->first()->isError());
        $this->assertDatabaseMissing('action_logs', ['item_id' => $asset->id, 'action_type' => 'note added']);
    }
}
