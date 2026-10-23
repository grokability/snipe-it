<?php

namespace Tests\Feature\S3Compatibility;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\S3Compatibility\FakeRemoteAdapter;
use Tests\Support\S3Compatibility\UsesFakeRemoteDisks;
use Tests\TestCase;
use ZipArchive;

/**
 * FR-12: `snipeit:restore` validates the whole archive before changing anything, writes upload files to
 * the disks configured for their roles (including non-local disks) at their full relative paths,
 * verifies what it wrote, never deletes unlisted objects on a non-local disk, and flags a half-finished
 * restore for manual recovery.
 *
 * Every run uses a scratch database (the restore rewrites tables) so the test database is untouched.
 */
class RestoreToRemoteDiskTest extends TestCase
{
    use UsesFakeRemoteDisks;

    private const SCRATCH = 's3compat_restore';

    private string $work;

    private string $publicRoot;

    private string $privateRoot;

    /** @var array<int, string> */
    private array $created = [];

    private string $tag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tag = 'rst'.bin2hex(random_bytes(3));
        $this->work = sys_get_temp_dir().'/s3compat-restore-'.bin2hex(random_bytes(4));
        File::ensureDirectoryExists($this->work);

        DB::statement('DROP DATABASE IF EXISTS `'.self::SCRATCH.'`');
        DB::statement('CREATE DATABASE `'.self::SCRATCH.'`');
        config([
            'database.connections.mysql.database' => self::SCRATCH,
            'database.connections.mysql.dump.dump_binary_path' => '/usr/bin',
        ]);
    }

    protected function tearDown(): void
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
        }
        DB::statement('DROP DATABASE IF EXISTS `'.self::SCRATCH.'`');
        File::deleteDirectory($this->work);
        foreach ($this->created as $path) {
            File::isDirectory($path) ? File::deleteDirectory($path) : @unlink($path);
        }
        $this->cleanUpFakeRemoteDisks();

        parent::tearDown();
    }

    private function useRemoteDisks(): void
    {
        $this->publicRoot = $this->fakeRemoteDisk('public');
        $this->privateRoot = $this->fakeRemoteDisk('custom_private');
        config(['filesystems.default' => 'custom_private']);
        Storage::forgetDisk('custom_private');
    }

    private function track(string $relative): string
    {
        $path = base_path($relative);
        $this->created[] = $path;

        return $path;
    }

    /**
     * @param  array<string, string>  $files  archive path relative to the staging root => bytes
     * @param  array<string, mixed>|false|null  $manifest  null = generate, false = no manifest, array = use as given
     */
    private function backupZip(array $files, array|false|null $manifest = null, string $sql = null, string $stage = 'storage/framework/backup-staging/abc123'): string
    {
        $zipPath = $this->work.'/backup-'.bin2hex(random_bytes(3)).'.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('db-dumps/mysql-test.sql', $sql ?? "DROP TABLE IF EXISTS `s3compat_probe`;\nCREATE TABLE `s3compat_probe` (\n  `id` int(11) NOT NULL\n) ENGINE=InnoDB;\nINSERT INTO `s3compat_probe` VALUES (1);\n");

        foreach ($files as $path => $bytes) {
            $zip->addFromString($stage.'/'.$path, $bytes);
        }

        if ($manifest === null) {
            $entries = [];
            foreach ($files as $path => $bytes) {
                $role = str_starts_with($path, 'public/uploads/') ? 'public' : 'private';
                $entries[] = [
                    'role' => $role,
                    'key' => $role === 'public' ? substr($path, strlen('public/uploads/')) : substr($path, strlen('storage/')),
                    'archive_path' => $path,
                    'bytes' => strlen($bytes),
                    'sha256' => hash('sha256', $bytes),
                ];
            }
            $manifest = ['version' => 1, 'created_at' => date('c'), 'remote_files' => 'included', 'files' => $entries];
        }
        if ($manifest !== false) {
            $zip->addFromString($stage.'/snipeit-remote-manifest.json', json_encode($manifest));
        }
        $zip->close();

        return $zipPath;
    }

    /** @return array{0: int, 1: string} */
    private function restore(string $zip): array
    {
        $exit = Artisan::call('snipeit:restore', ['filename' => $zip, '--force' => true, '--no-progress' => true]);

        return [$exit, Artisan::output()];
    }

    private function probeTableRows(): int
    {
        return (int) DB::connection()->selectOne('SELECT COUNT(*) AS c FROM `'.self::SCRATCH.'`.information_schema.tables WHERE table_schema = ? AND table_name = ?', [self::SCRATCH, 's3compat_probe'])->c;
    }

    private function seedProbeTable(): void
    {
        DB::statement('CREATE TABLE `'.self::SCRATCH.'`.`s3compat_probe` (`id` int)');
        DB::statement('INSERT INTO `'.self::SCRATCH.'`.`s3compat_probe` VALUES (99)');
    }

    private function probeValues(): array
    {
        return array_map(fn ($r) => (int) $r->id, DB::select('SELECT id FROM `'.self::SCRATCH.'`.`s3compat_probe`'));
    }

    // ---- writes to non-local disks ---------------------------------------------------------

    #[Test]
    public function files_are_written_to_the_configured_remote_disks_with_matching_bytes(): void
    {
        $this->useRemoteDisks();
        $png = random_bytes(500);
        $pdf = random_bytes(700);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><rect width="1" height="1"/></svg>';
        $zip = $this->backupZip([
            'public/uploads/avatars/'.$this->tag.'.png' => $png,
            'public/uploads/models/'.$this->tag.'.svg' => $svg,
            'storage/private_uploads/eula-pdfs/'.$this->tag.'.pdf' => $pdf,
            'storage/private_uploads/imports/'.$this->tag.'-legacycsv' => 'a,b',
        ]);

        [$exit, $output] = $this->restore($zip);

        $this->assertSame(0, $exit, $output);
        $this->assertSame(hash('sha256', $png), hash_file('sha256', $this->publicRoot.'/avatars/'.$this->tag.'.png'));
        $this->assertSame(hash('sha256', $pdf), hash_file('sha256', $this->privateRoot.'/private_uploads/eula-pdfs/'.$this->tag.'.pdf'));
        $this->assertSame('a,b', file_get_contents($this->privateRoot.'/private_uploads/imports/'.$this->tag.'-legacycsv'));
        $cleanSvg = file_get_contents($this->publicRoot.'/models/'.$this->tag.'.svg');
        $this->assertStringNotContainsString('<script', $cleanSvg, 'SVG sanitisation is kept');
        $this->assertStringContainsString('<rect', $cleanSvg);
    }

    #[Test]
    public function nested_paths_are_preserved_and_do_not_overwrite_each_other(): void
    {
        $this->useRemoteDisks();
        $zip = $this->backupZip([
            'storage/private_uploads/imports/a/x.csv' => 'from a',
            'storage/private_uploads/imports/b/x.csv' => 'from b',
            'public/uploads/models/a/x.png' => 'public a',
            'public/uploads/models/b/x.png' => 'public b',
        ]);

        [$exit, $output] = $this->restore($zip);

        $this->assertSame(0, $exit, $output);
        $this->assertSame('from a', file_get_contents($this->privateRoot.'/private_uploads/imports/a/x.csv'));
        $this->assertSame('from b', file_get_contents($this->privateRoot.'/private_uploads/imports/b/x.csv'));
        $this->assertSame('public a', file_get_contents($this->publicRoot.'/models/a/x.png'));
        $this->assertSame('public b', file_get_contents($this->publicRoot.'/models/b/x.png'));
    }

    #[Test]
    public function objects_on_a_remote_disk_that_are_not_in_the_backup_are_left_alone(): void
    {
        $this->useRemoteDisks();
        Storage::disk('custom_private')->put('private_uploads/eula-pdfs/keep-'.$this->tag.'.pdf', 'KEEP');
        Storage::disk('public')->put('avatars/keep-'.$this->tag.'.png', 'KEEP');
        Storage::disk('custom_private')->put('private_uploads/eula-pdfs/'.$this->tag.'.pdf', 'OLD');
        $zip = $this->backupZip(['storage/private_uploads/eula-pdfs/'.$this->tag.'.pdf' => 'NEW']);

        [$exit, $output] = $this->restore($zip);

        $this->assertSame(0, $exit, $output);
        $this->assertSame('KEEP', file_get_contents($this->privateRoot.'/private_uploads/eula-pdfs/keep-'.$this->tag.'.pdf'));
        $this->assertSame('KEEP', file_get_contents($this->publicRoot.'/avatars/keep-'.$this->tag.'.png'));
        $this->assertSame('NEW', file_get_contents($this->privateRoot.'/private_uploads/eula-pdfs/'.$this->tag.'.pdf'), 'listed objects are overwritten');
        $this->assertSame([], FakeRemoteAdapter::keysFor('delete'), 'nothing is deleted on a non-local disk');
    }

    // ---- local disks keep their behaviour ---------------------------------------------------

    #[Test]
    public function local_disks_are_pruned_and_written_as_before_with_nested_paths_kept(): void
    {
        $stale = $this->track('public/uploads/avatars/stale-'.$this->tag.'.png');
        File::ensureDirectoryExists(dirname($stale));
        file_put_contents($stale, 'STALE');
        $zip = $this->backupZip([
            'public/uploads/avatars/'.$this->tag.'.png' => 'FRESH',
            'storage/private_uploads/imports/sub/'.$this->tag.'.csv' => 'a,b',
        ]);
        $this->track('public/uploads/avatars/'.$this->tag.'.png');
        $this->track('storage/private_uploads/imports/sub');

        [$exit, $output] = $this->restore($zip);

        $this->assertSame(0, $exit, $output);
        $this->assertFileDoesNotExist($stale, 'local upload directories are still pruned');
        $this->assertSame('FRESH', file_get_contents(base_path('public/uploads/avatars/'.$this->tag.'.png')));
        $this->assertSame('a,b', file_get_contents(base_path('storage/private_uploads/imports/sub/'.$this->tag.'.csv')));
    }

    // ---- validation before anything changes -------------------------------------------------

    public static function brokenArchives(): array
    {
        return [
            'sha256 differs from the manifest' => ['sha'],
            'manifest lists a file that is not in the archive' => ['missing'],
            'unsafe path inside the archive' => ['traversal'],
            'not a manifest-conformant size' => ['size'],
        ];
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('brokenArchives')]
    public function an_invalid_archive_changes_nothing(string $problem): void
    {
        $this->useRemoteDisks();
        $this->seedProbeTable();
        $stale = $this->track('public/uploads/avatars/stale-'.$this->tag.'.png');
        File::ensureDirectoryExists(dirname($stale));
        file_put_contents($stale, 'STALE');
        Storage::disk('custom_private')->put('private_uploads/eula-pdfs/existing-'.$this->tag.'.pdf', 'EXISTING');

        $files = ['storage/private_uploads/eula-pdfs/'.$this->tag.'.pdf' => 'PDF'];
        $manifest = null;
        if ($problem === 'sha') {
            $manifest = ['version' => 1, 'remote_files' => 'included', 'files' => [[
                'role' => 'private', 'key' => 'private_uploads/eula-pdfs/'.$this->tag.'.pdf',
                'archive_path' => 'storage/private_uploads/eula-pdfs/'.$this->tag.'.pdf',
                'bytes' => 3, 'sha256' => str_repeat('0', 64),
            ]]];
        } elseif ($problem === 'size') {
            $manifest = ['version' => 1, 'remote_files' => 'included', 'files' => [[
                'role' => 'private', 'key' => 'private_uploads/eula-pdfs/'.$this->tag.'.pdf',
                'archive_path' => 'storage/private_uploads/eula-pdfs/'.$this->tag.'.pdf',
                'bytes' => 999, 'sha256' => hash('sha256', 'PDF'),
            ]]];
        } elseif ($problem === 'missing') {
            $manifest = ['version' => 1, 'remote_files' => 'included', 'files' => [[
                'role' => 'private', 'key' => 'private_uploads/eula-pdfs/gone-'.$this->tag.'.pdf',
                'archive_path' => 'storage/private_uploads/eula-pdfs/gone-'.$this->tag.'.pdf',
                'bytes' => 3, 'sha256' => hash('sha256', 'GON'),
            ]]];
        } elseif ($problem === 'traversal') {
            $files['storage/private_uploads/eula-pdfs/../../../../evil-'.$this->tag.'.pdf'] = 'EVIL';
        }
        $zip = $this->backupZip($files, $manifest);

        [$exit, $output] = $this->restore($zip);

        $this->assertNotSame(0, $exit, $output);
        $this->assertSame([99], $this->probeValues(), 'database is untouched');
        $this->assertFileExists($stale, 'no prune before the archive is validated');
        $this->assertFileExists($this->privateRoot.'/private_uploads/eula-pdfs/existing-'.$this->tag.'.pdf');
        $this->assertFileDoesNotExist($this->privateRoot.'/private_uploads/eula-pdfs/'.$this->tag.'.pdf');
        $this->assertFileDoesNotExist(base_path('../evil-'.$this->tag.'.pdf'));
    }

    #[Test]
    public function an_unreadable_archive_triggers_no_prune_and_a_non_zero_exit(): void
    {
        $this->useRemoteDisks();
        $stale = $this->track('public/uploads/avatars/stale-'.$this->tag.'.png');
        File::ensureDirectoryExists(dirname($stale));
        file_put_contents($stale, 'STALE');
        $bad = $this->work.'/not-a-zip.zip';
        file_put_contents($bad, 'this is not a zip');

        [$exit, $output] = $this->restore($bad);

        $this->assertNotSame(0, $exit, $output);
        $this->assertFileExists($stale);
    }

    // ---- excluded and legacy archives -------------------------------------------------------

    #[Test]
    public function an_archive_without_remote_files_leaves_the_remote_disks_untouched_and_warns(): void
    {
        $this->useRemoteDisks();
        Storage::disk('custom_private')->put('private_uploads/eula-pdfs/existing-'.$this->tag.'.pdf', 'EXISTING');
        $local = $this->track('public/uploads/avatars/'.$this->tag.'.png');
        $zip = $this->backupZip(
            [],
            ['version' => 1, 'created_at' => date('c'), 'remote_files' => 'excluded', 'files' => []],
        );

        [$exit, $output] = $this->restore($zip);

        $this->assertSame(0, $exit, $output);
        $this->assertStringContainsString('S3 files are not part of this backup', $output);
        $this->assertFileExists($this->privateRoot.'/private_uploads/eula-pdfs/existing-'.$this->tag.'.pdf');
        $this->assertSame([], FakeRemoteAdapter::keysFor('delete'));
        $this->assertFileDoesNotExist($local);
    }

    #[Test]
    public function an_archive_without_a_manifest_restores_with_a_warning(): void
    {
        $this->useRemoteDisks();
        $zip = $this->backupZip(['storage/private_uploads/eula-pdfs/'.$this->tag.'.pdf' => 'LEGACY'], false);

        [$exit, $output] = $this->restore($zip);

        $this->assertSame(0, $exit, $output);
        $this->assertStringContainsString('no manifest', strtolower($output));
        $this->assertSame('LEGACY', file_get_contents($this->privateRoot.'/private_uploads/eula-pdfs/'.$this->tag.'.pdf'));
    }

    // ---- failure after the restore started --------------------------------------------------

    #[Test]
    public function a_write_failure_after_the_restore_started_is_reported_and_puts_the_app_in_maintenance(): void
    {
        $this->useRemoteDisks();
        $zip = $this->backupZip([
            'storage/private_uploads/eula-pdfs/'.$this->tag.'-a.pdf' => 'AAA',
            'storage/private_uploads/eula-pdfs/'.$this->tag.'-b.pdf' => 'BBB',
        ]);
        FakeRemoteAdapter::$failWrites = ['private_uploads/eula-pdfs/'.$this->tag.'-b.pdf'];

        [$exit, $output] = $this->restore($zip);

        $this->assertNotSame(0, $exit);
        $this->assertStringContainsString('needs manual recovery', strtolower($output));
        $this->assertStringContainsString($this->tag.'-b.pdf', $output, 'the failing key is named');
        $this->assertTrue(app()->isDownForMaintenance());
        $this->assertSame('AAA', file_get_contents($this->privateRoot.'/private_uploads/eula-pdfs/'.$this->tag.'-a.pdf'), 'other files are still written so one report lists every failure');
    }

    #[Test]
    public function an_object_that_reads_back_differently_fails_the_restore(): void
    {
        $this->useRemoteDisks();
        $zip = $this->backupZip(['storage/private_uploads/eula-pdfs/'.$this->tag.'.pdf' => 'PDFBYTES']);
        FakeRemoteAdapter::$corruptWrites = ['private_uploads/eula-pdfs/'.$this->tag.'.pdf'];

        [$exit, $output] = $this->restore($zip);

        $this->assertNotSame(0, $exit);
        $this->assertStringContainsString('needs manual recovery', strtolower($output));
    }

    // ---- web flow ---------------------------------------------------------------------------

    #[Test]
    public function the_web_restore_takes_its_safety_backup_with_remote_files_included(): void
    {
        $source = file_get_contents(base_path('app/Http/Controllers/SettingsController.php'));
        $this->assertMatchesRegularExpression("/'snipeit:backup',\s*\[[^\]]*'--with-remote-files'\s*=>\s*true/s", $source);
    }
}
