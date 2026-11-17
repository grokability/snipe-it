<?php

namespace Tests\Feature\Mcp;

use App\Http\Middleware\CheckUserIsActivated;
use App\Http\Middleware\EnforceApiTwoFactorEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * Pins the api-group protections that the /mcp/snipe-it route inherits.
 *
 * `CheckUserIsActivated` and `EnforceApiTwoFactorEnrollment` were added
 * to the Snipe-IT api middleware group for the regular REST API, but
 * the MCP route only wired `auth:api` originally. That gap let a
 * deactivated user keep hitting MCP with an existing token, and let a
 * token-holding user bypass 2FA-required installs via MCP while the
 * web and REST paths were locked down.
 *
 * These tests invoke each middleware directly against a crafted
 * request to confirm the refusal shape. End-to-end routing through
 * Laravel MCP's JSON-RPC dispatcher is covered elsewhere.
 */
class McpRouteApiProtectionsTest extends TestCase
{
    public function test_deactivated_user_is_rejected_at_the_mcp_route(): void
    {
        $user = User::factory()->create(['activated' => 0]);
        $this->actingAs($user);

        $request = Request::create('/mcp/snipe-it', 'POST');
        $request->headers->set('Authorization', 'Bearer fake-mcp-token');
        $request->setUserResolver(fn () => auth()->user());

        $response = app(CheckUserIsActivated::class)->handle($request, fn () => response('through', 200));

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function test_activated_user_passes_through(): void
    {
        $user = User::factory()->create(['activated' => 1]);
        $this->actingAs($user);

        $request = Request::create('/mcp/snipe-it', 'POST');
        $request->headers->set('Authorization', 'Bearer fake-mcp-token');
        $request->setUserResolver(fn () => auth()->user());

        $response = app(CheckUserIsActivated::class)->handle($request, fn () => response('through', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_user_without_2fa_is_rejected_when_2fa_is_required(): void
    {
        $this->settings->set(['two_factor_enabled' => '2']);

        $user = User::factory()->create([
            'activated' => 1,
            'two_factor_enrolled' => 0,
        ]);
        $this->actingAs($user);

        $request = Request::create('/mcp/snipe-it', 'POST');
        $request->setUserResolver(fn () => auth()->user());

        $response = app(EnforceApiTwoFactorEnrollment::class)->handle($request, fn () => response('through', 200));

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function test_user_with_2fa_enrolled_passes_through_even_when_2fa_is_required(): void
    {
        $this->settings->set(['two_factor_enabled' => '2']);

        $user = User::factory()->create([
            'activated' => 1,
            'two_factor_enrolled' => 1,
        ]);
        $this->actingAs($user);

        $request = Request::create('/mcp/snipe-it', 'POST');
        $request->setUserResolver(fn () => auth()->user());

        $response = app(EnforceApiTwoFactorEnrollment::class)->handle($request, fn () => response('through', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_2fa_enforcement_skips_when_setting_is_off(): void
    {
        $this->settings->set(['two_factor_enabled' => '']);

        $user = User::factory()->create([
            'activated' => 1,
            'two_factor_enrolled' => 0,
        ]);
        $this->actingAs($user);

        $request = Request::create('/mcp/snipe-it', 'POST');
        $request->setUserResolver(fn () => auth()->user());

        $response = app(EnforceApiTwoFactorEnrollment::class)->handle($request, fn () => response('through', 200));

        $this->assertSame(200, $response->getStatusCode());
    }
}
