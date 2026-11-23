<?php

namespace Tests\Feature\Mcp;

use App\Http\Middleware\CheckForSetup;
use App\Models\OAuthClient;
use App\Models\Setting;
use Tests\TestCase;

class McpDynamicClientRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Setting::setupCompleted() returns false without any users, which
        // trips CheckForSetup into 302ing to /setup before our DCR
        // controller ever runs. DCR itself doesn't care about setup
        // state (it's a public endpoint called by external OAuth
        // clients), so skip that middleware for these tests.
        $this->withoutMiddleware(CheckForSetup::class);
    }

    public function test_loopback_redirect_auto_approves(): void
    {
        $response = $this->postJson('/oauth/register', [
            'client_name' => 'Claude Desktop',
            'redirect_uris' => ['http://127.0.0.1:5050/cb'],
        ])->assertCreated();

        $clientId = $response->json('client_id');
        $client = OAuthClient::find($clientId);

        $this->assertNotNull($client);
        $this->assertSame(OAuthClient::STATUS_APPROVED, $client->status);
        $this->assertNotNull($client->approved_at);
    }

    public function test_localhost_redirect_auto_approves(): void
    {
        $response = $this->postJson('/oauth/register', [
            'client_name' => 'Local MCP App',
            'redirect_uris' => ['http://localhost:4444/oauth/cb'],
        ])->assertCreated();

        $client = OAuthClient::find($response->json('client_id'));
        $this->assertSame(OAuthClient::STATUS_APPROVED, $client->status);
    }

    public function test_first_party_mobile_scheme_auto_approves(): void
    {
        $response = $this->postJson('/oauth/register', [
            'client_name' => 'Snipe-IT Mobile',
            'redirect_uris' => ['com.grokability.snipeitmobile://home'],
        ])->assertCreated();

        $client = OAuthClient::find($response->json('client_id'));
        $this->assertSame(OAuthClient::STATUS_APPROVED, $client->status);
    }

    public function test_off_box_https_redirect_lands_in_pending_queue(): void
    {
        // The specific attack path: attacker posts an off-box HTTPS
        // redirect. Pre-fix, this would either be rejected outright
        // (config lock) or go straight to approved (vendor default).
        // Post-fix, it's accepted but pending until admin review.
        $response = $this->postJson('/oauth/register', [
            'client_name' => 'Totally Legit App',
            'redirect_uris' => ['https://evil.example/cb'],
        ])->assertCreated();

        $client = OAuthClient::find($response->json('client_id'));
        $this->assertSame(OAuthClient::STATUS_PENDING, $client->status);
        $this->assertNull($client->approved_at);
    }

    public function test_untrusted_custom_scheme_lands_in_pending(): void
    {
        $response = $this->postJson('/oauth/register', [
            'client_name' => 'Third-Party Mobile',
            'redirect_uris' => ['com.thirdparty.app://cb'],
        ])->assertCreated();

        $client = OAuthClient::find($response->json('client_id'));
        $this->assertSame(OAuthClient::STATUS_PENDING, $client->status);
    }

    public function test_admin_trusted_custom_scheme_auto_approves(): void
    {
        $settings = Setting::getSettings();
        $settings->mcp_trusted_custom_schemes = "com.thirdparty.app\n# allowed per ticket #12345";
        $settings->save();

        // The service-provider merge only runs on boot. Reflect the
        // runtime config update the same way the service provider
        // would after an admin saved the settings form.
        config(['mcp.custom_schemes' => array_merge(
            (array) config('mcp.custom_schemes', []),
            ['com.thirdparty.app'],
        )]);

        $response = $this->postJson('/oauth/register', [
            'client_name' => 'Third-Party Mobile (now trusted)',
            'redirect_uris' => ['com.thirdparty.app://cb'],
        ])->assertCreated();

        $client = OAuthClient::find($response->json('client_id'));
        $this->assertSame(OAuthClient::STATUS_APPROVED, $client->status);
    }

    public function test_registered_ip_is_captured_on_pending_client(): void
    {
        $response = $this->postJson(
            '/oauth/register',
            ['client_name' => 'Pending IP Test', 'redirect_uris' => ['https://offbox.example/cb']],
            ['REMOTE_ADDR' => '203.0.113.42']
        )->assertCreated();

        $client = OAuthClient::find($response->json('client_id'));
        $this->assertSame(OAuthClient::STATUS_PENDING, $client->status);
        $this->assertSame('203.0.113.42', $client->registered_ip);
    }

    public function test_malformed_redirect_uri_is_rejected(): void
    {
        $this->postJson('/oauth/register', [
            'client_name' => 'Garbage',
            'redirect_uris' => ['not-a-uri-at-all'],
        ])->assertStatus(400)
            ->assertJsonPath('error', 'invalid_redirect_uri');
    }

    public function test_redirect_uri_with_userinfo_is_rejected(): void
    {
        $this->postJson('/oauth/register', [
            'client_name' => 'Userinfo',
            'redirect_uris' => ['https://user:pass@legit.example/cb'],
        ])->assertStatus(400);
    }
}
