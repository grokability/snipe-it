<?php

namespace Tests\Feature\Importing\Api;

use App\Models\Import;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * `POST /api/v1/imports` accepts a `files[]` array. Before the fix,
 * `ImportController::store` instantiated one `new Import` above the
 * foreach loop and reused the same model instance for every file.
 * Each iteration overwrote the row's `file_path` and called `save()`,
 * so after N files the DB held one row referencing the Nth file while
 * N-1 earlier files sat on disk with no row referencing them. Those
 * objects could not be removed through `destroy()`, which deletes by
 * `file_path` on the stored row.
 */
class ImportStoreMultiFileRowPerFileTest extends TestCase
{
    public function test_multi_file_upload_creates_one_row_per_file_and_leaves_no_orphans(): void
    {
        Storage::fake();

        $importer = User::factory()->canImport()->create();

        $firstCsv = "asset tag,item name\nORPHAN-A,File One\n";
        $secondCsv = "asset tag,item name\nORPHAN-B,File Two\n";

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->fakeCsvUpload('cand-14-first.csv', $firstCsv),
                    $this->fakeCsvUpload('cand-14-second.csv', $secondCsv),
                ],
            ])
            ->assertSuccessful();

        $imports = Import::query()->where('created_by', $importer->id)->get();
        $this->assertCount(2, $imports, 'Each uploaded file must get its own Import row.');

        $storedPaths = $imports->pluck('file_path')->all();
        $this->assertCount(2, array_unique($storedPaths), 'The two rows must reference distinct storage paths.');

        $storedFiles = Storage::disk()->files('private_uploads/imports');
        $this->assertCount(2, $storedFiles, 'Exactly two storage objects should exist for two uploads.');

        foreach ($storedPaths as $path) {
            $this->assertTrue(
                Storage::disk()->exists('private_uploads/imports/'.$path),
                "Row's file_path must reference a real object on disk ({$path}).",
            );
        }

        $referenced = array_map(fn ($p) => 'private_uploads/imports/'.$p, $storedPaths);
        $orphans = array_diff($storedFiles, $referenced);
        $this->assertEmpty(
            $orphans,
            'No storage object may be left without a DB row referencing it. Orphans: '.implode(', ', $orphans),
        );
    }

    public function test_single_file_upload_still_creates_one_row(): void
    {
        Storage::fake();

        $importer = User::factory()->canImport()->create();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->fakeCsvUpload('solo.csv', "asset tag,item name\nSOLO-001,Solo\n"),
                ],
            ])
            ->assertSuccessful();

        $this->assertSame(
            1,
            Import::query()->where('created_by', $importer->id)->count(),
            'The single-file happy path must remain one row per file.',
        );
    }

    private function fakeCsvUpload(string $name, string $contents): UploadedFile
    {
        $path = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'csv'));
        file_put_contents($path, $contents);

        return new UploadedFile($path, $name, 'text/csv', null, true);
    }
}
