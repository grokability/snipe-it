<?php

namespace Tests\Feature\S3Compatibility;

use App\Http\Requests\ItemImportRequest;
use App\Models\Import;
use App\Models\Location;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\S3Compatibility\FakeRemoteAdapter;
use Tests\Support\S3Compatibility\UsesFakeRemoteDisks;
use Tests\TestCase;

/**
 * FR-7 (ImportLocations), FR-8 (backup disk options), FR-9 (backup monitor), FR-10 (exit code and
 * import temp file).
 */
class MiscRemediationTest extends TestCase
{
    use UsesFakeRemoteDisks;

    private string $privateRoot;

    private const BACKUP_ENV = [
        'BACKUP_AWS_ACCESS_KEY_ID', 'BACKUP_AWS_SECRET_ACCESS_KEY', 'BACKUP_AWS_SESSION_TOKEN',
        'BACKUP_AWS_DEFAULT_REGION', 'BACKUP_AWS_BUCKET', 'BACKUP_AWS_ENDPOINT', 'BACKUP_AWS_URL',
        'BACKUP_AWS_PATH_STYLE', 'PRIVATE_AWS_ACCESS_KEY_ID', 'PRIVATE_AWS_SECRET_ACCESS_KEY',
        'PRIVATE_AWS_SESSION_TOKEN', 'PRIVATE_AWS_DEFAULT_REGION', 'PRIVATE_AWS_BUCKET',
        'PRIVATE_AWS_ENDPOINT', 'PRIVATE_AWS_URL', 'PRIVATE_AWS_PATH_STYLE', 'BACKUP_FILESYSTEM_DRIVER',
    ];

