<?php

namespace App\SyncAdapters\JamfSchool;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Jamf School (formerly ZuluDesk) API. Owns
 * auth + pagination for the endpoints the sync adapter uses. Yields
 * raw decoded JSON, no normalization (that's the adapter's job).
 *
 * Auth model: HTTP Basic auth with the tenant's Network ID as the
 * username and API key as the password. Both come from the Jamf School
 * admin console under Organization -> Settings -> API. This is a
 * distinct product and API from Jamf Pro (which uses bearer tokens).
 *
 * Jamf School API reference: https://school.jamfcloud.com/api/docs/
 */
class JamfSchoolClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $networkId,
        private readonly string $apiKey,
    ) {}

    /**
     * Iterate every enrolled device in the Jamf School tenant, one
     * page at a time. Returns a generator so callers stream through
     * large fleets without holding the whole list in memory.
     *
     * @return iterable<array<string, mixed>>
     */
    public function devices(int $pageSize = 100): iterable
    {
        $page = 1;

        do {
            $response = $this->request()
                ->get('/devices', [
                    'page' => $page,
                    'pageSize' => $pageSize,
                ])
                ->throw()
                ->json();

            $devices = $response['devices'] ?? [];
            foreach ($devices as $device) {
                yield $device;
            }

            // Jamf School returns a total count on this endpoint. Stop
            // when seen-so-far reaches it, or on a short page as a
            // fallback if the count field is absent.
            $total = $response['count'] ?? null;
            $seenSoFar = ($page - 1) * $pageSize + count($devices);
            $done = count($devices) < $pageSize
                || ($total !== null && $seenSoFar >= $total);
            $page++;
        } while (! $done);
    }

    /**
     * List every Location in the Jamf School tenant. Locations are
     * typically schools within a district. Used by the adapter's
     * fetchGroups() so admins can map Locations to Snipe-IT
     * companies.
     *
     * @return array<int, array<string, mixed>>
     */
    public function locations(): array
    {
        $response = $this->request()
            ->get('/locations')
            ->throw()
            ->json();

        return $response['locations'] ?? (is_array($response) ? $response : []);
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withBasicAuth($this->networkId, $this->apiKey)
            ->acceptJson()
            ->timeout(30);
    }
}
