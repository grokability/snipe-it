<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadedFilesInlineTest extends TestCase
{
    private User $user;

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        $this->user = User::factory()->superuser()->create();
        $this->asset = Asset::factory()->create();
    }

    private function seedUpload(string $filename, string $contents): void
    {
        Storage::put('private_uploads/assets/'.$filename, $contents);

        $log = new Actionlog;
        $log->item_id = $this->asset->id;
        $log->item_type = Asset::class;
        $log->action_type = 'uploaded';
        $log->filename = $filename;
        $log->created_by = $this->user->id;
        $log->save();
    }

    public function test_xml_upload_requested_inline_is_forced_to_attachment()
    {
        $this->seedUpload('malicious.xml', '<?xml version="1.0"?><data>test</data>');

        $log = Actionlog::where('filename', 'malicious.xml')->firstOrFail();

        $response = $this->actingAsForApi($this->user)
            ->get(route('api.files.show', [
                'object_type' => 'assets',
                'id' => $this->asset->id,
                'file_id' => $log->id,
                'inline' => 'true',
            ]))
            ->assertOk();

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('attachment', (string) $disposition,
            'XML files must never be served inline via the API — attacker-controlled xml-stylesheet can execute script in-origin.');
    }

    public function test_html_upload_requested_inline_is_forced_to_attachment()
    {
        $this->seedUpload('page.html', '<html><body><script>alert(1)</script></body></html>');

        $log = Actionlog::where('filename', 'page.html')->firstOrFail();

        $response = $this->actingAsForApi($this->user)
            ->get(route('api.files.show', [
                'object_type' => 'assets',
                'id' => $this->asset->id,
                'file_id' => $log->id,
                'inline' => 'true',
            ]))
            ->assertOk();

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('attachment', (string) $disposition);
    }

    public function test_png_upload_requested_inline_is_served_inline_with_nosniff()
    {
        // 1x1 transparent PNG so Storage::mimeType() detects image/png.
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        $this->seedUpload('photo.png', $png);

        $log = Actionlog::where('filename', 'photo.png')->firstOrFail();

        $response = $this->actingAsForApi($this->user)
            ->get(route('api.files.show', [
                'object_type' => 'assets',
                'id' => $this->asset->id,
                'file_id' => $log->id,
                'inline' => 'true',
            ]))
            ->assertOk();

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('inline', (string) $disposition);
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function test_xml_disguised_as_png_extension_is_forced_to_attachment()
    {
        // Extension says png, contents are XML. The MIME cross-check in
        // allowSafeInline must catch this and block the inline response.
        $this->seedUpload('bait.png', '<?xml version="1.0"?><data>test</data>');

        $log = Actionlog::where('filename', 'bait.png')->firstOrFail();

        $response = $this->actingAsForApi($this->user)
            ->get(route('api.files.show', [
                'object_type' => 'assets',
                'id' => $this->asset->id,
                'file_id' => $log->id,
                'inline' => 'true',
            ]))
            ->assertOk();

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('attachment', (string) $disposition);
    }
}
