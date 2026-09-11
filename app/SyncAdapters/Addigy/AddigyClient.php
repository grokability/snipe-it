<?php

namespace App\SyncAdapters\Addigy;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Addigy API. Owns auth for the devices
 * endpoint the sync adapter uses. Yields raw decoded JSON, no
 * normalization (that's the adapter's job).
 *
 * Auth model: a header pair generated in the Addigy admin console
 * under Integrations -> Addigy API. Both are treated as secrets by
 * the framework and pass in the `client-id` and `client-secret`
 * headers on every request (Addigy's own naming, lowercase). Distinct
 * from the bearer-token pattern the other adapters use.
 *
 * The v1 devices endpoint returns the full device list in one call,
 * so this client is single-request. Large tenants (thousands of
 * devices) can extend later to page through /api/v2/devices with the
 * cursor-based response shape.
 *
 * Addigy API reference: https://addigy.freshdesk.com/support/solutions/folders/8000078965
 */
class AddigyClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    /**
     * List every enrolled device in the Addigy tenant. Returned as a
     * generator for consistency with the other adapters, even though
     * the underlying endpoint is single-response.
     *
     * @return iterable<array<string, mixed>>
     */
    public function devices(): iterable
    {
        $response = $this->request()
            ->get('/api/devices')
            ->throw()
            ->json();

        // v1 returns a bare array of device objects on this endpoint.
        $devices = is_array($response) ? $response : [];

        foreach ($devices as $device) {
            yield $device;
        }
    }

    /**
     * List every Policy in the Addigy tenant. Used by the adapter's
     * fetchGroups() so admins can map Policies to Snipe-IT companies.
     * Policies are Addigy's device-grouping concept. MSPs often use
     * them as customer proxies.
     *
     * @return array<int, array<string, mixed>>
     */
    public function policies(): array
    {
        $response = $this->request()
            ->get('/api/policies')
            ->throw()
            ->json();

        return is_array($response) ? $response : [];
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withHeaders([
                'client-id' => $this->clientId,
                'client-secret' => $this->clientSecret,
            ])
            ->acceptJson()
            ->timeout(30);
    }
}
