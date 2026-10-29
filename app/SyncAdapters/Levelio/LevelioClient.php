<?php

namespace App\SyncAdapters\Levelio;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Level.io v2 REST API. Owns auth and
 * cursor pagination for the endpoints the sync adapter uses. Yields
 * raw decoded JSON, no normalization (that's the adapter's job).
 *
 * Level.io API reference: https://developers.level.io/
 */
class LevelioClient
{
    use RetriesTransientHttpErrors;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {}

    /**
     * Iterate every device in the Level.io tenant, one page at a time.
     * Returns a generator so callers stream through large fleets without
     * holding the whole list in memory. Level.io caps `limit` at 100
     * and uses cursor pagination keyed on `starting_after`.
     *
     * Every include_* flag maps to data the adapter surfaces either
     * as a standard HostInventoryRecord field or as an admin-mappable
     * extra. Toggles kept to the exact set the normalizer reads so a
     * big-fleet pull does not spend bandwidth on sections nothing
     * routes to.
     *
     * @return iterable<array<string, mixed>>
     */
    public function devices(int $limit = 100): iterable
    {
        $cursor = null;

        do {
            $query = [
                'limit' => $limit,
                'include_operating_system' => 'true',
                'include_network_interfaces' => 'true',
                'include_motherboard' => 'true',
                'include_cpus' => 'true',
                'include_memory' => 'true',
                'include_disks' => 'true',
                'include_security' => 'true',
            ];
            if ($cursor !== null) {
                $query['starting_after'] = $cursor;
            }

            $response = $this->request()
                ->get('/v2/devices', $query)
                ->throw()
                ->throwIfNotJson()
                ->json();

            $devices = is_array($response['data'] ?? null) ? $response['data'] : [];
            foreach ($devices as $device) {
                yield $device;
            }

            $hasMore = (bool) ($response['has_more'] ?? false);
            $cursor = $hasMore && ! empty($devices)
                ? ($devices[count($devices) - 1]['id'] ?? null)
                : null;
        } while ($cursor !== null);
    }

    /**
     * List every group in the Level.io tenant. Used by the adapter's
     * fetchGroups() so admins can map Level groups to Snipe-IT
     * companies. Level.io's groups endpoint uses the same cursor
     * pagination style as devices.
     *
     * @return array<int, array<string, mixed>>
     */
    public function groups(int $limit = 100): array
    {
        $groups = [];
        $cursor = null;

        do {
            $query = ['limit' => $limit];
            if ($cursor !== null) {
                $query['starting_after'] = $cursor;
            }

            $response = $this->request()
                ->get('/v2/groups', $query)
                ->throw()
                ->throwIfNotJson()
                ->json();

            $batch = is_array($response['data'] ?? null) ? $response['data'] : [];
            foreach ($batch as $group) {
                $groups[] = $group;
            }

            $hasMore = (bool) ($response['has_more'] ?? false);
            $cursor = $hasMore && ! empty($batch)
                ? ($batch[count($batch) - 1]['id'] ?? null)
                : null;
        } while ($cursor !== null);

        return $groups;
    }

    /**
     * Update a device's writable fields on Level.io. The vendor
     * exposes `name`, `notes`, and `group_id` on this endpoint, which
     * is a narrow push surface compared to full-featured RMMs. Fields
     * the caller does not include stay untouched.
     *
     * @param  array<string, scalar|null>  $fields
     */
    public function updateDevice(string $deviceId, array $fields): void
    {
        Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withOptions(['allow_redirects' => false])
            ->withHeaders(['Authorization' => $this->apiKey])
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->patch('/v2/devices/'.rawurlencode($deviceId), $fields)
            ->throw();
    }

    /**
     * Level.io auth is a raw `Authorization: <api_key>` header. No
     * Bearer prefix, no OAuth exchange, so withToken() is not usable
     * here (it would prepend "Bearer " and 401 on every call).
     */
    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->withOptions(['allow_redirects' => false])
                ->withHeaders(['Authorization' => $this->apiKey])
                ->acceptJson()
                ->timeout(30)
        );
    }
}
