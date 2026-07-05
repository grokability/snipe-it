<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceApiUserAgent
{
    /**
     * The route-level parameter that lets a route opt out of blank-User-Agent
     * blocking. Routes that legitimately receive blank UAs (e.g. Entra ID SCIM
     * provisioning) declare this via `EnforceApiUserAgent::class.':allow_blank_user_agent'`.
     */
    private const ALLOW_BLANK_PARAMETER = 'allow_blank_user_agent';

    public function handle(Request $request, Closure $next, ?string $parameter = null): Response
    {
        $setting = Setting::getSettings();

        // Bail out entirely when User-Agent blocking is disabled, regardless of
        // whether a route opts out of blank blocking — the whole feature is off.
        if (! $setting?->block_api_user_agents) {
            return $next($request);
        }

        $rawUserAgent = $request->header('User-Agent');
        $userAgent = trim((string) $rawUserAgent);

        if ($userAgent === '') {
            // Hard override: routes that legitimately receive blank UAs (SCIM,
            // hit by Entra ID provisioning) pass the allow_blank_user_agent
            // parameter and always succeed here, regardless of admin settings.
            if ($parameter === self::ALLOW_BLANK_PARAMETER) {
                return $next($request);
            }

            // Otherwise the admin's block_blank_api_user_agents toggle decides.
            // Pattern blocking on + blank blocking off lets blank-UA integrations
            // through on the regular API surface; flipping blank blocking on
            // tightens it.
            if ($setting->block_blank_api_user_agents) {
                return $this->reject($rawUserAgent);
            }

            return $next($request);
        }

        // Prefix match (=== 0) rather than substring: scripted clients identify
        // themselves at the start of the User-Agent (curl/x, PostmanRuntime/x,
        // python-requests/x, etc.), and matching only the prefix prevents a pattern
        // from accidentally hitting an unrelated UA that mentions it later in the
        // string.
        foreach ($this->blockedPatterns($setting->blocked_api_user_agents) as $pattern) {
            if (stripos($userAgent, $pattern) === 0) {
                return $this->reject($rawUserAgent);
            }
        }

        return $next($request);
    }

    /**
     * Split the textarea contents into a clean list of patterns. Lines are
     * trimmed and blanks are dropped so the textarea ergonomics (trailing
     * newlines, accidental whitespace) don't accidentally match every UA.
     *
     * @return array<int, string>
     */
    private function blockedPatterns(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', $raw) ?: [],
        ), fn (string $line) => $line !== ''));
    }

    /**
     * Echo the original, untrimmed User-Agent header back to the caller so they
     * can see exactly what the server saw. Null means the header was absent.
     */
    private function reject(?string $userAgent): JsonResponse
    {
        return new JsonResponse([
            'status' => 'error',
            'messages' => trans('admin/settings/general.blocked_api_user_agent_rejected'),
            'payload' => [
                'user_agent' => $userAgent,
            ],
        ], Response::HTTP_FORBIDDEN);
    }
}
