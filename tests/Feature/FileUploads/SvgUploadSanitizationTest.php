<?php

namespace Tests\Feature\FileUploads;

use App\Http\Requests\UploadFileRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression guard for SVG upload sanitization.
 *
 * `UploadFileRequest::handleFile` detects `image/svg+xml` via
 * server-side finfo and pipes the content through
 * `enshrined/svg-sanitize` before writing to disk. That's what stops
 * a stored-XSS via inline SVG rendering (the download endpoint
 * serves .svg with Content-Disposition: inline when the extension
 * and detected MIME both match).
 *
 * External report against a June 2026 master snapshot suggested the
 * inline-SVG XSS was still exploitable via <script> or onload=""
 * payloads. It isn't. The upload-time sanitizer strips both shapes
 * before the file reaches disk, but the report was accurate about
 * the inline-serving path, so this test locks in the upload-time
 * sanitization so a future refactor can't quietly remove the SVG
 * branch and reintroduce the exploit chain.
 */
class SvgUploadSanitizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    private function realUpload(string $clientName, string $content): UploadedFile
    {
        // A real temp file is required for finfo to sniff the MIME
        // correctly. UploadedFile::fake() derives its mime from the
        // filename, which would trivially "pass" the SVG detection
        // without exercising the real sanitize path.
        $path = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'snipeit_svg_'));
        file_put_contents($path, $content);

        return new UploadedFile($path, $clientName, null, null, true);
    }

    public function test_svg_upload_strips_inline_script_element(): void
    {
        $malicious = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">
  <script type="text/javascript">alert(document.domain)</script>
  <rect width="100" height="100" fill="red"/>
</svg>
SVG;

        $upload = $this->realUpload('malicious.svg', $malicious);

        $storedName = (new UploadFileRequest)->handleFile('private_uploads/assets/', 'asset-1', $upload);
        $storedContents = Storage::get('private_uploads/assets/'.$storedName);

        $this->assertStringNotContainsString('<script', $storedContents, 'Sanitizer must strip <script> elements from uploaded SVGs.');
        $this->assertStringNotContainsString('alert(', $storedContents, 'Sanitizer must strip script bodies from uploaded SVGs.');
        // Legitimate SVG content should survive so the file is still
        // a usable image after sanitization.
        $this->assertStringContainsString('<rect', $storedContents);
    }

    public function test_svg_upload_strips_onload_event_handler(): void
    {
        $malicious = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" onload="alert(document.domain)">
  <rect width="100" height="100" fill="blue"/>
</svg>
SVG;

        $upload = $this->realUpload('onload.svg', $malicious);

        $storedName = (new UploadFileRequest)->handleFile('private_uploads/assets/', 'asset-1', $upload);
        $storedContents = Storage::get('private_uploads/assets/'.$storedName);

        $this->assertStringNotContainsString('onload', $storedContents, 'Sanitizer must strip on* event handlers from uploaded SVGs.');
        $this->assertStringNotContainsString('alert(', $storedContents);
        $this->assertStringContainsString('<rect', $storedContents);
    }

    public function test_clean_svg_upload_passes_through_unchanged_content(): void
    {
        // Non-regression: legitimate SVGs must still be usable.
        $clean = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50">
  <circle cx="25" cy="25" r="20" fill="green"/>
</svg>
SVG;

        $upload = $this->realUpload('logo.svg', $clean);

        $storedName = (new UploadFileRequest)->handleFile('private_uploads/assets/', 'asset-1', $upload);
        $storedContents = Storage::get('private_uploads/assets/'.$storedName);

        $this->assertStringContainsString('<circle', $storedContents);
        $this->assertStringContainsString('fill="green"', $storedContents);
    }
}
