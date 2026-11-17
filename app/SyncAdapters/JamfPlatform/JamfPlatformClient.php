<?php

namespace App\SyncAdapters\JamfPlatform;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Jamf Platform API. Owns auth and pagination
 * for the endpoints the sync adapter uses. Yields raw decoded JSON, no
 * normalization (that's the adapter's job).
 *
 * Product context: Jamf's Platform API (GA September 2025) is a single
 * gateway that fronts multiple Jamf products, including what used to be
 * Jamf Now (blueprints + basic device inventory) alongside cross-product
 * workflows. Customers previously on Jamf Now authenticate here, and
 * customers on Jamf Pro or Jamf School can also use this endpoint for
 * their cross-product needs. Jamf Pro's rich per-product API and Jamf
 * School's Network-ID basic-auth API stay in their own adapters
 * (JamfAdapter and JamfSchoolAdapter) because Platform does not subsume
 * their full surface yet.
 *
 * Auth model: OAuth 2.0 client credentials. The admin creates an
 * integration in Jamf Account, grants it the capabilities it needs,
 * and copies the client id + client secret plus the environment UUID
 * into this adapter's settings. This client exchanges those for a
 * 15-minute bearer token at /auth/token and caches it on the instance
 * so a full-fleet pull does not re-authenticate on every request.
 *
 * Required request headers:
 *   - Authorization: Bearer <access_token>
 *   - X-Environment-Id: <UUID>
 *   - Accept: application/json
 *
 * Jamf Platform API reference: https://developer.jamf.com/platform-api/
 */
class JamfPlatformClient
{
    use RetriesTransientHttpErrors;

    private ?string $accessToken = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $environmentId,
    ) {}

    /**
     * Iterate every device in the environment, one page at a time.
     * Returns a generator so callers stream through large fleets
     * without holding the whole list in memory.
     *
     * Platform API collection endpoints use page + page-size (integer,
     * 1-based) and return a `{results: [...], totalCount: N}` envelope.
     * The loop terminates on either a short page (fewer than page-size
     * records back) or when seen-so-far matches totalCount, whichever
     * hits first.
     *
     * @return iterable<array<string, mixed>>
     */
    public function devices(int $pageSize = 100): iterable
    {
        $page = 1;

        do {
            $response = $this->request()
                ->get('/devices/v1/devices', [
                    'page' => $page,
                    'page-size' => $pageSize,
                ])
                ->throw()
                ->json();

            $devices = $response['results'] ?? [];
            foreach ($devices as $device) {
                yield $device;
            }

            $total = $response['totalCount'] ?? null;
            $seenSoFar = ($page - 1) * $pageSize + count($devices);
            $done = count($devices) < $pageSize
                || ($total !== null && $seenSoFar >= $total);
            $page++;
        } while (! $done);
    }

    /**
     * List every Blueprint in the environment. Blueprints are Jamf's
     * device-grouping concept (configuration profile bundle applied to
     * a set of devices, historically the core primitive of Jamf Now).
     * Used by the adapter's fetchGroups() so admins can map Blueprints
     * to Snipe-IT companies.
     *
     * @return array<int, array<string, mixed>>
     */
    public function blueprints(): array
    {
        $response = $this->request()
            ->get('/blueprints/v1/blueprints')
            ->throw()
            ->json();

        return $response['results'] ?? [];
    }

    /**
     * Base HTTP client with resolved bearer token AND the environment
     * scope header the Platform API requires on every environment-
     * scoped call. The environment id is a UUID copied from Jamf
     * Account after the integration is created.
     */
    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->withOptions(['allow_redirects' => false])
                ->withToken($this->bearer())
                ->withHeaders(['X-Environment-Id' => $this->environmentId])
                ->acceptJson()
                ->timeout(60)
        );
    }

    /**
     * Exchange client credentials for a bearer token. Tokens are
     * short-lived (15 minutes per Jamf docs) and cached on this
     * instance so a full sync run does not retrigger auth per request.
     * Failures throw up to the sync runner and land in the
     * sync-adapters log.
     */
    private function bearer(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $response = Http::asForm()
            ->withOptions(['allow_redirects' => false])
            ->timeout(30)
            ->post(rtrim($this->baseUrl, '/').'/auth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ])
            ->throw()
            ->json();

        return $this->accessToken = (string) ($response['access_token'] ?? '');
    }
}
