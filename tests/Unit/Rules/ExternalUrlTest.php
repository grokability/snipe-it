<?php

namespace Tests\Unit\Rules;

use App\Rules\ExternalUrl;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ExternalUrlTest extends TestCase
{
    private function passes(string $url): bool
    {
        return Validator::make(
            ['url' => $url],
            ['url' => [new ExternalUrl]],
        )->passes();
    }

    public static function rejectedProvider(): array
    {
        return [
            // Non-http schemes are gone even if the URL is otherwise well-formed.
            'ftp scheme' => ['ftp://example.com/'],
            'irc scheme' => ['irc://example.com/'],
            'javascript scheme' => ['javascript:alert(1)'],
            'file scheme' => ['file:///etc/passwd'],
            'gopher scheme' => ['gopher://example.com/'],

            // IMDS / cloud metadata.
            'aws metadata' => ['http://169.254.169.254/latest/meta-data/'],
            'aws metadata https' => ['https://169.254.169.254/'],

            // Loopback.
            'ipv4 loopback' => ['http://127.0.0.1/'],
            'ipv4 loopback alt' => ['http://127.0.0.53/'],
            'ipv6 loopback' => ['http://[::1]/'],
            'localhost host' => ['http://localhost/'],

            // RFC-1918.
            'rfc1918 10' => ['http://10.0.0.1/'],
            'rfc1918 172' => ['http://172.16.5.5/'],
            'rfc1918 192' => ['http://192.168.1.1/'],

            // Link-local / unspecified / broadcast.
            'link local v4' => ['http://169.254.1.1/'],
            'link local v6' => ['http://[fe80::1]/'],
            'unspecified v4' => ['http://0.0.0.0/'],

            // IPv6 unique-local.
            'unique local v6' => ['http://[fc00::1]/'],
            'unique local v6 fd' => ['http://[fd12:3456::1]/'],

            // IPv4-mapped IPv6 sneak-in.
            'v4-mapped loopback' => ['http://[::ffff:127.0.0.1]/'],
            'v4-mapped rfc1918' => ['http://[::ffff:10.0.0.1]/'],

            // Malformed.
            'no scheme' => ['example.com'],
            'no host' => ['http:///'],
        ];
    }

    #[DataProvider('rejectedProvider')]
    public function test_rejects_dangerous_or_malformed_urls(string $url)
    {
        $this->assertFalse($this->passes($url), 'Should have been rejected: '.$url);
    }

    public function test_accepts_public_ipv4_literal()
    {
        // Public IP literal — no DNS lookup required.
        $this->assertTrue($this->passes('http://93.184.216.34/'));
    }

    public function test_accepts_public_ipv6_literal()
    {
        $this->assertTrue($this->passes('http://[2606:2800:220:1:248:1893:25c8:1946]/'));
    }

    public function test_internal_targets_pass_when_escape_hatch_is_enabled()
    {
        config(['app.webhook_allow_internal_targets' => true]);

        $this->assertTrue($this->passes('http://127.0.0.1/hook'));
        $this->assertTrue($this->passes('http://10.0.0.1/hook'));
        $this->assertTrue($this->passes('http://192.168.1.50:8080/hook'));
        $this->assertTrue($this->passes('http://169.254.169.254/'));
        $this->assertTrue($this->passes('http://[::1]/hook'));
    }

    public function test_escape_hatch_still_blocks_non_http_schemes()
    {
        config(['app.webhook_allow_internal_targets' => true]);

        $this->assertFalse($this->passes('ftp://example.com/'));
        $this->assertFalse($this->passes('irc://example.com/'));
        $this->assertFalse($this->passes('javascript:alert(1)'));
        $this->assertFalse($this->passes('file:///etc/passwd'));
    }
}
