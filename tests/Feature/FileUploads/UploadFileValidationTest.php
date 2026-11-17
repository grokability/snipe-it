<?php

namespace Tests\Feature\FileUploads;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\License;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

// Regression coverage for issues #12460 and #10387: legitimate uploads
// that were rejected because Laravel's built-in `mimes:` rule (and the
// hand-rolled MIME allowlist in the CSV importer) rely on finfo content
// sniffing that misidentifies ordinary files. Fake UploadedFiles bypass
// finfo entirely (their getMimeType() reads MimeType::from($name)), so
// these tests use real temp files.
class UploadFileValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    private function realUpload(string $clientName, string $content): UploadedFile
    {
        $path = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'snipeit_upload_'));
        file_put_contents($path, $content);

        return new UploadedFile($path, $clientName, null, null, true);
    }

    // Issue #12460 TechWilk reproduction: plain-text .txt whose bytes
    // trigger libmagic's INI heuristic (leading `;`, tab-separated
    // values). Before the fix, UploadFileRequest's `mimes:txt,...` rule
    // rejected this because finfo returned application/x-wine-extension-ini
    // and Symfony's guesser had no reverse mapping to `txt`.
    #[Test]
    public function accepts_txt_file_that_libmagic_misidentifies_as_ini(): void
    {
        $license = License::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->post(
                route('api.files.store', ['object_type' => 'licenses', 'id' => $license->id]),
                ['file' => [$this->realUpload('sample.txt', ";Bob[A]\tSmith[B]\r\n50\t0.8")]]
            )
            ->assertOk();

        $log = Actionlog::where('item_id', $license->id)
            ->where('item_type', License::class)
            ->where('action_type', 'uploaded')
            ->latest('id')
            ->firstOrFail();

        // Stored filename must still carry a .txt extension. Before the
        // handleFile fallback, guessExtension() returned null on this
        // input and the stored name ended in a bare "." with no
        // extension, breaking the eventual download.
        $this->assertStringEndsWith('.txt', $log->filename);
    }

    // Issue #12460 primary: empty .txt file. finfo returns
    // application/x-empty for zero-byte files.
    #[Test]
    public function accepts_empty_txt_file(): void
    {
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->post(
                route('api.files.store', ['object_type' => 'assets', 'id' => $asset->id]),
                ['file' => [$this->realUpload('empty.txt', '')]]
            )
            ->assertOk();
    }

    // The extension allowlist is still authoritative: an .exe rename
    // must not slip past just because we deferred to the client
    // extension. Sniff returns application/x-dosexec which reverse-maps
    // to `exe`, and `exe` is not on the extensions allowlist.
    #[Test]
    public function still_rejects_files_whose_extension_is_not_on_the_allowlist(): void
    {
        $asset = Asset::factory()->create();

        $peHeader = "MZ\x90\x00\x03\x00\x00\x00\x04\x00\x00\x00\xff\xff\x00\x00";

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->post(
                route('api.files.store', ['object_type' => 'assets', 'id' => $asset->id]),
                ['file' => [$this->realUpload('installer.exe', $peHeader)]]
            )
            ->assertSessionHasErrors('file.0');
    }

    // Issue #10387: CSV importer used to reject anything whose sniffed
    // MIME wasn't on a small hand-rolled list. Windows/IIS commonly
    // sniffs .csv as application/octet-stream because the platform's
    // magic database is thinner than Linux's. Real CSV content of the
    // shape below also sniffs as octet-stream on the current test
    // environment, which is exactly the scenario the reporter hit.
    #[Test]
    public function csv_importer_accepts_csv_that_content_sniffs_as_octet_stream(): void
    {
        // Leading NULs guarantee finfo returns application/octet-stream,
        // reproducing the Windows-sniff behavior deterministically.
        $csv = "\x00\x01\x02header1,header2\nvalue1,value2\n";

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->post(
                route('api.imports.store'),
                ['files' => [$this->realUpload('inventory.csv', $csv)]]
            )
            ->assertOk();
    }

    // Regression: POST /api/v1/imports with no `files` field returned
    // a 500 from Symfony's MIME guesser because the controller iterated
    // a null $files and then called getMimeType() on a phantom File
    // with an empty path. The guarded shape should return 422 with a
    // hint about the missing form field instead.
    #[Test]
    public function csv_importer_returns_422_when_files_field_is_missing(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.imports.store'), [])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    #[Test]
    public function csv_importer_returns_422_when_files_field_is_empty_array(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.imports.store'), ['files' => []])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    // Backstop: a genuine non-CSV file (a PNG here) whose extension is
    // also not csv/tsv/txt must still be rejected by the importer.
    #[Test]
    public function csv_importer_still_rejects_non_csv_extensions(): void
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
        );

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->post(
                route('api.imports.store'),
                ['files' => [$this->realUpload('picture.png', $png)]]
            )
            ->assertStatus(422);
    }
}
