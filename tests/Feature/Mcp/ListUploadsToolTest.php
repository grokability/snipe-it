<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListUploadsTool;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class ListUploadsToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return (new ListUploadsTool)->handle(new Request($args));
    }

    public function test_returns_uploads_for_asset()
    {
        $asset = Asset::factory()->create();

        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'uploaded',
            'filename' => 'test-document.pdf',
        ]);

        $content = $this->handle(['object_type' => 'assets', 'id' => $asset->id])->getStructuredContent();

        $this->assertEquals(1, $content['total']);
        $this->assertEquals('test-document.pdf', $content['files'][0]['filename']);
    }

    public function test_returns_empty_list_when_no_uploads()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle(['object_type' => 'assets', 'id' => $asset->id])->getStructuredContent();

        $this->assertEquals(0, $content['total']);
        $this->assertEmpty($content['files']);
    }

    public function test_returns_error_when_object_not_found()
    {
        $this->assertTrue(
            $this->handle(['object_type' => 'assets', 'id' => 999999])->responses()->first()->isError()
        );
    }

    public function test_returns_error_when_user_lacks_files_permission()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->create();

        $this->assertTrue(
            $this->handle(['object_type' => 'assets', 'id' => $asset->id])->responses()->first()->isError()
        );
    }

    public function test_supports_user_object_type()
    {
        $user = User::factory()->create();

        $content = $this->handle(['object_type' => 'users', 'id' => $user->id])->getStructuredContent();

        $this->assertEquals('users', $content['object_type']);
        $this->assertEquals($user->id, $content['object_id']);
    }
}
