<?php

namespace App\Http\Controllers\Settings;

use App\Models\OAuthClient;
use App\Models\User;
use App\Notifications\PendingOauthClientNotification;
use Illuminate\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Mcp\Server\Http\Controllers\OAuthRegisterController;
use Laravel\Mcp\Server\Registrar;
use Throwable;

/**
 * Snipe-IT's Dynamic Client Registration controller. Replaces the
 * laravel/mcp vendor controller so we can take the pending-approval
 * path instead of hard-rejecting off-allow-list redirect URIs:
 *
 *   - Loopback redirects (http://localhost, 127.0.0.1, [::1]) are
 *     auto-approved. Attackers can't intercept callbacks delivered to
 *     the victim's own device loopback.
 *   - Custom URI schemes in the admin-configured trusted list are
 *     auto-approved. The first-party mobile app scheme
 *     (com.grokability.snipeitmobile) ships in the trusted list by
 *     default so it works out of the box.
 *   - Admin-configured HTTPS redirect-domain prefixes are auto-
 *     approved. Snipe-IT shops that run their own hosted MCP client
 *     can pre-trust their callback host via Settings -> MCP.
 *   - Everything else lands in the pending queue (status=pending)
 *     and cannot complete the authorize/token exchange until a
 *     superuser approves it in the admin UI. Attacker redirects with
 *     off-box HTTPS hosts land here.
 *
 * Validation on the shape of the request mirrors the vendor
 * controller. The one semantic change is that redirect URIs don't have
 * to be in the pre-trusted list to pass validation. They just have to
 * be a well-formed URI. The status field is the gate, not validation.
 */
class McpOAuthRegisterController extends OAuthRegisterController
{
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_name' => ['nullable', 'string', 'min:1', 'max:255'],
            'name' => ['nullable', 'string', 'min:1', 'max:255'],
            'redirect_uris' => ['required', 'array', 'min:1', 'max:10'],
            'redirect_uris.*' => ['bail', 'required', 'string', 'max:1024', function (string $attribute, $value, $fail): void {
                if (! $this->isWellFormedRedirectUri($value)) {
                    $fail($attribute.' is not a valid URL.');
                }
            }],
            'logo_uri' => ['nullable', 'string', 'url:http,https', 'max:2048'],
            'client_uri' => ['nullable', 'string', 'url:http,https', 'max:2048'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $redirectError = $errors->first('redirect_uris*');

            return response()->json([
                'error' => $redirectError !== '' ? 'invalid_redirect_uri' : 'invalid_client_metadata',
                'error_description' => $redirectError !== '' ? $redirectError : $errors->first(),
            ], 400);
        }

        $validated = $validator->validated();

        if (! class_exists('Laravel\Passport\ClientRepository')) {
            return response()->json([
                'error' => 'server_error',
                'error_description' => 'OAuth support (Passport) is not installed.',
            ], 500);
        }

        $clients = Container::getInstance()->make('Laravel\Passport\ClientRepository');

        try {
            $client = $clients->createAuthorizationCodeGrantClient(
                name: $this->resolveClientName($validated),
                redirectUris: $validated['redirect_uris'],
                confidential: false,
                enableDeviceFlow: false,
            );

            $this->grantMcpScope($client);
            $metadata = $this->persistClientMetadata($client, $validated);
            $this->applyApprovalWorkflow($client, $validated['redirect_uris'], $request);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json([
                'error' => 'server_error',
                'error_description' => 'The client could not be registered.',
            ], 500);
        }

