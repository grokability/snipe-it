<?php

namespace Tests\Unit;

use PDO;
use Tests\TestCase;

/**
 * Coverage for the PDO SSL options block in config/database.php. Loads the
 * real config file under different env combinations rather than a hand-rolled
 * simulator, so the tests would actually catch a regression in the file
 * itself (which the earlier simulator-based version could not, see #19411).
 */
class DatabaseSslConfigurationTest extends TestCase
{
    /**
     * Every DB_SSL_* key this test manipulates. The snapshot/restore in
     * setUp and the reset in loadDatabaseConfig must cover the exact same
     * set: any key mutated but not restored would bake into the *next*
     * test's setUp config boot and break its mysql connection with "Cannot
     * connect to MySQL using SSL". A single source of truth keeps them in sync.
     */
    private const SSL_KEYS = [
        'DB_SSL',
        'DB_SSL_IS_PAAS',
        'DB_SSL_KEY_PATH',
        'DB_SSL_CERT_PATH',
        'DB_SSL_CA_PATH',
        'DB_SSL_CIPHER',
        'DB_SSL_VERIFY_SERVER',
        'DB_DUMP_SKIP_SSL',
        'DB_DUMP_SSL_FLAG',
        'DB_DUMP_SINGLE_TRANSACTION',
    ];

    private array $originalSslEnv = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->snapshotSslEnv();

