<?php

namespace App\SyncAdapters\Kandji;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Kandji Enterprise API. Owns auth + pagination
 * for the endpoints the sync adapter uses. Yields raw decoded JSON, no
 * normalization (that's the adapter's job).
 *
 * Kandji API reference: https://api-docs.kandji.io/
 */
class KandjiClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
    ) {}

    /**
     * Iterate every device in the Kandji tenant, one page at a time.
     * Returns a generator so callers stream through large fleets without
     * holding the whole list in memory. Kandji caps limit at 300 and
     * uses offset-based pagination.
     *
     * @return iterable<array<string, mixed>>
     */
    public function devices(int $limit = 300): iterable
    {
        $offset = 0;

        do {
            $response = $this->request()
                ->get('/api/v1/devices', [
                    'limit' => $limit,
                    'offset' => $offset,
                ])
                ->throw()
                ->json();

            // Kandji returns a bare array of device objects on this
            // endpoint (no wrapper). Bail when the vendor returns an
            // unexpected shape rather than iterating over garbage.
            $devices = is_array($response) ? $response : [];

            foreach ($devices as $device) {
                yield $device;
            }

            // Terminate when a short page comes back. No totalCount in
            // Kandji's response for this endpoint so a short page is
            // the accepted end-of-stream signal.
            $done = count($devices) < $limit;
            $offset += $limit;
        } while (! $done);
    }

    /**
     * List every Blueprint in the Kandji tenant. Used by the
     * adapter's fetchGroups() so admins can map Blueprints to
     * Snipe-IT companies. Endpoint returns a paginated list. Walk
     * all pages up to the vendor cap.
     *
     * @return array<int, array<string, mixed>>
     */
    public function blueprints(int $limit = 300): array
    {
        $blueprints = [];
        $offset = 0;

        do {
            $response = $this->request()
                ->get('/api/v1/blueprints', ['limit' => $limit, 'offset' => $offset])
                ->throw()
                ->json();

            $batch = $response['results'] ?? (is_array($response) ? $response : []);
            $blueprints = array_merge($blueprints, $batch);

            $done = count($batch) < $limit;
            $offset += $limit;
        } while (! $done);

        return $blueprints;
    }

    /**
     * Update a device's writable metadata (asset_tag, blueprint,
     * user assignment, etc.). Sends a PATCH to the per-device
     * endpoint with a form-encoded body since Kandji's device API
     * uses form-urlencoded for writes rather than JSON. Fields the
     * caller doesn't include stay untouched.
     *
     * @param  array<string, scalar|null>  $fields
     */
    public function updateDevice(string $deviceId, array $fields): void
    {
        Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->token)
            ->acceptJson()
            ->asForm()
            ->timeout(30)
            ->patch('/api/v1/devices/'.$deviceId, $fields)
            ->throw();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->token)
            ->acceptJson()
            ->timeout(30);
    }
}