        // Response shape matches the vendor controller so compliant
        // DCR clients (Claude Desktop, etc.) parse it the same way.
        // The pending-state detail is NOT surfaced in the response so
        // the client can't tell whether it was auto-approved or is
        // waiting on admin review until it tries the authorize flow.
        return response()->json([
            'client_id' => (string) $client->id,
            'grant_types' => $client->getAttribute('grant_types'),
            'response_types' => ['code'],
            'redirect_uris' => $client->getAttribute('redirect_uris'),
            'scope' => Registrar::OAUTH_SCOPE,
            'token_endpoint_auth_method' => 'none',
            ...$metadata,
        ], 201);
    }

    /**
     * Shape-only validation of a redirect URI. Must be parseable, have
     * a scheme and (for non-http(s) schemes) a host, must not carry
     * userinfo. Unlike the vendor controller we don't require the URI
     * to match a pre-trusted allow-list here: the approval workflow
     * gates whether a successfully-registered client can actually be
     * used, not whether it can register.
     */
    protected function isWellFormedRedirectUri(string $value): bool
    {
        $scheme = parse_url($value, PHP_URL_SCHEME);
        if (! is_string($scheme) || $scheme === '') {
            return false;
        }

        if (parse_url($value, PHP_URL_USER) !== null || parse_url($value, PHP_URL_PASS) !== null) {
            return false;
        }

        if (in_array($scheme, ['http', 'https'], true)) {
            return Str::isUrl($value, ['http', 'https']);
        }

        return is_string(parse_url($value, PHP_URL_HOST)) && parse_url($value, PHP_URL_HOST) !== '';
    }

    /**
     * Decide whether the just-registered client is auto-approved or
     * left pending based on its redirect URIs. Also records the
     * registering IP so a superuser reviewing a pending client sees
     * where the request came from.
     *
     * Rule: client is auto-approved if EVERY redirect URI matches an
     * auto-approve category. One off-allow-list URI is enough to
     * force the whole client to pending.
     *
     * @param  array<int, string>  $redirectUris
     */
    protected function applyApprovalWorkflow(mixed $client, array $redirectUris, Request $request): void
    {
        if (! $client instanceof OAuthClient) {
            return;
        }

        $autoApprove = true;
        foreach ($redirectUris as $uri) {
            if (! $this->isAutoApproved((string) $uri)) {
                $autoApprove = false;
                break;
            }
        }

        $client->forceFill([
            'status' => $autoApprove ? OAuthClient::STATUS_APPROVED : OAuthClient::STATUS_PENDING,
            'approved_at' => $autoApprove ? now() : null,
            'registered_ip' => $request->ip(),
        ])->save();

        if (! $autoApprove) {
            $this->notifyPendingSuperusers($client, $request->ip());
        }
    }

    /**
     * Email every active (and non-soft-deleted) superuser that a new
     * OAuth client is awaiting review. Wrapped in a try/catch so a
     * mail-driver misconfiguration cannot break the DCR response the
     * requesting client is waiting on. Mail failures are logged and
     * the registration still succeeds (client is still pending in the
     * DB, admins can discover it via the dashboard tile either way).
     */
    protected function notifyPendingSuperusers(OAuthClient $client, ?string $registeredIp): void
    {
        try {
            // Active superusers with an mail address.
            $recipients = User::query()
                ->where('activated', 1)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->onlySuperAdmins()
                ->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new PendingOauthClientNotification($client, $registeredIp));
            }
        } catch (Throwable $e) {
            Log::warning('Failed to notify superusers about pending OAuth client '.$client->id.': '.$e->getMessage());
        }
    }

    /**
     * Auto-approve categories: loopback redirects, custom URI schemes
     * in the trusted list (hardcoded first-party + admin-configured),
     * HTTPS prefixes in the trusted list (admin-configured).
     */
    protected function isAutoApproved(string $uri): bool
    {
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        if (! is_string($scheme)) {
            return false;
        }

        if ($scheme === 'http' || $scheme === 'https') {
            $host = parse_url($uri, PHP_URL_HOST);
            if ($scheme === 'http' && in_array($host, ['localhost', '127.0.0.1', '[::1]', '::1'], true)) {
                return true;
            }

            $prefixes = array_map(
                fn (string $d): string => Str::endsWith($d, '/') ? $d : $d.'/',
                (array) config('mcp.redirect_domains', [])
            );

            foreach ($prefixes as $prefix) {
                if (Str::startsWith($uri, $prefix)) {
                    return true;
                }
            }

            return false;
        }

        return in_array($scheme, (array) config('mcp.custom_schemes', []), true);
    }
}
