<?php

namespace Tests\Unit\Support;

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\SQLiteConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\MySqlDatabaseSnapshot;

class MySqlDatabaseSnapshotTest extends TestCase
{
    /**
     * @return array<string, array{string, list<string>, bool}>
     */
    public static function client_availability(): array
    {
        return [
            'SQLite uses normal migrations' => ['sqlite', ['mysql', 'mysqldump'], false],
            'no client tools' => ['mysql', [], false],
            'missing dump tool' => ['mysql', ['mysql'], false],
            'missing load tool' => ['mysql', ['mysqldump'], false],
            'MySQL with both tools' => ['mysql', ['mysql', 'mysqldump'], true],
            'MariaDB with both tools' => ['mariadb', ['mysql', 'mysqldump'], true],
        ];
    }

    /**
     * @param list<string> $tools
     */
    #[DataProvider('client_availability')]
    public function test_snapshot_support_requires_a_supported_driver_and_both_clients(string $driver, array $tools, bool $expected): void
    {
        $directory = sys_get_temp_dir().'/snipe-snapshot-clients-'.bin2hex(random_bytes(16));
        mkdir($directory, 0700);
        $originalPath = getenv('PATH');

        try {
            foreach ($tools as $tool) {
                file_put_contents($directory.'/'.$tool, "#!/bin/sh\nexit 0\n");
                chmod($directory.'/'.$tool, 0700);
            }
            putenv('PATH='.$directory);

            $connection = $driver === 'sqlite'
                ? new SQLiteConnection(null, ':memory:', '', ['driver' => $driver])
                : new MySqlConnection(null, 'testing', '', ['driver' => $driver]);

            $this->assertSame($expected, MySqlDatabaseSnapshot::isSupported($connection));
        } finally {
            putenv($originalPath === false ? 'PATH' : 'PATH='.$originalPath);
            foreach ($tools as $tool) {
                unlink($directory.'/'.$tool);
            }
            rmdir($directory);
        }
    }
}
