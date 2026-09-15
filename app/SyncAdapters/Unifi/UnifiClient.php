<?php

namespace App\SyncAdapters\Unifi;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the UniFi Network Integration API. Owns auth +
 * pagination for the endpoints the sync adapter uses. Yields raw
 * decoded JSON, no normalization (that's the adapter's job).
 *
 * Auth model: API key generated in the UniFi admin console under
 * Settings -> Admins & Users -> API Keys, passed via `X-API-KEY`
 * header. Available on UniFi OS 4.0.6+, UniFi Network Application
 * 9.0.108+, and Site Manager cloud. Older controllers only support
 * session-cookie auth which this client does not implement.
 *
 * Endpoint path is under /proxy/network/integration/v1/ on local
 * controllers, or at api.ui.com/v1/ on Site Manager cloud. The
 * adapter's Base URL setting controls which. the paths this client
 * hits are relative and identical on both.
 *
 * UniFi Network Integration API reference:
 * https://developer.ui.com/unifi-api/
 */
class UnifiClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly string $siteId,
    ) {}

    /**
     * List every managed device in the configured UniFi site (APs,
     * switches, gateways, cameras, etc). Returns a generator so
     * callers stream through large deployments without buffering the
     * whole list in memory.
     *
     * @return iterable<array<string, mixed>>
     */
    public function devices(int $limit = 100): iterable
    {
        $offset = 0;

        do {
            $response = $this->request()
                ->get('/proxy/network/integration/v1/sites/'.$this->siteId.'/devices', [
                    'offset' => $offset,
                    'limit' => $limit,
                ])
                ->throw()
                ->json();

            $devices = $response['data'] ?? [];
            foreach ($devices as $device) {
                yield $device;
            }

            // Stop when seen-so-far reaches totalCount or a short
            // page comes back. Belt-and-suspenders: either signal
            // alone would work.
            $total = $response['totalCount'] ?? null;
            $seenSoFar = $offset + count($devices);
            $done = count($devices) < $limit
                || ($total !== null && $seenSoFar >= $total);
            $offset += $limit;
        } while (! $done);
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withHeaders(['X-API-KEY' => $this->apiKey])
            ->acceptJson()
            ->timeout(30);
    }
}
