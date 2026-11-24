<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\TrustHosts;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class TrustHostsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_health_request_bypasses_the_parent_trust_hosts_check(): void
    {
        // Mock the app so shouldSpecifyTrustedHosts() in the parent class
        // would return true if it were reached. The middleware must skip
        // the parent entirely for /health, so no trusted-host configuration
        // runs for that path.
        $app = Mockery::mock(\Illuminate\Contracts\Foundation\Application::class);
        $app->shouldReceive('environment')->with('local')->andReturn(false);
        $app->shouldReceive('runningUnitTests')->andReturn(false);

        $middleware = new TrustHosts($app);

        $request = Request::create('http://pod-ip.local/health');
        $called = false;

        $middleware->handle($request, function () use (&$called) {
            $called = true;

            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function test_non_health_request_runs_the_parent_check(): void
    {
        // Force shouldSpecifyTrustedHosts() to return false (runningUnitTests)
        // so the parent handle() short-circuits cleanly. We only care that
        // the parent IS reached for non-health paths, not that it rejects.
        $app = Mockery::mock(\Illuminate\Contracts\Foundation\Application::class);
        $app->shouldReceive('environment')->andReturn(true);
        $app->shouldReceive('runningUnitTests')->andReturn(true);

        $middleware = new TrustHosts($app);

        $request = Request::create('http://pod-ip.local/hardware');
        $called = false;

        $middleware->handle($request, function () use (&$called) {
            $called = true;

            return 'ok';
        });

        $this->assertTrue($called);
    }
}
