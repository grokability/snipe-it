<?php

namespace Tests\Feature\S3Compatibility;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\S3Compatibility\UsesFakeRemoteDisks;
use Tests\TestCase;
use ZipArchive;

/**
 * Characterization tests for the two design assumptions behind the S3 backup (task 1.2). They record how
 * the unmodified code behaves; they are not expected to fail first.
 *
 * (a) Files stored under storage/framework/backup-staging/<id>/{public/uploads,storage/private_uploads}/...
 *     in the archive are recognised by `snipeit:restore` and written back to the normal upload directories.
 * (b) Setting backup.backup.source.files.include with config() in the running process is honoured by
 *     `backup:run`.
 */
class StagingAssumptionsTest extends TestCase
{
    use UsesFakeRemoteDisks;

    private string $work;

    /** @var array<int, string> */
    private array $created = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->work = sys_get_temp_dir().'/s3compat-assume-'.bin2hex(random_bytes(4));
        File::ensureDirectoryExists($this->work);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->work);
        $this->cleanUpFakeRemoteDisks();
        foreach ($this->created as $path) {
            File::isDirectory($path) ? File::deleteDirectory($path) : @unlink($path);
        }
        parent::tearDown();
    }

    #[Test]
    public function restore_recognises_staged_paths_inside_the_archive(): void
    {
        $tag = 'stg'.bin2hex(random_bytes(3));
        $uuid = bin2hex(random_bytes(8));
        $stage = 'storage/framework/backup-staging/'.$uuid;

        $zipPath = $this->work.'/staged.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('db-dumps/mysql-test.sql', "DROP TABLE IF EXISTS `s3compat_probe`;\nCREATE TABLE `s3compat_probe` (\n  `id` int(11) NOT NULL\n) ENGINE=InnoDB;\nINSERT INTO `s3compat_probe` VALUES (1);\n");
        $zip->addFromString($stage.'/public/uploads/avatars/'.$tag.'.png', 'AVATAR-BYTES');
        $zip->addFromString($stage.'/storage/private_uploads/eula-pdfs/'.$tag.'.pdf', 'PDF-BYTES');
        $zip->addFromString($stage.'/storage/private_uploads/imports/'.$tag.'-legacy-csv', 'no extension');
        $zip->close();

        $this->created = [
            base_path('public/uploads/avatars/'.$tag.'.png'),
            base_path('storage/private_uploads/eula-pdfs/'.$tag.'.pdf'),
            base_path('storage/private_uploads/imports/'.$tag.'-legacy-csv'),
        ];

        // The restore wipes and rewrites tables of the configured database, so point it at a scratch one.
        $scratch = 's3compat_restore';
        DB::statement("CREATE DATABASE IF NOT EXISTS `$scratch`");
        config([
            'database.connections.mysql.database' => $scratch,
            'database.connections.mysql.dump.dump_binary_path' => '/usr/bin',
        ]);

        $exit = Artisan::call('snipeit:restore', ['filename' => $zipPath, '--force' => true, '--no-progress' => true]);
        $output = Artisan::output();

        fwrite(STDERR, "\n[assumption-a] exit=$exit output=".trim($output)."\n");

        $this->assertFileExists(base_path('public/uploads/avatars/'.$tag.'.png'), $output);
        $this->assertSame('AVATAR-BYTES', file_get_contents(base_path('public/uploads/avatars/'.$tag.'.png')));
        $this->assertFileExists(base_path('storage/private_uploads/eula-pdfs/'.$tag.'.pdf'), $output);
        $this->assertSame('PDF-BYTES', file_get_contents(base_path('storage/private_uploads/eula-pdfs/'.$tag.'.pdf')));
        // The extension-less CSV is only accepted when the name ends in "csv".
        $this->assertFileExists(base_path('storage/private_uploads/imports/'.$tag.'-legacy-csv'), $output);

        DB::statement("DROP DATABASE IF EXISTS `$scratch`");
    }

    #[Test]
    public function backup_run_honours_an_include_path_set_at_runtime(): void
    {
        $stageRel = 'storage/framework/backup-staging/'.bin2hex(random_bytes(8));
        $stage = base_path($stageRel);
        File::ensureDirectoryExists($stage.'/public/uploads/avatars');
        file_put_contents($stage.'/public/uploads/avatars/probe.png', 'STAGED-BYTES');
        $this->created[] = $stage;

        $backupRoot = $this->work.'/backup-disk';
        File::ensureDirectoryExists($backupRoot);
        config(['filesystems.disks.backup' => ['driver' => 'local', 'root' => $backupRoot]]);
        Storage::forgetDisk('backup');

        $include = config('backup.backup.source.files.include');
        $include[] = $stage;
        config(['backup.backup.source.files.include' => $include]);
        // Finding: Spatie builds its settings object once, so a changed include list only counts after it is rebuilt.
        app()->forgetInstance(\Spatie\Backup\Config\Config::class);

        $exit = Artisan::call('backup:run', ['--only-files' => true, '--disable-notifications' => true]);
        $output = Artisan::output();
        fwrite(STDERR, "\n[assumption-b] exit=$exit output=".trim($output)."\n");

        $zips = glob($backupRoot.'/'.config('backup.backup.name').'/*.zip');
        $this->assertCount(1, $zips, $output);

        $zip = new ZipArchive;
        $zip->open($zips[0]);
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();

        $this->assertContains($stageRel.'/public/uploads/avatars/probe.png', $names, implode("\n", $names));
    }

    #[Test]
    public function snipeit_backup_with_remote_files_puts_the_staged_copies_into_a_real_archive(): void
    {
        $this->fakeRemoteDisk('public');
        $this->fakeRemoteDisk('custom_private');
        config(['filesystems.default' => 'custom_private']);
        Storage::forgetDisk('custom_private');
        Storage::disk('public')->put('avatars/e2e-probe.png', 'PNG-BYTES');
        Storage::disk('custom_private')->put('private_uploads/eula-pdfs/e2e-probe.pdf', 'PDF-BYTES');

        $backupRoot = $this->work.'/backup-disk';
        File::ensureDirectoryExists($backupRoot);
        config(['filesystems.disks.backup' => ['driver' => 'local', 'root' => $backupRoot]]);
        Storage::forgetDisk('backup');
        config(['database.connections.mysql.dump.dump_binary_path' => '/usr/bin']);

        $exit = Artisan::call('snipeit:backup', ['--with-remote-files' => true, '--filename' => 'e2e.zip']);
        $output = Artisan::output();

        // The database dump may fail in this environment; the files are what this test is about.
        $zips = glob($backupRoot.'/'.config('backup.backup.name').'/*.zip');
        $this->assertCount(1, $zips, 'exit='.$exit.' '.$output);

        $zip = new ZipArchive;
        $zip->open($zips[0]);
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();

        $matching = fn (string $suffix) => array_values(array_filter($names, fn ($n) => str_ends_with($n, $suffix)));
        $this->assertNotEmpty($matching('/public/uploads/avatars/e2e-probe.png'), implode("\n", $names));
        $this->assertNotEmpty($matching('/storage/private_uploads/eula-pdfs/e2e-probe.pdf'));
        $this->assertNotEmpty($matching('/snipeit-remote-manifest.json'));
        $this->assertSame([], glob(storage_path('framework/backup-staging/*')) ?: []);
    }
}
