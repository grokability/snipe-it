<?php

namespace Tests\Feature\S3Compatibility;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use ZipArchive;

/**
 * FR-3 / FR-4: web upload, web list/download/delete and the API endpoints all use the `backup`
 * disk under backup.backup.name, so they agree when that disk differs from the default disk.
 */
class BackupDiskConsistencyTest extends TestCase
{
    private string $backupRoot;

    private string $workDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workDir = sys_get_temp_dir().'/s3compat-backups-'.bin2hex(random_bytes(4));
        $this->backupRoot = $this->workDir.'/backup-disk';
        File::ensureDirectoryExists($this->backupRoot);

        // The backup disk lives somewhere other than the default (private) disk.
        config(['filesystems.disks.backup' => ['driver' => 'local', 'root' => $this->backupRoot]]);
        config(['app.allow_backup_delete' => 'true']);
        Storage::forgetDisk('backup');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->workDir);

        parent::tearDown();
    }

    private function makeZip(string $name = 'source.zip'): UploadedFile
    {
        $path = $this->workDir.'/'.$name;
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('db-dumps/mysql-snipeit.sql', 'SELECT 1;'.str_repeat('x', 1000));
        $zip->close();

        return new UploadedFile($path, $name, 'application/zip', null, true);
    }

    private function upload(): string
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.backups.upload'), ['file' => $this->makeZip()])
            ->assertSessionHas('success');

        $files = Storage::disk('backup')->files(config('backup.backup.name'));
        $this->assertCount(1, $files, 'uploaded zip must be on the backup disk under backup.backup.name');

        return basename($files[0]);
    }

    #[Test]
    public function uploaded_backup_is_listed_downloadable_and_deletable(): void
    {
        $name = $this->upload();
        $admin = User::factory()->superuser()->create();
        $expected = hash_file('sha256', $this->workDir.'/source.zip');

        $this->actingAs($admin)->get(route('settings.backups.index'))
            ->assertOk()
            ->assertViewHas('files', fn ($files) => collect($files)->pluck('filename')->contains($name));

        $download = $this->actingAs($admin)->get(route('settings.backups.download', ['filename' => $name]));
        $download->assertOk();
        $this->assertSame($expected, hash_file('sha256', $download->baseResponse->getFile()->getPathname()));

        $this->actingAs($admin)->delete(route('settings.backups.destroy', ['filename' => $name]))
            ->assertSessionHas('success');
        $this->assertFalse(Storage::disk('backup')->exists(config('backup.backup.name').'/'.$name));
    }

    #[Test]
    public function api_lists_and_downloads_the_same_backups_as_the_web_ui(): void
    {
        $name = $this->upload();
        $admin = User::factory()->superuser()->create();
        $expected = hash_file('sha256', $this->workDir.'/source.zip');

        $list = $this->actingAsForApi($admin)->getJson(route('api.settings.backups.index'))->assertOk();
        $this->assertContains($name, collect($list->json('rows'))->pluck('filename')->all());

        $byName = $this->actingAsForApi($admin)->get(route('api.settings.backups.download', ['file' => $name]));
        $byName->assertOk();
        $this->assertSame($expected, hash_file('sha256', $byName->baseResponse->getFile()->getPathname()));

        $latest = $this->actingAsForApi($admin)->get(route('api.settings.backups.latest'));
        $latest->assertOk();
        $this->assertSame($expected, hash_file('sha256', $latest->baseResponse->getFile()->getPathname()));
    }

    #[Test]
    public function api_latest_returns_404_when_there_are_no_backups(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.settings.backups.latest'))
            ->assertNotFound();
    }

    #[Test]
    public function api_latest_picks_the_newest_backup_with_a_stable_tie_break(): void
    {
        $disk = Storage::disk('backup');
        $dir = config('backup.backup.name');
        $disk->put($dir.'/a.zip', 'AAAA');
        $disk->put($dir.'/b.zip', 'BBBB');
        touch($this->backupRoot.'/'.$dir.'/a.zip', 1700000000);
        touch($this->backupRoot.'/'.$dir.'/b.zip', 1700000000);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->get(route('api.settings.backups.latest'));

        $response->assertOk();
        // Same modification time: filename order decides, and the result is the same on every call.
        $this->assertSame('b.zip', basename($response->baseResponse->getFile()->getPathname()));
    }

    #[Test]
    public function upload_reports_an_error_when_the_backup_disk_cannot_be_written(): void
    {
        // A file where the disk root should be makes every write fail.
        $brokenRoot = $this->workDir.'/not-a-directory';
        file_put_contents($brokenRoot, 'x');
        config(['filesystems.disks.backup' => ['driver' => 'local', 'root' => $brokenRoot.'/sub']]);
        Storage::forgetDisk('backup');

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.backups.upload'), ['file' => $this->makeZip()])
            ->assertSessionHas('error')
            ->assertSessionMissing('success');
    }

    #[Test]
    public function default_local_backup_disk_still_uses_storage_app_backups(): void
    {
        // Restore the shipped configuration for the backup disk and upload once.
        config(['filesystems.disks.backup' => ['driver' => 'local', 'root' => storage_path('app')]]);
        Storage::forgetDisk('backup');

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('settings.backups.upload'), ['file' => $this->makeZip('shipped.zip')])
            ->assertSessionHas('success');

        $found = glob(storage_path('app/backups/uploaded-*-shipped.zip'));
        $this->assertNotEmpty($found, 'local layout must stay storage/app/backups/<name>');
        foreach ($found as $f) {
            unlink($f);
        }
    }
}
