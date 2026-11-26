<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\CheckForSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustHostsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Laravel skips host validation in local and testing environments.
        $this->app->instance('env', 'production');
        config([
            'app.url' => 'http://trusted.example.test',
            'app.allow_insecure_hosts' => false,
        ]);
        $this->withoutMiddleware(CheckForSetup::class);

        Route::get('/__test/trusted-host', function (Request $request) {
            return response()->json(['host' => $request->getHost()]);
        });
    }

    protected function tearDown(): void
    {
        Request::setTrustedHosts([]);

        parent::tearDown();
    }

    public function test_health_request_accepts_an_untrusted_host(): void
    {
        $this->get('http://127.0.0.1/health')
            ->assertOk()
            ->assertCookieMissing(config('session.cookie'));

        $this->assertSame('127.0.0.1', $this->app['request']->getHost());
        $this->assertFalse($this->app['request']->hasSession());
    }

    public function test_health_exception_does_not_allow_untrusted_hosts_on_other_requests(): void
    {
        $this->get('http://127.0.0.1/health')->assertOk();

        $this->get('http://untrusted.example.test/__test/trusted-host')->assertStatus(400);
    }

    public function test_other_requests_reject_an_untrusted_host(): void
    {
        $this->get('http://untrusted.example.test/__test/trusted-host')->assertStatus(400);
    }

    public function test_other_requests_accept_the_application_host(): void
    {
        $this->get('http://trusted.example.test/__test/trusted-host')
            ->assertOk()
            ->assertJsonPath('host', 'trusted.example.test');
    }

    public function test_allow_insecure_hosts_accepts_an_untrusted_host(): void
    {
        config(['app.allow_insecure_hosts' => true]);

        $this->get('http://untrusted.example.test/__test/trusted-host')
            ->assertOk()
            ->assertJsonPath('host', 'untrusted.example.test');
    }
}