        $this->beforeApplicationDestroyed(fn () => $this->restoreSslEnv());
    }

    /**
     * Record every SSL env key as it stands before this test mutates it.
     */
    private function snapshotSslEnv(): void
    {
        foreach (self::SSL_KEYS as $key) {
            $this->originalSslEnv[$key] = [
                'env' => $_ENV[$key] ?? false,
                'server' => $_SERVER[$key] ?? false,
                'getenv' => getenv($key),
            ];
        }
    }

    /**
     * Put every SSL env key back exactly as it was before this test ran, so
     * a mutated value can't bake into the next test's config boot.
     */
    private function restoreSslEnv(): void
    {
        foreach ($this->originalSslEnv as $key => $original) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);

            if ($original['env'] !== false) {
                $_ENV[$key] = $original['env'];
            }
            if ($original['server'] !== false) {
                $_SERVER[$key] = $original['server'];
            }
            if ($original['getenv'] !== false) {
                putenv("$key={$original['getenv']}");
            }
        }
    }

    /**
     * Set the env vars this test needs, re-require the config file, and
     * return the fully-built connections array. Any env key not passed is
     * unset so it doesn't leak in from the surrounding test env.
     *
     * @param  array<string, string|null>  $env
     * @return array<string, mixed>
     */
    private function loadDatabaseConfig(array $env): array
    {
        foreach (self::SSL_KEYS as $key) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }

        foreach ($env as $key => $value) {
            if ($value === null) {
                $_ENV[$key] = 'null';
                $_SERVER[$key] = 'null';
                putenv("$key=null");
            } else {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv("$key=$value");
            }
        }

        return require base_path('config/database.php');
    }

    public function test_ssl_disabled_yields_no_pdo_options(): void
    {
        $config = $this->loadDatabaseConfig(['DB_SSL' => 'false']);

        $this->assertSame([], $config['connections']['mysql']['options']);
        $this->assertSame([], $config['connections']['mariadb']['options']);
    }

    public function test_ssl_enabled_paas_mode_includes_ca_and_verify_only(): void
    {
        $config = $this->loadDatabaseConfig([
            'DB_SSL' => 'true',
            'DB_SSL_IS_PAAS' => 'true',
            'DB_SSL_CA_PATH' => '/path/to/ca.pem',
            'DB_SSL_VERIFY_SERVER' => 'true',
        ]);

        $options = $config['connections']['mysql']['options'];

        $this->assertSame('/path/to/ca.pem', $options[PDO::MYSQL_ATTR_SSL_CA]);
        $this->assertTrue($options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]);
        $this->assertArrayNotHasKey(PDO::MYSQL_ATTR_SSL_KEY, $options);
        $this->assertArrayNotHasKey(PDO::MYSQL_ATTR_SSL_CERT, $options);
        $this->assertArrayNotHasKey(PDO::MYSQL_ATTR_SSL_CIPHER, $options);
    }

    public function test_ssl_enabled_self_hosted_mode_includes_full_client_cert_set(): void
    {
        $config = $this->loadDatabaseConfig([
            'DB_SSL' => 'true',
            'DB_SSL_KEY_PATH' => '/path/to/key.pem',
            'DB_SSL_CERT_PATH' => '/path/to/cert.pem',
            'DB_SSL_CA_PATH' => '/path/to/ca.pem',
            'DB_SSL_CIPHER' => 'ECDHE-RSA-AES256-GCM-SHA384',
        ]);

        $options = $config['connections']['mysql']['options'];

        $this->assertSame('/path/to/key.pem', $options[PDO::MYSQL_ATTR_SSL_KEY]);
        $this->assertSame('/path/to/cert.pem', $options[PDO::MYSQL_ATTR_SSL_CERT]);
        $this->assertSame('/path/to/ca.pem', $options[PDO::MYSQL_ATTR_SSL_CA]);
        $this->assertSame('ECDHE-RSA-AES256-GCM-SHA384', $options[PDO::MYSQL_ATTR_SSL_CIPHER]);
    }

    public function test_null_cipher_is_omitted_not_passed_as_null(): void
    {
        // Regression for #19411. .env.example ships DB_SSL_CIPHER=null and
        // the old code passed that through as PDO::MYSQL_ATTR_SSL_CIPHER =>
        // null, which made libmysql / libmariadb fail with "Cannot connect
        // to MySQL using SSL". A null cipher MUST be omitted so the driver
        // negotiates a default.
        $config = $this->loadDatabaseConfig([
            'DB_SSL' => 'true',
            'DB_SSL_KEY_PATH' => '/path/to/key.pem',
            'DB_SSL_CERT_PATH' => '/path/to/cert.pem',
            'DB_SSL_CA_PATH' => '/path/to/ca.pem',
            'DB_SSL_CIPHER' => null,
        ]);

        $options = $config['connections']['mysql']['options'];

        $this->assertArrayNotHasKey(PDO::MYSQL_ATTR_SSL_CIPHER, $options);
    }

    public function test_null_paths_are_omitted_across_every_ssl_key(): void
    {
        // Same shape trap as CIPHER: any of the path envs left at the
        // .env.example default of `null` would land as PDO::MYSQL_ATTR_SSL_*
        // => null and break the SSL handshake. Every path key gets the
        // omit-when-null treatment.
        $config = $this->loadDatabaseConfig([
            'DB_SSL' => 'true',
            'DB_SSL_KEY_PATH' => null,
            'DB_SSL_CERT_PATH' => null,
            'DB_SSL_CA_PATH' => null,
            'DB_SSL_CIPHER' => null,
        ]);

        $options = $config['connections']['mysql']['options'];

        $this->assertArrayNotHasKey(PDO::MYSQL_ATTR_SSL_KEY, $options);
        $this->assertArrayNotHasKey(PDO::MYSQL_ATTR_SSL_CERT, $options);
        $this->assertArrayNotHasKey(PDO::MYSQL_ATTR_SSL_CA, $options);
        $this->assertArrayNotHasKey(PDO::MYSQL_ATTR_SSL_CIPHER, $options);
        // VERIFY_SERVER_CERT is always included with a bool cast because
        // both true and false are valid values.
        $this->assertArrayHasKey(PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT, $options);
        $this->assertFalse($options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]);
    }

    public function test_mariadb_connection_shares_the_same_ssl_options(): void
    {
        $config = $this->loadDatabaseConfig([
            'DB_SSL' => 'true',
            'DB_SSL_KEY_PATH' => '/path/to/key.pem',
            'DB_SSL_CERT_PATH' => '/path/to/cert.pem',
            'DB_SSL_CA_PATH' => '/path/to/ca.pem',
        ]);

        $this->assertSame(
            $config['connections']['mysql']['options'],
            $config['connections']['mariadb']['options']
        );
    }

    public function test_dump_skip_ssl_true_adds_skip_ssl_to_dump_options(): void
    {
        // Regression for #20117. env() returns a boolean for `true`, so the
        // old `=== 'true'` check never matched and the flag did nothing.
        $config = $this->loadDatabaseConfig(['DB_DUMP_SKIP_SSL' => 'true']);

        $this->assertTrue($config['connections']['mysql']['dump']['skip_ssl']);
        $this->assertTrue($config['connections']['mariadb']['dump']['skip_ssl']);
    }

    public function test_dump_skip_ssl_unset_or_false_leaves_skip_ssl_out(): void
    {
        $config = $this->loadDatabaseConfig([]);
        $this->assertArrayNotHasKey('skip_ssl', $config['connections']['mysql']['dump']);

        $config = $this->loadDatabaseConfig(['DB_DUMP_SKIP_SSL' => 'false']);
        $this->assertArrayNotHasKey('skip_ssl', $config['connections']['mysql']['dump']);
    }

    public function test_dump_ssl_flag_overrides_the_server_version_guess(): void
    {
        // Regression for #20155. mariadb-dump rejects the ssl-mode=DISABLED that
        // spatie picks for a MySQL 8.4+ server.
        $config = $this->loadDatabaseConfig(['DB_DUMP_SKIP_SSL' => 'true', 'DB_DUMP_SSL_FLAG' => 'skip-ssl']);
        $this->assertSame('skip-ssl', $config['connections']['mysql']['dump']['ssl_flag']);

        $config = $this->loadDatabaseConfig(['DB_DUMP_SKIP_SSL' => 'true']);
        $this->assertArrayNotHasKey('ssl_flag', $config['connections']['mysql']['dump']);
    }

    public function test_dump_single_transaction_true_adds_it_to_dump_options(): void
    {
        $config = $this->loadDatabaseConfig(['DB_DUMP_SINGLE_TRANSACTION' => 'true']);

        $this->assertTrue($config['connections']['mysql']['dump']['use_single_transaction']);
        $this->assertTrue($config['connections']['mariadb']['dump']['use_single_transaction']);
    }

    public function test_dump_single_transaction_unset_or_false_leaves_it_out(): void
    {
        // A `false` value would reach spatie's callMethodOnDumper, which calls
        // useSingleTransaction() with no args and enables it (see #19120).
        $config = $this->loadDatabaseConfig([]);
        $this->assertArrayNotHasKey('use_single_transaction', $config['connections']['mysql']['dump']);

        $config = $this->loadDatabaseConfig(['DB_DUMP_SINGLE_TRANSACTION' => 'false']);
        $this->assertArrayNotHasKey('use_single_transaction', $config['connections']['mysql']['dump']);
    }
}
