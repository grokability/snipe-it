<?php

namespace App\SyncAdapters\Jamf;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Jamf Pro API. Owns auth + pagination for the
 * endpoints the sync adapter uses. Yields raw decoded JSON, no
 * normalization (that's the adapter's job).
 *
 * Auth model: OAuth 2.0 client credentials. The admin creates an API
 * Role granting Read on Computers (plus Read on Sites when
 * group-to-company mapping is in play) and an API Client that uses
 * that role, under Settings -> System -> API Roles and Clients in
 * Jamf Pro. This client exchanges the Client ID + Client Secret for a
 * bearer token via POST /api/oauth/token and caches it on the instance
 * (with a small buffer before expiry so a slow sync run does not
 * present a token that's about to be rejected). Jamf Pro's default
 * token lifetime is 60 seconds, so any non-trivial sync will refresh
 * at least once. Longer lifetimes are configurable per API Client in
 * Jamf. Failures throw up to the sync runner and land in the
 * sync-adapters log.
 *
 * Jamf Pro API reference: https://developer.jamf.com/jamf-pro/reference
 */
class JamfClient
{
    use RetriesTransientHttpErrors;

    private ?string $accessToken = null;

    private ?int $expiresAt = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    /**
     * Iterate every managed computer in the Jamf tenant, one page at a
     * time. Returns a generator so callers stream through large fleets
     * without holding the whole list in memory.
     *
     * @return iterable<array<string, mixed>>
     */
    public function computers(int $pageSize = 100): iterable
    {
        $page = 0;

        // Build the query string by hand so the wire form matches
        // what Jamf actually reads.
        $sections = ['GENERAL', 'HARDWARE', 'OPERATING_SYSTEM', 'USER_AND_LOCATION', 'STORAGE'];

        do {
            $query = http_build_query([
                'page' => $page,
                'page-size' => $pageSize,
            ]);
            foreach ($sections as $section) {
                $query .= '&section='.urlencode($section);
            }

            $response = $this->request()
                ->get('/api/v1/computers-inventory?'.$query)
                ->throw()
                ->json();

            $results = $response['results'] ?? [];
            foreach ($results as $computer) {
                yield $computer;
            }

            // Jamf returns totalCount on this endpoint. Stop when we've
            // seen enough results or when a short page comes back.
            $total = $response['totalCount'] ?? null;
            $seenSoFar = ($page + 1) * $pageSize;
            $done = count($results) < $pageSize
                || ($total !== null && $seenSoFar >= $total);
            $page++;
        } while (! $done);
    }

    /**
     * Iterate every mobile device (iOS, iPadOS, tvOS) in the Jamf
     * tenant, one page at a time. Hits /api/v2/mobile-devices which,
     * unlike /api/v1/computers-inventory, already returns most of the
     * fields we need at the top level without a per-device detail
     * fetch. Separate pager from computers() because the pagination
     * terminator shape matches but the URL + fields differ enough that
     * sharing the loop would obscure both.
     *
     * @return iterable<array<string, mixed>>
     */
    public function mobileDevices(int $pageSize = 100): iterable
    {
        $page = 0;

        do {
            $query = http_build_query([
                'page' => $page,
                'page-size' => $pageSize,
            ]);

            $response = $this->request()
                ->get('/api/v2/mobile-devices?'.$query)
                ->throw()
                ->json();

            $results = $response['results'] ?? [];
            foreach ($results as $device) {
                yield $device;
            }

            $total = $response['totalCount'] ?? null;
            $seenSoFar = ($page + 1) * $pageSize;
            $done = count($results) < $pageSize
                || ($total !== null && $seenSoFar >= $total);
            $page++;
        } while (! $done);
    }

    /**
     * List every Site in the Jamf Pro tenant. Used by the adapter's
     * fetchGroups() so admins can map Sites to Snipe-IT companies.
     * Response shape is a bare array of {id, name} objects.
     *
     * @return array<int, array<string, mixed>>
     */
    public function sites(): array
    {
        $response = $this->request()
            ->get('/api/v1/sites')
            ->throw()
            ->json();

        return is_array($response) ? $response : [];
    }

    /**
     * List every Computer Group (smart + static) in the Jamf Pro tenant.
     * Used by the adapter's optional group filter so admins who don't
     * use Sites can still scope the sync to specific Smart Groups (or
     * static groups), e.g. "Law School Macs".
     *
     * Hits the classic API because /JSSResource/computergroups has been
     * the stable membership-returning endpoint across every supported
     * Jamf Pro release, where the v1 pro-API equivalent has moved
     * several times and omits smart-group member lists on older tenants.
     * Bearer-token auth works against classic API endpoints since Jamf
     * Pro 10.35, which is well below our effective minimum.
     *
     * Returns a list of {id, name, is_smart} entries.
     *
     * @return array<int, array<string, mixed>>
     */
    public function computerGroups(): array
    {
        $response = $this->request()
            ->get('/JSSResource/computergroups')
            ->throw()
            ->json();

        return (array) ($response['computer_groups'] ?? []);
    }

    /**
     * Return the computer IDs that currently belong to a given Computer
     * Group. The classic group-detail endpoint returns the full group
     * payload including a `computers` array of `{id, name, ...}` entries
     * for both smart and static groups. We just need the IDs.
     *
     * @return array<int, int>
     */
    public function computerGroupMembers(int $groupId): array
    {
        $response = $this->request()
            ->get('/JSSResource/computergroups/id/'.$groupId)
            ->throw()
            ->json();

        $computers = (array) ($response['computer_group']['computers'] ?? []);

        return array_values(array_filter(array_map(
            fn (array $computer) => isset($computer['id']) ? (int) $computer['id'] : null,
            $computers,
        )));
    }

    /**
     * Mobile device groups counterpart to computerGroups(). Only queried
     * when the admin has enabled mobile-device sync AND configured a
     * group filter, so Jamf tenants without mobile device groups don't
     * pay for the call.
     *
     * @return array<int, array<string, mixed>>
     */
    public function mobileDeviceGroups(): array
    {
        $response = $this->request()
            ->get('/JSSResource/mobiledevicegroups')
            ->throw()
            ->json();

        return (array) ($response['mobile_device_groups'] ?? []);
    }

    /**
     * Mobile device ID list for a given Mobile Device Group. Same shape
     * as computerGroupMembers() but the classic payload nests the member
     * list under `mobile_devices` instead of `computers`.
     *
     * @return array<int, int>
     */
    public function mobileDeviceGroupMembers(int $groupId): array
    {
        $response = $this->request()
            ->get('/JSSResource/mobiledevicegroups/id/'.$groupId)
            ->throw()
            ->json();

        $devices = (array) ($response['mobile_device_group']['mobile_devices'] ?? []);

        return array_values(array_filter(array_map(
            fn (array $device) => isset($device['id']) ? (int) $device['id'] : null,
            $devices,
        )));
    }

    /**
     * Update a computer's inventory detail via Jamf Pro's newer JSON
     * API. Accepts a partial payload with nested objects like `general`
     * (assetTag lives at general.assetTag per ComputerGeneralUpdate).
     * Fields the caller doesn't include stay untouched.
     *
     * @param  array<string, mixed>  $payload
     */
    public function updateComputerDetail(string $computerId, array $payload): void
    {
        $this->request()
            ->asJson()
            ->patch('/api/v1/computers-inventory-detail/'.$computerId, $payload)
            ->throw();
    }

    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->withOptions(['allow_redirects' => false])
                ->withToken($this->bearer())
                ->acceptJson()
                ->timeout(30)
        );
    }

    /**
     * Exchange client credentials for a bearer token via Jamf Pro's
     * /api/oauth/token endpoint. Tokens are short-lived (60 seconds
     * on default API Clients, up to 68 years if the admin configured
     * a longer lifetime) so we cache the token on this instance and
     * re-exchange when we're within a 30-second buffer of expiry.
     * Response shape: {access_token, token_type: Bearer, expires_in}.
     */
    private function bearer(): string
    {
        $now = time();
        if ($this->accessToken !== null && $this->expiresAt !== null && $now < $this->expiresAt - 30) {
            return $this->accessToken;
        }

        $response = Http::asForm()
            ->withOptions(['allow_redirects' => false])
            ->timeout(30)
            ->post(rtrim($this->baseUrl, '/').'/api/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ])
            ->throw()
            ->json();

        $this->accessToken = (string) ($response['access_token'] ?? '');
        $expiresIn = (int) ($response['expires_in'] ?? 0);
        $this->expiresAt = $expiresIn > 0 ? $now + $expiresIn : null;

        return $this->accessToken;
    }
}
