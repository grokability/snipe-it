<?php

namespace Tests\Feature\Console;

use App\Console\Commands\RestoreFromBackup;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RestoreFromBackupBinaryPickerTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir().'/snipeit-restore-picker-'.uniqid();
        mkdir($this->tmpDir, 0755, true);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->tmpDir.'/*') ?: []);
        rmdir($this->tmpDir);
        parent::tearDown();
    }

    private function makeBinary(string $name): void
    {
        touch($this->tmpDir.\DIRECTORY_SEPARATOR.$name.(\DIRECTORY_SEPARATOR === '\\' ? '.exe' : ''));
    }

    public function test_mariadb_driver_prefers_mariadb_binary(): void
    {
        $this->makeBinary('mysql');
        $this->makeBinary('mariadb');

        $picked = RestoreFromBackup::pickDbClientBinary('mariadb', $this->tmpDir);

        $this->assertSame($this->tmpDir.\DIRECTORY_SEPARATOR.'mariadb'.(\DIRECTORY_SEPARATOR === '\\' ? '.exe' : ''), $picked);
    }

    public function test_mysql_driver_prefers_mysql_binary(): void
    {
        $this->makeBinary('mysql');
        $this->makeBinary('mariadb');

        $picked = RestoreFromBackup::pickDbClientBinary('mysql', $this->tmpDir);

        $this->assertSame($this->tmpDir.\DIRECTORY_SEPARATOR.'mysql'.(\DIRECTORY_SEPARATOR === '\\' ? '.exe' : ''), $picked);
    }

    public function test_mariadb_driver_falls_back_to_mysql_binary_when_mariadb_missing(): void
    {
        $this->makeBinary('mysql');

        $picked = RestoreFromBackup::pickDbClientBinary('mariadb', $this->tmpDir);

        $this->assertSame($this->tmpDir.\DIRECTORY_SEPARATOR.'mysql'.(\DIRECTORY_SEPARATOR === '\\' ? '.exe' : ''), $picked);
    }

    public function test_mysql_driver_falls_back_to_mariadb_binary_when_mysql_missing(): void
    {
        $this->makeBinary('mariadb');

        $picked = RestoreFromBackup::pickDbClientBinary('mysql', $this->tmpDir);

        $this->assertSame($this->tmpDir.\DIRECTORY_SEPARATOR.'mariadb'.(\DIRECTORY_SEPARATOR === '\\' ? '.exe' : ''), $picked);
    }

    public function test_returns_null_when_no_binary_exists(): void
    {
        $picked = RestoreFromBackup::pickDbClientBinary('mysql', $this->tmpDir);

        $this->assertNull($picked);
    }

    public function test_trailing_separator_on_path_does_not_double(): void
    {
        $this->makeBinary('mariadb');

        $picked = RestoreFromBackup::pickDbClientBinary('mariadb', $this->tmpDir.\DIRECTORY_SEPARATOR);

        $this->assertSame($this->tmpDir.\DIRECTORY_SEPARATOR.'mariadb'.(\DIRECTORY_SEPARATOR === '\\' ? '.exe' : ''), $picked);
    }

    public function test_config_exposes_mariadb_connection_with_correct_driver(): void
    {
        $this->assertSame('mariadb', config('database.connections.mariadb.driver'));
    }

    public function test_public_files_covers_current_branding_upload_shape(): void
    {
        // Regression for #19503. ImageUploadRequest::handleImages produces
        // filenames like Setting-<field><id>-<random>.<ext> for the branding
        // fields, and the restore classifier uses strrpos (case-sensitive)
        // to match. Both cases must be present or the branding files get
        // silently dropped during restore.
        $this->assertContains('public/uploads/Setting-*', RestoreFromBackup::PUBLIC_FILES);
        $this->assertContains('public/uploads/setting-*', RestoreFromBackup::PUBLIC_FILES);
    }

    public function test_skip_ssl_is_absent_from_dump_config_by_default(): void
    {
        // Regression: spatie's processExtraDumpParameters iterates the dump
        // config and treats a `false` value as "call the setter with no args",
        // which for setSkipSsl(bool $x = true) silently sets skipSsl to TRUE.
        // The key must be absent from the array (not set to false) when opted
        // out, matching the old add_extra_option gating shape.
        $this->assertArrayNotHasKey('skip_ssl', config('database.connections.mysql.dump'));
        $this->assertArrayNotHasKey('skip_ssl', config('database.connections.mariadb.dump'));
        $this->assertArrayNotHasKey('add_extra_option', config('database.connections.mysql.dump'));
        $this->assertArrayNotHasKey('add_extra_option', config('database.connections.mariadb.dump'));
    }

    public function test_restore_launches_database_client_with_direct_arguments(): void
    {
        $this->assertRestoreClientInvocation();
    }

    public function test_restore_uses_socket_database_client_arguments(): void
    {
        $this->assertRestoreClientInvocation('/tmp/restore-test.sock');
    }

    public function test_restore_does_not_report_success_when_database_client_cannot_launch(): void
    {
        $this->assertRestoreClientInvocation(launchFailure: true);
    }

    private function assertRestoreClientInvocation(?string $socket = null, bool $launchFailure = false): void
    {
        if (\DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('The executable PHP stub uses a Unix shebang.');
        }

        $clientBinary = $this->tmpDir.'/mysql';
        $capturePath = $this->tmpDir.'/client-invocation.json';
        $archivePath = $this->tmpDir.'/backup.zip';
        $sql = "SELECT 'restore process test';\n";
        $clientScript = <<<'PHP'
<?php
$capture = [
    'arguments' => array_slice($_SERVER['argv'], 1),
    'stdin' => stream_get_contents(STDIN),
    'mysql_password' => getenv('MYSQL_PWD'),
];
file_put_contents(getenv('RESTORE_CLIENT_CAPTURE'), json_encode($capture, JSON_THROW_ON_ERROR));
fwrite(STDOUT, "fake database client stdout\n");
fwrite(STDERR, "fake database client stderr\n");
exit((int) getenv('RESTORE_CLIENT_EXIT_CODE'));
PHP;

        // The picker accepts this executable file, but exec fails naturally
        // when its interpreter is absent. No real database client is used.
        $interpreter = $launchFailure ? '/no-such-snipeit-restore-interpreter' : PHP_BINARY;
        $this->assertNotFalse(file_put_contents($clientBinary, '#!'.$interpreter."\n".$clientScript));
        $this->assertTrue(chmod($clientBinary, 0755));

        $archive = new \ZipArchive;
        $this->assertSame(true, $archive->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        $this->assertTrue($archive->addFromString('db-dumps/backup.sql', $sql));
        $this->assertTrue($archive->close());

        $originalDatabaseConfig = config('database.connections.mysql');
        $originalDatabaseDefault = config('database.default');
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.driver' => 'mysql',
            'database.connections.mysql.host' => 'restore-test-host',
            'database.connections.mysql.port' => 3307,
            'database.connections.mysql.unix_socket' => $socket,
            'database.connections.mysql.database' => 'restore_test',
            'database.connections.mysql.username' => 'restore_test_user',
            'database.connections.mysql.password' => 'restore-test-password',
            'database.connections.mysql.dump.dump_binary_path' => $this->tmpDir,
        ]);

        $previousCapturePath = getenv('RESTORE_CLIENT_CAPTURE');
        $previousClientExitCode = getenv('RESTORE_CLIENT_EXIT_CODE');
        putenv('RESTORE_CLIENT_CAPTURE='.$capturePath);
        putenv('RESTORE_CLIENT_EXIT_CODE=23');

        try {
            $arguments = [
                '--force' => true,
                '--no-progress' => true,
                'filename' => $archivePath,
            ];
            if ($launchFailure) {
                // Unlike Artisan::call(), the console kernel renders launch
                // exceptions and returns the command-line failure status.
                $buffer = new \Symfony\Component\Console\Output\BufferedOutput;
                $exitCode = $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->handle(
                    new \Symfony\Component\Console\Input\ArrayInput(['command' => 'snipeit:restore', ...$arguments]),
                    $buffer,
                );
                $output = $buffer->fetch();
            } else {
                $exitCode = Artisan::call('snipeit:restore', $arguments);
                $output = Artisan::output();
            }
        } finally {
            putenv($previousCapturePath === false ? 'RESTORE_CLIENT_CAPTURE' : 'RESTORE_CLIENT_CAPTURE='.$previousCapturePath);
            putenv($previousClientExitCode === false ? 'RESTORE_CLIENT_EXIT_CODE' : 'RESTORE_CLIENT_EXIT_CODE='.$previousClientExitCode);
            config([
                'database.default' => $originalDatabaseDefault,
                'database.connections.mysql' => $originalDatabaseConfig,
            ]);
        }

        if ($launchFailure) {
            $this->assertNotSame(0, $exitCode);
            $this->assertFileDoesNotExist($capturePath);
            $this->assertMatchesRegularExpression('/(proc_open\(\).*No such file or directory|Error number 127)/s', $output);
            $this->assertStringNotContainsString('fake database client stdout', $output);
            $this->assertStringNotContainsString('fake database client stderr', $output);
            $this->assertStringNotContainsString('files were succesfully transferred', $output);

            return;
        }

        $this->assertSame(23, $exitCode);
        $this->assertStringContainsString('fake database client stdout', $output);
        $this->assertStringContainsString('fake database client stderr', $output);

        $invocation = json_decode(file_get_contents($capturePath), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame([
            '-h', 'restore-test-host', '--batch', '--binary-mode', '-u', 'restore_test_user',
            ...($socket === null ? ['-P', '3307'] : ['-S', $socket]), 'restore_test',
        ], $invocation['arguments']);
        $this->assertSame($sql, $invocation['stdin']);
        $this->assertSame('restore-test-password', $invocation['mysql_password']);
    }
}
