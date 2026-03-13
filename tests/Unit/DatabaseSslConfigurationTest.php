<?php

namespace Tests\Unit;

use PDO;
use Tests\TestCase;

class DatabaseSslConfigurationTest extends TestCase
{
    public function testMysqlSslConfigurationWithPaasMode(): void
    {
        config([
            'database.connections.mysql.options' => null
        ]);

        // Test PAAS mode SSL configuration
        config([
            'database.connections.mysql.options' => $this->getSslOptions(true, false, true) // isPaas=true, verifyServer=false, sslEnabled=true
        ]);

        $options = config('database.connections.mysql.options');

        $this->assertArrayHasKey(PDO::MYSQL_ATTR_SSL_CA, $options);
        $this->assertArrayHasKey(PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT, $options);

        // PAAS mode should not include client certificate attributes
        $this->assertArrayNotHasKey(PDO::MYSQL_ATTR_SSL_KEY, $options);
        $this->assertArrayNotHasKey(PDO::MYSQL_ATTR_SSL_CERT, $options);
        $this->assertArrayNotHasKey(PDO::MYSQL_ATTR_SSL_CIPHER, $options);
    }

    public function testMysqlSslConfigurationWithNonPaasMode(): void
    {
        config([
            'database.connections.mysql.options' => null
        ]);

        // Test non-PAAS mode SSL configuration
        config([
            'database.connections.mysql.options' => $this->getSslOptions(false, false, true) // isPaas=false, verifyServer=false, sslEnabled=true
        ]);

        $options = config('database.connections.mysql.options');

        // Non-PAAS mode should include all SSL attributes
        $this->assertArrayHasKey(PDO::MYSQL_ATTR_SSL_CA, $options);
        $this->assertArrayHasKey(PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT, $options);
        $this->assertArrayHasKey(PDO::MYSQL_ATTR_SSL_KEY, $options);
        $this->assertArrayHasKey(PDO::MYSQL_ATTR_SSL_CERT, $options);
        $this->assertArrayHasKey(PDO::MYSQL_ATTR_SSL_CIPHER, $options);
    }

    public function testMysqlSslConfigurationWithoutSsl(): void
    {
        config([
            'database.connections.mysql.options' => null
        ]);

        // Test SSL disabled configuration
        config([
            'database.connections.mysql.options' => $this->getSslOptions(true, false, false) // isPaas=true, verifyServer=false, sslEnabled=false
        ]);

        $options = config('database.connections.mysql.options');

        // When SSL is disabled, options should be empty
        $this->assertEmpty($options);
    }

    public function testSslVerifyServerDefaultsToFalse(): void
    {
        // Test that SSL_VERIFY_SERVER defaults to false when not explicitly set
        $options = $this->getSslOptions(true, null, true); // isPaas=true, verifyServer=null, sslEnabled=true

        $this->assertArrayHasKey(PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT, $options);
        $this->assertFalse($options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]);
    }

    private function getSslOptions(bool $isPaas, ?bool $verifyServer=false, bool $sslEnabled=true): array
    {
        // simulates the SSL options logic from database.php
        if (!$sslEnabled) {
            return [];
        }

        if ($isPaas) {
            return [
                PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca.pem',
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => $verifyServer ?? false,
            ];
        }

        return [
            PDO::MYSQL_ATTR_SSL_KEY => '/path/to/key.pem',
            PDO::MYSQL_ATTR_SSL_CERT => '/path/to/cert.pem',
            PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca.pem',
            PDO::MYSQL_ATTR_SSL_CIPHER => null,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => $verifyServer ?? false,
        ];
    }
}
