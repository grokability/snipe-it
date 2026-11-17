<?php

namespace App\SyncAdapters\UnifiAccess;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the UniFi Access developer API. Owns auth for
 * the endpoints the sync adapter uses. Yields raw decoded JSON, no
 * normalization (that's the adapter's job).
 *
 * Auth model: an API token generated in UniFi Portal under
 * Access -> Settings -> General -> Advanced -> API Token. Sent as a
 * Bearer token. UniFi Access 1.9.1 or later is required, and the API
 * is unavailable on consoles that upgraded to Identity Enterprise.
 *
 * This is a different credential from the Network API key: the two
 * apps provision tokens independently, so admins running both
 * configure one UniFi (Network) instance and one UniFi Access instance,
 * each with its own token.
 *
 * The Access API runs on port 12445 with a self-signed cert and does
 * not share Network's site model, so there is no site segment in the
 * path. One console serves one Access deployment.
 *
 * UniFi Access developer API reference (PDF):
 * https://assets.identity.ui.com/unifi-access/api_reference.pdf
 */
class UnifiAccessClient
{
    use RetriesTransientHttpErrors;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiToken,
    ) {}

    /**
     * List every Access device the console knows about (door hubs,
     * readers, elevator hubs, Access-managed cameras). Yields raw
     * decoded entries so callers stream through large deployments
     * without buffering the whole list.
     *
     * @return iterable<array<string, mixed>>
     */
    public function devices(): iterable
    {
        $response = $this->request()
            ->get('/api/v1/developer/devices')
            ->throw()
            ->json();

        // The Access API wraps its payload in {code, msg, data}. Fall
        // back to the top-level array shape so a future API revision
        // that drops the envelope still works.
        $devices = $response['data'] ?? $response;
        if (! is_array($devices)) {
            return;
        }

        foreach ($devices as $device) {
            if (is_array($device)) {
                yield $device;
            }
        }
    }

    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->withOptions(['allow_redirects' => false])
                ->withToken($this->apiToken)
                ->acceptJson()
                ->timeout(30)
        );
    }
}
