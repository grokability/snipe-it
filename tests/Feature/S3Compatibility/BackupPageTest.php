<?php

namespace Tests\Feature\S3Compatibility;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\S3Compatibility\UsesFakeRemoteDisks;
use Tests\TestCase;
use ZipArchive;

/**
 * FR-11a / FR-13: the backup page offers "with / without S3 files" (default: without) only when a disk is
 * remote, labels each backup, and states the limits.
 */
class BackupPageTest extends TestCase
{
    use UsesFakeRemoteDisks;

    private string $backupRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupRoot = sys_get_temp_dir().'/s3compat-page-'.bin2hex(random_bytes(4));
        File::ensureDirectoryExists($this->backupRoot.'/backups');
        config(['filesystems.disks.backup' => ['driver' => 'local', 'root' => $this->backupRoot]]);
        Storage::forgetDisk('backup');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backupRoot);
        $this->cleanUpFakeRemoteDisks();

        parent::tearDown();
    }

    private function remoteDisks(): void
    {
        $this->fakeRemoteDisk('public');
        $this->fakeRemoteDisk('custom_private');
        config(['filesystems.default' => 'custom_private']);
        Storage::forgetDisk('custom_private');
    }

    private function page()
    {
        return $this->actingAs(User::factory()->superuser()->create())->get(route('settings.backups.index'));
    }

    private function zip(string $name, ?array $manifest): void
    {
        $zip = new ZipArchive;
        $zip->open($this->backupRoot.'/backups/'.$name, ZipArchive::CREATE);
        $zip->addFromString('db-dumps/mysql-test.sql', 'SELECT 1;');
        if ($manifest !== null) {
            $zip->addFromString('storage/framework/backup-staging/x/snipeit-remote-manifest.json', json_encode($manifest));
        }
        $zip->close();
    }

    #[Test]
    public function remote_disks_show_both_choices_with_without_preselected_and_the_limits(): void
    {
        $this->remoteDisks();

        $response = $this->page()->assertOk();

        $response->assertSee('name="include_remote_files"', false);
        $response->assertSee(trans('admin/settings/general.backups_remote_without'), false);
        $response->assertSee(trans('admin/settings/general.backups_remote_with'), false);
        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/<input[^>]*name="include_remote_files"[^>]*value="0"[^>]*checked/s', $html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*name="include_remote_files"[^>]*value="1"[^>]*checked/s', $html);
        $response->assertSee(trans('admin/settings/general.backups_remote_not_included'), false);
        $response->assertSee(trans('admin/settings/general.backups_remote_limits'), false);
        $response->assertSee(trans('admin/settings/general.backups_remote_quiesce'), false);
    }

    #[Test]
    public function all_local_installs_see_no_new_option_or_notice(): void
    {
        config(['filesystems.default' => 'local']);
        Storage::forgetDisk('local');

        $response = $this->page()->assertOk();

        $response->assertDontSee('include_remote_files', false);
        $response->assertDontSee(trans('admin/settings/general.backups_remote_limits'), false);
    }

    #[Test]
    public function the_generate_button_passes_the_choice_on(): void
    {
        $this->remoteDisks();
        $seen = [];
        Artisan::command('snipeit:backup {--filename=} {--force} {--with-remote-files} {--without-remote-files}', function () use (&$seen) {
            $seen[] = [
                'with' => (bool) $this->option('with-remote-files'),
                'without' => (bool) $this->option('without-remote-files'),
            ];

            return 0;
        })->purpose('stub');
        $admin = User::factory()->superuser()->create();

        $this->actingAs($admin)->post(route('settings.backups.create'), ['include_remote_files' => '1']);
        $this->actingAs($admin)->post(route('settings.backups.create'), ['include_remote_files' => '0']);
        $this->actingAs($admin)->post(route('settings.backups.create'), []);

        $this->assertSame([true, false], [$seen[0]['with'], $seen[0]['without']]);
        $this->assertSame([false, true], [$seen[1]['with'], $seen[1]['without']]);
        $this->assertSame([false, true], [$seen[2]['with'], $seen[2]['without']], 'no choice means the default: without');
    }

    #[Test]
    public function each_backup_on_a_local_backup_disk_is_labelled_from_its_manifest(): void
    {
        $this->remoteDisks();
        $this->zip('snipe-it-included.zip', ['version' => 1, 'remote_files' => 'included', 'files' => []]);
        $this->zip('snipe-it-excluded.zip', ['version' => 1, 'remote_files' => 'excluded', 'files' => []]);
        $this->zip('snipe-it-legacy.zip', null);

        $files = collect($this->page()->assertOk()->viewData('files'))->keyBy('filename');

        $this->assertSame('included', $files['snipe-it-included.zip']['remote_files']);
        $this->assertSame('excluded', $files['snipe-it-excluded.zip']['remote_files']);
        $this->assertSame('unknown', $files['snipe-it-legacy.zip']['remote_files']);
    }

    #[Test]
    public function backups_on_a_remote_backup_disk_are_listed_as_unknown_without_downloading_them(): void
    {
        $this->remoteDisks();
        $this->fakeRemoteDisk('backup');
        Storage::disk('backup')->put('backups/snipe-it-x.zip', 'zipbytes');
        \Tests\Support\S3Compatibility\FakeRemoteAdapter::$log = [];

        $files = collect($this->page()->assertOk()->viewData('files'))->keyBy('filename');

        $this->assertSame('unknown', $files['snipe-it-x.zip']['remote_files']);
        $this->assertSame([], \Tests\Support\S3Compatibility\FakeRemoteAdapter::keysFor('read'), 'listing must not read backup contents');
    }
}
