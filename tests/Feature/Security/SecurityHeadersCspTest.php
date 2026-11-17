<?php

namespace Tests\Feature\Security;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class SecurityHeadersCspTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // SecurityHeaders emits the CSP header only when debug is off
        // and ENABLE_CSP is on. Flip both so the test exercises the
        // real header-emission branch.
        config([
            'app.debug' => false,
            'app.enable_csp' => 'true',
        ]);
    }

    private function runMiddleware(): Response
    {
        return (new SecurityHeaders)->handle(Request::create('/'), fn () => new Response('ok'));
    }

    public function test_csp_sets_base_uri_to_self(): void
    {
        $csp = $this->runMiddleware()->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp, 'SecurityHeaders middleware must emit a CSP header when enable_csp is true.');
        $this->assertStringContainsString("base-uri 'self'", $csp);
    }

    public function test_csp_sets_frame_ancestors_none_when_iframing_disallowed(): void
    {
        config(['app.allow_iframing' => false]);

        $csp = $this->runMiddleware()->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
    }

    public function test_csp_omits_frame_ancestors_when_iframing_allowed(): void
    {
        // When ALLOW_IFRAMING is on, admins intentionally embed
        // Snipe-IT in a parent portal. Emitting frame-ancestors here
        // would re-break the intentional iframe.
        config(['app.allow_iframing' => true]);

        $csp = $this->runMiddleware()->headers->get('Content-Security-Policy');

        $this->assertStringNotContainsString('frame-ancestors', $csp);
    }

    public function test_csp_header_is_not_emitted_when_disabled(): void
    {
        config(['app.enable_csp' => false]);

        $csp = $this->runMiddleware()->headers->get('Content-Security-Policy');

        $this->assertNull(
            $csp,
            'ENABLE_CSP=false must suppress the CSP header entirely so admins can opt out.',
        );
    }
}
