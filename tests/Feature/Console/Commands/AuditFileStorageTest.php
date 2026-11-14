<?php

namespace Tests\Feature\Console\Commands;

use App\Enums\FileStorage;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuditFileStorageTest extends TestCase
{
    private ?string $csvPath = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Both scanners route through Storage::fake-able disks. The
        // orphan pass enumerates files() under each case's prefix, so
        // fake disks give test-local isolation and no cross-test
        // pollution.
        Storage::fake('public');
        Storage::fake(config('filesystems.default'));
    }

    protected function tearDown(): void
    {
        if ($this->csvPath !== null && is_file($this->csvPath)) {
            @unlink($this->csvPath);
        }

        // Storage::fake() already resets the fake disk root at every
        // setUp(), but the LAST test's files linger in
        // storage/framework/testing/disks/ until something cleans them
        // up. Explicitly flush both fake disks here so the test suite
        // leaves nothing behind on the real filesystem.
        //
        // The path() check guards against the wipe hitting a real disk.
        // Storage::fake() swaps the binding under this test's lifetime,
        // but if an earlier error prevents setUp from faking (test
        // extension, misconfig, etc), the facade returns the real disk
        // and allDirectories/allFiles enumerates the production tree.
        // Deleting under the real disk nukes committed dotfiles like
        // public/uploads/.gitignore. Restricting to a path inside
        // storage/framework/testing/disks/ keeps the wipe scoped to
        // Laravel's fake-disk sandbox.
        $fakeRoot = storage_path('framework/testing/disks');
        foreach (['public', config('filesystems.default')] as $disk) {
            try {
                $driver = Storage::disk($disk);
                if (! str_starts_with((string) $driver->path(''), $fakeRoot)) {
                    continue;
                }
                foreach ($driver->allDirectories() as $dir) {
                    $driver->deleteDirectory($dir);
                }
                foreach ($driver->allFiles() as $file) {
                    $driver->delete($file);
                }
            } catch (\Throwable) {
                // Disk was never faked for this test. Nothing to clean.
            }
        }

        parent::tearDown();
    }

    public function test_missing_direction_reports_db_rows_whose_files_are_absent(): void
    {
        Statuslabel::factory()->rtd()->create();
        $missing = Asset::factory()->create(['image' => 'ghost.png']);
        $present = Asset::factory()->create(['image' => 'real.png']);
        Storage::disk('public')->put(FileStorage::Assets->publicPath().'real.png', 'bytes');

        $this->runAudit([
            '--direction' => 'missing',
            '--only' => 'images',
        ]);

        $assetRows = $this->rowsFor('assets');
        $this->assertCount(1, $assetRows, 'Exactly one asset-image row expected.');
        $this->assertSame('missing', $assetRows[0]['direction']);
        $this->assertSame((string) $missing->id, $assetRows[0]['id']);
    }

    public function test_orphan_direction_reports_disk_files_with_no_db_row(): void
    {
        Statuslabel::factory()->rtd()->create();
        Asset::factory()->create(['image' => 'claimed.png']);

        $publicDisk = Storage::disk('public');
        $publicDisk->put(FileStorage::Assets->publicPath().'claimed.png', 'bytes');
        $publicDisk->put(FileStorage::Assets->publicPath().'orphan-one.png', 'bytes');
        $publicDisk->put(FileStorage::Assets->publicPath().'orphan-two.png', 'bytes');

        $this->runAudit([
            '--direction' => 'orphan',
            '--only' => 'images',
        ]);

        $rows = $this->readCsvRows();
        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertSame('orphan', $row['direction']);
            $this->assertStringContainsString('orphan-', $row['file']);
        }
    }

    public function test_both_direction_combines_passes_with_direction_tags(): void
    {
        Statuslabel::factory()->rtd()->create();
        $missing = Asset::factory()->create(['image' => 'ghost.png']);
        Storage::disk('public')->put(FileStorage::Assets->publicPath().'leftover.png', 'bytes');

        $this->runAudit([
            '--direction' => 'both',
            '--only' => 'images',
        ]);

        $assetRows = $this->rowsFor('assets');
        $directions = collect($assetRows)->pluck('direction')->sort()->values()->all();
        $this->assertSame(['missing', 'orphan'], $directions);
    }

    public function test_only_filter_scopes_the_scan(): void
    {
        Statuslabel::factory()->rtd()->create();
        Asset::factory()->create(['image' => 'claimed.png']);
        Storage::disk('public')->put(FileStorage::Assets->publicPath().'claimed.png', 'bytes');
        Storage::disk('public')->put(FileStorage::Assets->publicPath().'image-orphan.png', 'bytes');

        Storage::put(FileStorage::Imports->privateStorageKey().'import-orphan.csv', 'csv-bytes');

        $this->runAudit([
            '--direction' => 'orphan',
            '--only' => 'imports',
        ]);

        // Imports orphan surfaces, assets orphan filtered out by --only.
        $rows = $this->readCsvRows();
        $this->assertCount(1, $rows);
        $this->assertSame('imports', $rows[0]['table']);
    }

    public function test_delete_with_force_removes_orphan_files_from_disk(): void
    {
        Statuslabel::factory()->rtd()->create();
        Asset::factory()->create(['image' => 'claimed.png']);
        $publicDisk = Storage::disk('public');
        $publicDisk->put(FileStorage::Assets->publicPath().'claimed.png', 'bytes');
        $publicDisk->put(FileStorage::Assets->publicPath().'orphan-one.png', 'bytes');
        $publicDisk->put(FileStorage::Assets->publicPath().'orphan-two.png', 'bytes');

        $this->artisan('snipeit:audit-file-storage', [
            '--direction' => 'orphan',
            '--only' => 'images',
            '--delete' => true,
            '--force' => true,
        ])->assertExitCode(0);

        $publicDisk->assertMissing(FileStorage::Assets->publicPath().'orphan-one.png');
        $publicDisk->assertMissing(FileStorage::Assets->publicPath().'orphan-two.png');
        $publicDisk->assertExists(FileStorage::Assets->publicPath().'claimed.png');
    }

    public function test_delete_leaves_missing_direction_rows_alone(): void
    {
        Statuslabel::factory()->rtd()->create();
        // No orphan files, only a missing-direction mismatch.
        Asset::factory()->create(['image' => 'ghost.png']);
        $publicDisk = Storage::disk('public');
        $publicDisk->put(FileStorage::Assets->publicPath().'claimed.png', 'bytes');
        Asset::factory()->create(['image' => 'claimed.png']);

        $this->artisan('snipeit:audit-file-storage', [
            '--direction' => 'both',
            '--only' => 'images',
            '--delete' => true,
            '--force' => true,
        ])->assertExitCode(0);

        // The claimed file is referenced by a DB row, so no deletion.
        $publicDisk->assertExists(FileStorage::Assets->publicPath().'claimed.png');
    }

    public function test_action_log_uploads_are_split_per_item_type(): void
    {
        Statuslabel::factory()->rtd()->create();
        $asset = Asset::factory()->create();
        $user = User::factory()->create();

        Actionlog::factory()->create([
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'uploaded',
            'filename' => 'missing-asset-doc.pdf',
        ]);
        Actionlog::factory()->create([
            'item_type' => User::class,
            'item_id' => $user->id,
            'action_type' => 'uploaded',
            'filename' => 'missing-user-doc.pdf',
        ]);

        $this->runAudit([
            '--direction' => 'missing',
            '--only' => 'action_logs',
        ]);

        $tables = collect($this->readCsvRows())->pluck('table')->sort()->values()->all();
        $this->assertSame(['assets', 'users'], $tables);
    }

    public function test_acceptance_signature_orphan_gets_detected(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        // Claimed signature stays, orphan gets reported.
        CheckoutAcceptance::factory()->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $user->id,
            'signature_filename' => 'claimed-sig.png',
            'stored_eula_file' => null,
        ]);
        Storage::put(FileStorage::Signatures->privateStorageKey().'claimed-sig.png', 'bytes');
        Storage::put(FileStorage::Signatures->privateStorageKey().'leftover-sig.png', 'bytes');

        $this->runAudit([
            '--direction' => 'orphan',
            '--only' => 'acceptances',
        ]);

        $rows = $this->readCsvRows();
        $this->assertCount(1, $rows);
        $this->assertSame('checkout_acceptances', $rows[0]['table']);
        $this->assertSame('signature_filename', $rows[0]['type']);
        $this->assertStringContainsString('leftover-sig.png', $rows[0]['file']);
    }

    /**
     * Thin wrapper around $this->artisan that always writes a CSV so
     * tests can read structured rows out of the sweep without
     * fighting with Laravel Prompts' output stream (which does not
     * flow through Artisan::call's BufferedOutput in the test harness).
     *
     * @param  array<string, mixed>  $parameters
     */
    private function runAudit(array $parameters): void
    {
        $parameters['--csv'] = $this->tempCsvPath();
        $this->artisan('snipeit:audit-file-storage', $parameters)->assertExitCode(0);
    }

    private function tempCsvPath(): string
    {
        if ($this->csvPath === null) {
            $stub = $this->trackTempFile(tempnam(sys_get_temp_dir(), 'audit-file-storage-'));
            $this->csvPath = $this->trackTempFile($stub.'.csv');
        }

        return $this->csvPath;
    }

    /**
     * @return array<int, array{direction: string, table: string, id: string, type: string, file: string}>
     */
    private function readCsvRows(): array
    {
        $this->assertFileExists($this->csvPath);

        $handle = fopen($this->csvPath, 'r');
        $header = fgetcsv($handle, escape: '\\');
        $this->assertSame(['direction', 'table', 'id', 'type', 'file'], $header);

        $rows = [];
        while (($row = fgetcsv($handle, escape: '\\')) !== false) {
            $rows[] = array_combine($header, $row);
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Narrow the CSV rows down to the one $table we care about. Tests
     * run against a seeded DB that has pre-existing admin users with
     * `.jpg` avatars (fixtures shipped with the install), so asserting
     * on the whole row set would false-positive on those unrelated
     * misses.
     *
     * @return array<int, array{direction: string, table: string, id: string, type: string, file: string}>
     */
    private function rowsFor(string $table): array
    {
        return array_values(array_filter($this->readCsvRows(), fn (array $row) => $row['table'] === $table));
    }
}
