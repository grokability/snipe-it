<?php

namespace App\SyncAdapters\Unifi;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the two UniFi APIs the sync adapter can target:
 *
 * - Local Network Integration API, served off a UniFi console or
 *   Network Application at `/proxy/network/integration/v1/`. Admin
 *   points the base URL at the controller (LAN or exposed WAN).
 *   Pagination is offset/limit, device data is site-scoped and
 *   includes serial numbers, MACs, and last-seen timestamps.
 *
 * - Site Manager cloud API at `https://api.ui.com/v1/`. Admin points
 *   the base URL at `api.ui.com`. Pagination is cursor-based via
 *   `nextToken`, device data is host-scoped (not site-scoped), the
 *   envelope wraps devices under a per-host `data[].devices[]` nest,
 *   and the field names are shorter (mac/ip/version) with no serial
 *   number or lastSeen exposed. Site Manager works through consoles
 *   with Remote Access enabled, so it is the supported path for hosted
 *   Snipe-IT installs talking to consoles behind NAT.
 *
 * Both backends authenticate with an `X-API-KEY` header. The key
 * types are different in UniFi's UI (Settings -> Admins & Users ->
 * API Keys on a console vs. Site Manager -> Developer Portal) but the
 * client handles them uniformly.
 *
 * This client yields a canonical device shape with the local-API
 * field names (`serialNumber`, `macAddress`, `ipAddress`,
 * `firmwareVersion`, `lastSeen`, `modelKey`, `state`, `uplink`,
 * `adopted`). Cloud-mode responses are translated into that shape
 * before yielding, with fields the cloud API does not expose set to
 * null. `UnifiAdapter::normalize` therefore does not need to know
 * which backend produced a record.
 *
 * UniFi Network Integration API reference:
 * https://developer.ui.com/unifi-api/
 *
 * UniFi Site Manager API reference:
 * https://developer.ui.com/site-manager-api/
 */
class UnifiClient
{
    use RetriesTransientHttpErrors;

    /**
     * Site Manager cloud host. Hard-coded rather than configurable
     * because the public API is only served at this one domain today
     * (unifi.ui.com is the web-console SPA, not an API).
     */
    private const SITE_MANAGER_HOST = 'api.ui.com';

    private readonly bool $isCloud;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly ?string $siteId = null,
    ) {
        $this->isCloud = $this->baseUrlLooksLikeSiteManager($baseUrl);
    }

    /**
     * List every site the API key can see. On the local backend this
     * returns the sites configured on the one UniFi console the base
     * URL points at. On Site Manager cloud this returns sites across
     * every console the key has access to. The stored identifier is
     * the host ID (which is what the device-list endpoint accepts as
     * its filter), so two sites on the same console collapse to one
     * dropdown row.
     *
     * @return array<int, array<string, mixed>>
     */
    public function sites(): array
    {
        return $this->isCloud ? $this->sitesFromCloud() : $this->sitesFromLocal();
    }

    /**
     * List every managed device at the configured scope. Local mode
     * is site-scoped (siteId maps to a controller site), cloud mode
     * is host-scoped (siteId is treated as the Site Manager hostId
     * and all devices on that console are returned regardless of
     * which internal site they belong to, because the public cloud
     * device endpoint does not expose a site filter today).
     *
     * @return iterable<array<string, mixed>>
     */
    public function devices(int $limit = 100): iterable
    {
        return $this->isCloud ? $this->devicesFromCloud() : $this->devicesFromLocal($limit);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sitesFromLocal(): array
    {
        $response = $this->request()
            ->get('/proxy/network/integration/v1/sites')
            ->throw()
            ->json();

        return $response['data'] ?? [];
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    private function devicesFromLocal(int $limit): iterable
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

            $total = $response['totalCount'] ?? null;
            $seenSoFar = $offset + count($devices);
            $done = count($devices) < $limit
                || ($total !== null && $seenSoFar >= $total);
            $offset += $limit;
        } while (! $done);
    }

    /**
     * Walk /v1/sites via nextToken cursor, flattening each row into
     * the {id, name} shape the adapter already expects. Uses the
     * hostId as the stored config value. Host-scoped filtering
     * aggregates sibling sites on the same console. Adding a per-site
     * filter would require the console to expose siteIds[] on
     * /v1/devices, which it does not today.
     *
     * @return array<int, array<string, mixed>>
     */
    private function sitesFromCloud(): array
    {
        $sites = [];
        $nextToken = null;

        do {
            $query = ['pageSize' => '100'];
            if ($nextToken !== null) {
                $query['nextToken'] = $nextToken;
            }

            $response = $this->request()
                ->get('/v1/sites', $query)
                ->throw()
                ->json();

            foreach ($response['data'] ?? [] as $row) {
                $hostId = $row['hostId'] ?? null;
                if ($hostId === null || $hostId === '') {
                    continue;
                }

                $sites[] = [
                    'id' => (string) $hostId,
                    'name' => $row['meta']['name'] ?? (string) $hostId,
                    'displayName' => $row['meta']['name'] ?? null,
                ];
            }

            $nextToken = $response['nextToken'] ?? null;
        } while (! empty($nextToken));

        return $sites;
    }

    /**
     * Walk /v1/devices?hostIds[]={hostId} via nextToken cursor,
     * flatten each host's nested devices[], and translate cloud field
     * names to the canonical local-API shape so UnifiAdapter::normalize
     * does not need two code paths.
     *
     * @return iterable<array<string, mixed>>
     */
    private function devicesFromCloud(): iterable
    {
        $nextToken = null;

        do {
            $query = [
                'hostIds[]' => $this->siteId,
                'pageSize' => '100',
            ];
            if ($nextToken !== null) {
                $query['nextToken'] = $nextToken;
            }

            $response = $this->request()
                ->get('/v1/devices', $query)
                ->throw()
                ->json();

            foreach ($response['data'] ?? [] as $hostRow) {
                foreach ($hostRow['devices'] ?? [] as $cloudDevice) {
                    yield $this->translateCloudDevice($cloudDevice);
                }
            }

            $nextToken = $response['nextToken'] ?? null;
        } while (! empty($nextToken));
    }

    /**
     * Map Site Manager's device shape to the canonical local-API shape
     * the adapter normalizes against. Fields Site Manager does not
     * expose (serial number, last-seen, uplink MAC) stay null.
     *
     * @param  array<string, mixed>  $cloudDevice
     * @return array<string, mixed>
     */
    private function translateCloudDevice(array $cloudDevice): array
    {
        return [
            'id' => $cloudDevice['id'] ?? null,
            'name' => $cloudDevice['name'] ?? null,
            'model' => $cloudDevice['model'] ?? null,
            'modelKey' => $cloudDevice['shortname'] ?? null,
            'serialNumber' => null,
            'macAddress' => $cloudDevice['mac'] ?? null,
            'ipAddress' => $cloudDevice['ip'] ?? null,
            'firmwareVersion' => $cloudDevice['version'] ?? null,
            'lastSeen' => null,
            'state' => $cloudDevice['status'] ?? null,
            'uplink' => ['mac' => null],
            'adopted' => $cloudDevice['isManaged'] ?? null,
        ];
    }

    private function baseUrlLooksLikeSiteManager(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host)) {
            return false;
        }

        return strtolower($host) === self::SITE_MANAGER_HOST;
    }

    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->withOptions(['allow_redirects' => false])
                ->withHeaders(['X-API-KEY' => $this->apiKey])
                ->acceptJson()
                ->timeout(30)
        );
    }
}
