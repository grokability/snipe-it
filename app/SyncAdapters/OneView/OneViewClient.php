<?php

namespace App\SyncAdapters\OneView;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the HPE OneView REST API. Owns session-token
 * auth, API-version negotiation, and the paged /rest/server-hardware
 * generator. Yields raw decoded JSON members. No normalization (that
 * lives on the adapter).
 *
 * Auth shape is three-step and distinct from every other adapter we
 * ship:
 *   1. POST /rest/login-sessions { userName, password } -> returns a
 *      session id string in the response body's `sessionID` field. All
 *      subsequent requests carry it as the `Auth:` header (not
 *      `Authorization:` - OneView uses its own header name).
 *   2. GET /rest/version returns { minimumVersion, currentVersion }.
 *      We pin requests to minimumVersion so appliances of different
 *      ages all route through a known-stable schema. OneView rejects
 *      calls without an X-API-Version header with 400.
 *   3. GET /rest/server-hardware?count=N&start=M walks the inventory.
 *      Response contains { members, nextPageUri, total }. We follow
 *      nextPageUri until it's null.
 *
 * Session token is cached on this instance. OneView session tokens
 * expire after 24h of inactivity per the vendor docs. Adapter-level
 * calls that cross that boundary are rare since pulls are short and
 * the sync scheduler creates a fresh client each run.
 *
 * HPE OneView API reference: https://techhub.hpe.com/eginfolib/servers/docs/oneview/api/
 */
class OneViewClient
{
    use RetriesTransientHttpErrors;

    /**
     * OneView rejects any REST call without an X-API-Version header
     * with 400. The actual value gets picked from the appliance's
     * /rest/version response on first use so one adapter supports
     * every appliance generation without a per-tenant setting.
     */
    private ?string $apiVersion = null;

    private ?string $sessionToken = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $username,
        private readonly string $password,
    ) {}

    /**
     * Iterate every server-hardware row on the appliance, one page at
     * a time. Yields raw decoded members. Walks nextPageUri until the
     * appliance stops returning one.
     *
     * @return iterable<array<string, mixed>>
     */
    public function serverHardware(int $pageSize = 100): iterable
    {
        $uri = '/rest/server-hardware?count='.$pageSize.'&start=0';

        while ($uri !== null) {
            $response = $this->request()
                ->get($uri)
                ->throw()
                ->json();

            $members = $response['members'] ?? [];
            if (! is_array($members)) {
                $members = [];
            }

            foreach ($members as $member) {
                yield $member;
            }

            // OneView returns nextPageUri as an absolute-path string on
            // intermediate pages and null on the terminal one. Honor it
            // verbatim rather than recomputing start+count ourselves so
            // appliance-side filter/count tweaks (e.g. enforced max page
            // sizes on some generations) don't desync pagination.
            $next = $response['nextPageUri'] ?? null;
            $uri = is_string($next) && $next !== '' ? $next : null;
        }
    }

    /**
     * Build a PendingRequest with auth + API-version headers attached.
     * Lazily exchanges credentials for a session token on first use
     * and negotiates the API version off /rest/version so admins don't
     * have to pin it per appliance.
     */
    private function request(): PendingRequest
    {
        $request = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withOptions(['allow_redirects' => false])
            ->acceptJson()
            ->timeout(30)
            ->withHeaders([
                'Auth' => $this->sessionToken(),
                'X-API-Version' => $this->apiVersion(),
            ]);

        return $this->withTransientRetry($request);
    }

    /**
     * POST /rest/login-sessions returns { sessionID: "..." }. The
     * sessionID string is what OneView expects on the Auth header for
     * every subsequent call. Cached on this instance.
     */
    private function sessionToken(): string
    {
        if ($this->sessionToken !== null) {
            return $this->sessionToken;
        }

        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withOptions(['allow_redirects' => false])
            ->acceptJson()
            ->timeout(30)
            ->post('/rest/login-sessions', [
                'userName' => $this->username,
                'password' => $this->password,
            ])
            ->throw()
            ->json();

        $this->sessionToken = (string) ($response['sessionID'] ?? '');

        return $this->sessionToken;
    }

    /**
     * GET /rest/version returns { minimumVersion, currentVersion }.
     * Pin requests to minimumVersion so an adapter that works on one
     * appliance generation works on all of them. currentVersion drifts
     * per appliance release and would silently gate us out of fleets
     * that haven't upgraded yet. Numeric string per OneView convention.
     */
    private function apiVersion(): string
    {
        if ($this->apiVersion !== null) {
            return $this->apiVersion;
        }

        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withOptions(['allow_redirects' => false])
            ->acceptJson()
            ->timeout(30)
            ->get('/rest/version')
            ->throw()
            ->json();

        $this->apiVersion = (string) ($response['minimumVersion'] ?? '');

        return $this->apiVersion;
    }
}
