<?php

namespace Tests\Feature\S3Compatibility;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\S3Compatibility\FakeRemoteAdapter;
use Tests\Support\S3Compatibility\UsesFakeRemoteDisks;
use Tests\TestCase;

/**
 * FR-11 / FR-11a: snipeit:backup includes S3 hosted upload files only when asked to (default: not),
 * and only then reads them.
 */
class BackupRemoteOptionTest extends TestCase
{
    use UsesFakeRemoteDisks;

    /** @var array<int, array<string, mixed>> what the stubbed backup:run saw */
    private array $calls = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeRemoteDisk('public');
        $this->fakeRemoteDisk('custom_private');
        config(['filesystems.default' => 'custom_private']);
        Storage::forgetDisk('custom_private');
        config(['backup.include_remote_files' => false]);

        Storage::disk('custom_private')->put('private_uploads/eula-pdfs/e.pdf', 'PDF-BYTES');
        Storage::disk('public')->put('avatars/a.png', 'PNG-BYTES');
        FakeRemoteAdapter::$log = [];

        $this->stubBackupRun(0);
    }

    protected function tearDown(): void
    {
        $this->cleanUpFakeRemoteDisks();

        parent::tearDown();
    }

    private function stubBackupRun(int $exitCode): void
    {
        $this->calls = [];
        Artisan::command('backup:run {--filename=}', function () use ($exitCode) {
            $extra = [];
            foreach (config('backup.backup.source.files.include') as $path) {
                $manifest = $path.'/snipeit-remote-manifest.json';
                if (is_file($manifest)) {
                    $extra[] = [
                        'dir' => $path,
                        'manifest' => json_decode(file_get_contents($manifest), true),
                        'files' => array_map(
                            fn ($f) => str_replace('\\', '/', substr($f->getPathname(), strlen($path) + 1)),
                            File::allFiles($path, true),
                        ),
                    ];
                }
            }
            app()->instance('s3compat.backup-run-calls', array_merge(app('s3compat.backup-run-calls') ?? [], [['extra' => $extra]]));

            return $exitCode;
        })->purpose('stub');
        app()->instance('s3compat.backup-run-calls', []);
    }

    private function calls(): array
    {
        return app('s3compat.backup-run-calls');
    }

    private function remoteUploadReads(): array
    {
        return array_values(array_filter(
            FakeRemoteAdapter::$log,
            fn ($e) => in_array($e[0], ['read', 'list'], true)
                && (str_starts_with($e[1], 'public:') || str_starts_with($e[1], 'custom_private:')),
        ));
    }

    #[Test]
    public function by_default_remote_files_are_neither_listed_nor_read_and_the_manifest_says_excluded(): void
    {
        $this->artisan('snipeit:backup')->assertExitCode(0);

        $this->assertSame([], $this->remoteUploadReads());
        $calls = $this->calls();
        $this->assertCount(1, $calls);
        $this->assertCount(1, $calls[0]['extra']);
        $this->assertSame('excluded', $calls[0]['extra'][0]['manifest']['remote_files']);
        $this->assertSame([], $calls[0]['extra'][0]['manifest']['files']);
        $this->assertSame(['snipeit-remote-manifest.json'], $calls[0]['extra'][0]['files']);
        $this->assertDirectoryDoesNotExist($calls[0]['extra'][0]['dir'], 'the marker directory is removed afterwards');
    }

    #[Test]
    public function without_remote_files_flag_behaves_like_the_default(): void
    {
        $this->artisan('snipeit:backup', ['--without-remote-files' => true])->assertExitCode(0);

        $this->assertSame([], $this->remoteUploadReads());
        $this->assertSame('excluded', $this->calls()[0]['extra'][0]['manifest']['remote_files']);
    }

    #[Test]
    public function with_remote_files_flag_stages_the_objects_and_cleans_up(): void
    {
        $this->artisan('snipeit:backup', ['--with-remote-files' => true])->assertExitCode(0);

        $extra = $this->calls()[0]['extra'];
        $this->assertCount(1, $extra);
        $this->assertSame('included', $extra[0]['manifest']['remote_files']);
        $this->assertEqualsCanonicalizing(
            ['public/uploads/avatars/a.png', 'storage/private_uploads/eula-pdfs/e.pdf', 'snipeit-remote-manifest.json'],
            $extra[0]['files'],
        );
        $this->assertDirectoryDoesNotExist($extra[0]['dir']);
    }

    #[Test]
    public function the_environment_setting_turns_remote_files_on_for_scheduled_backups(): void
    {
        config(['backup.include_remote_files' => true]);

        $this->artisan('snipeit:backup')->assertExitCode(0);

        $this->assertSame('included', $this->calls()[0]['extra'][0]['manifest']['remote_files']);
    }

    #[Test]
    public function the_flags_override_the_environment_setting(): void
    {
        config(['backup.include_remote_files' => true]);

        $this->artisan('snipeit:backup', ['--without-remote-files' => true])->assertExitCode(0);

        $this->assertSame('excluded', $this->calls()[0]['extra'][0]['manifest']['remote_files']);
    }

    #[Test]
    public function the_two_flags_together_are_rejected(): void
    {
        $this->artisan('snipeit:backup', ['--with-remote-files' => true, '--without-remote-files' => true])
            ->assertExitCode(1);

        $this->assertSame([], $this->calls(), 'backup:run must not run');
    }

    #[Test]
    public function a_failure_while_staging_stops_the_backup_and_leaves_nothing_behind(): void
    {
        FakeRemoteAdapter::$failReads = ['private_uploads/eula-pdfs/e.pdf'];

        $this->artisan('snipeit:backup', ['--with-remote-files' => true])->assertExitCode(1);

        $this->assertSame([], $this->calls(), 'no archive may be produced when an object cannot be read');
        $this->assertSame([], glob(storage_path('framework/backup-staging/*')) ?: []);
    }

    #[Test]
    public function a_failing_backup_run_fails_the_command_and_still_cleans_up(): void
    {
        $this->stubBackupRun(1);

        $this->artisan('snipeit:backup', ['--with-remote-files' => true])->assertExitCode(1);

        $this->assertSame([], glob(storage_path('framework/backup-staging/*')) ?: []);
    }

    #[Test]
    public function with_only_local_disks_nothing_changes(): void
    {
        config(['filesystems.disks.public' => ['driver' => 'local', 'root' => sys_get_temp_dir()]]);
        config(['filesystems.default' => 'local']);
        Storage::forgetDisk('public');
        Storage::forgetDisk('local');

        $before = config('backup.backup.source.files.include');
        $this->artisan('snipeit:backup', ['--with-remote-files' => true])->assertExitCode(0);

        $this->assertSame([], $this->calls()[0]['extra'], 'no manifest directory is added for all-local installs');
        $this->assertSame($before, config('backup.backup.source.files.include'));
    }

    #[Test]
    public function callers_that_used_backup_run_directly_now_include_remote_files(): void
    {
        foreach (['app/Http/Controllers/SettingsController.php', 'app/Console/Commands/RegenerateAssetTags.php', 'app/Console/Commands/RestoreDeletedUsers.php'] as $file) {
            $source = file_get_contents(base_path($file));
            $this->assertStringNotContainsString("'backup:run'", $source, $file.' must call snipeit:backup');
            $this->assertStringContainsString('--with-remote-files', $source, $file);
        }
    }
}
