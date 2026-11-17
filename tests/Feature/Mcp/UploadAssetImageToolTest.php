<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\UploadAssetImageTool;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class UploadAssetImageToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->editAssets()->create());
        Storage::fake('public');
    }

    private function handle(array $args): ResponseFactory
    {
        return resolve(UploadAssetImageTool::class)->handle(new Request($args));
    }

    private function pngBase64(): string
    {
        // 1x1 red PNG
        return 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';
    }

    public function test_attaches_image_to_asset_by_asset_tag()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'asset_tag' => $asset->asset_tag,
            'image_base64' => $this->pngBase64(),
            'mime_type' => 'image/png',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertNotEmpty($asset->fresh()->image);
    }

    public function test_attaches_image_to_asset_by_numeric_id()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'id' => $asset->id,
            'image_base64' => $this->pngBase64(),
            'mime_type' => 'image/png',
        ])->getStructuredContent();

        $this->assertTrue($content['success']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle([
            'image_base64' => $this->pngBase64(),
            'mime_type' => 'image/png',
        ])->responses()->first()->isError());
    }

    public function test_returns_error_when_asset_not_found()
    {
        $this->assertTrue($this->handle([
            'asset_tag' => 'DOES-NOT-EXIST',
            'image_base64' => $this->pngBase64(),
            'mime_type' => 'image/png',
        ])->responses()->first()->isError());
    }

    public function test_rejects_disallowed_mime_type()
    {
        $asset = Asset::factory()->create();

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->handle([
            'asset_tag' => $asset->asset_tag,
            'image_base64' => $this->pngBase64(),
            'mime_type' => 'application/octet-stream',
        ]);
    }

    public function test_rejects_undecodable_base64()
    {
        $asset = Asset::factory()->create();

        $response = $this->handle([
            'asset_tag' => $asset->asset_tag,
            'image_base64' => '!!!not valid base64!!!',
            'mime_type' => 'image/png',
        ]);

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_returns_error_when_acting_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());
        $asset = Asset::factory()->create();

        $this->assertTrue($this->handle([
            'asset_tag' => $asset->asset_tag,
            'image_base64' => $this->pngBase64(),
            'mime_type' => 'image/png',
        ])->responses()->first()->isError());
    }

    public function test_response_includes_image_filename_and_url()
    {
        $asset = Asset::factory()->create();

        $content = $this->handle([
            'asset_tag' => $asset->asset_tag,
            'image_base64' => $this->pngBase64(),
            'mime_type' => 'image/png',
        ])->getStructuredContent();

        $this->assertArrayHasKey('image', $content);
        $this->assertArrayHasKey('image_url', $content);
        $this->assertNotEmpty($content['image']);
    }
}