    /** @var array<string, mixed> */
    private array $savedEnv = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::BACKUP_ENV as $name) {
            $this->savedEnv[$name] = [array_key_exists($name, $_ENV), $_ENV[$name] ?? null, array_key_exists($name, $_SERVER), $_SERVER[$name] ?? null];
            unset($_ENV[$name], $_SERVER[$name]);
        }

        $this->privateRoot = $this->fakeRemoteDisk('custom_private');
        config(['filesystems.default' => 'custom_private']);
        Storage::forgetDisk('custom_private');
    }

    protected function tearDown(): void
    {
        foreach ($this->savedEnv as $name => [$hadEnv, $env, $hadServer, $server]) {
            $hadEnv ? $_ENV[$name] = $env : null;
            $hadServer ? $_SERVER[$name] = $server : null;
        }
        $this->cleanUpFakeRemoteDisks();

        parent::tearDown();
    }

    private function freshFilesystemsConfig(array $env): array
    {
        foreach ($env as $name => $value) {
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        return require config_path('filesystems.php');
    }

    private function importsKey(string $name): string
    {
        return 'private_uploads/imports/'.$name;
    }

    // ---- FR-7 -------------------------------------------------------------------------------

    #[Test]
    public function import_locations_reads_the_csv_from_the_configured_private_disk(): void
    {
        $name = 'locations-'.bin2hex(random_bytes(3)).'.csv';
        $location = 'S3 Compat Site '.bin2hex(random_bytes(3));
        Storage::disk('custom_private')->put($this->importsKey($name), "Name,Parent Name\n\"$location\",\n");

        $this->artisan('snipeit:import-locations', ['filename' => $name])->assertExitCode(0);

        $this->assertTrue(Location::where('name', $location)->exists());
    }

    #[Test]
    public function import_locations_fails_when_the_file_is_missing(): void
    {
        $this->artisan('snipeit:import-locations', ['filename' => 'does-not-exist-'.bin2hex(random_bytes(3)).'.csv'])
            ->assertExitCode(1);
    }

    // ---- FR-8 -------------------------------------------------------------------------------

    #[Test]
    public function backup_disk_gains_the_s3_options_and_inherits_private_values(): void
    {
        $disks = $this->freshFilesystemsConfig([
            'PRIVATE_AWS_ACCESS_KEY_ID' => 'private-key',
            'PRIVATE_AWS_SECRET_ACCESS_KEY' => 'private-secret',
            'PRIVATE_AWS_SESSION_TOKEN' => 'private-token',
            'PRIVATE_AWS_DEFAULT_REGION' => 'eu-west-1',
            'PRIVATE_AWS_BUCKET' => 'private-bucket',
            'PRIVATE_AWS_ENDPOINT' => 'https://s3.example.invalid',
            'PRIVATE_AWS_URL' => 'https://cdn.example.invalid',
            'PRIVATE_AWS_PATH_STYLE' => 'true',
        ])['disks']['backup'];

        $this->assertSame('private-key', $disks['key']);
        $this->assertSame('private-secret', $disks['secret']);
        $this->assertSame('private-token', $disks['token']);
        $this->assertSame('https://s3.example.invalid', $disks['endpoint']);
        $this->assertSame('https://cdn.example.invalid', $disks['url']);
        $this->assertTrue((bool) $disks['use_path_style_endpoint']);
        $this->assertSame('private-bucket', $disks['bucket']);
        $this->assertSame('eu-west-1', $disks['region']);
        $this->assertArrayNotHasKey('visibility', $disks);
        $this->assertArrayNotHasKey('http', $disks);
    }

    #[Test]
    public function backup_overrides_win_and_do_not_mix_with_private_credentials(): void
    {
        $disk = $this->freshFilesystemsConfig([
            'PRIVATE_AWS_ACCESS_KEY_ID' => 'private-key',
            'PRIVATE_AWS_SECRET_ACCESS_KEY' => 'private-secret',
            'PRIVATE_AWS_SESSION_TOKEN' => 'private-token',
            'BACKUP_AWS_ACCESS_KEY_ID' => 'backup-key',
            'BACKUP_AWS_ENDPOINT' => 'https://backup.example.invalid',
        ])['disks']['backup'];

        $this->assertSame('backup-key', $disk['key']);
        $this->assertNull($disk['secret'], 'a backup key must not be paired with the private secret');
        $this->assertNull($disk['token'], 'a backup key must not be paired with the private session token');
        $this->assertSame('https://backup.example.invalid', $disk['endpoint']);
    }

    #[Test]
    public function without_any_credentials_the_default_credential_chain_is_used(): void
    {
        $disk = $this->freshFilesystemsConfig([])['disks']['backup'];

        $this->assertNull($disk['key']);
        $this->assertNull($disk['secret']);
        $this->assertNull($disk['token']);
    }

    // ---- FR-9 -------------------------------------------------------------------------------

    #[Test]
    public function backup_monitor_watches_the_disk_and_name_the_backup_writes_to(): void
    {
        $this->assertSame(config('backup.backup.name'), config('backup.monitor_backups.0.name'));
        $this->assertSame(config('backup.backup.destination.disks'), config('backup.monitor_backups.0.disks'));
    }

    // ---- FR-10 ------------------------------------------------------------------------------

    #[Test]
    public function snipeit_backup_passes_on_the_exit_code_of_backup_run(): void
    {
        // A command name can only be registered once, so one stub returns whatever the test sets.
        $exit = 1;
        Artisan::command('backup:run {--filename=}', function () use (&$exit) {
            return $exit;
        })->purpose('stub');

        $this->artisan('snipeit:backup')->assertExitCode(1);

        $exit = 0;
        $this->artisan('snipeit:backup')->assertExitCode(0);
    }

    #[Test]
    public function a_truncated_import_file_is_reported_instead_of_processed(): void
    {
        $name = 'truncated-'.bin2hex(random_bytes(3)).'.csv';
        $site = 'Truncated Site '.bin2hex(random_bytes(3));
        $csv = "Name\n".$site."\n".str_repeat("Filler Site\n", 50);
        Storage::disk('custom_private')->put($this->importsKey($name), $csv);
        FakeRemoteAdapter::$truncateReads = [$this->importsKey($name)];

        $import = Import::factory()->locations()->create(['file_path' => $name]);
        $request = ItemImportRequest::create('/', 'POST', ['import-type' => 'location']);
        $errors = $request->import($import);

        $this->assertIsArray($errors);
        $this->assertArrayHasKey('file', $errors[$import->name] ?? [], 'a short copy must be a file error');
        $this->assertFalse(Location::where('name', $site)->exists());
    }

    #[Test]
    public function a_complete_import_file_is_still_processed(): void
    {
        $name = 'complete-'.bin2hex(random_bytes(3)).'.csv';
        $site = 'Complete Site '.bin2hex(random_bytes(3));
        Storage::disk('custom_private')->put($this->importsKey($name), "Name\n".$site."\n");

        $import = Import::factory()->locations()->create(['file_path' => $name]);
        $request = ItemImportRequest::create('/', 'POST', [
            'import-type' => 'location',
            'column-mappings' => ['Name' => 'name'],
        ]);
        $errors = $request->import($import);

        $this->assertEmpty($errors);
        $this->assertTrue(Location::where('name', $site)->exists());
    }
}
