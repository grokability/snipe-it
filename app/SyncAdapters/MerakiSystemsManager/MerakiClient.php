<?php

namespace App\SyncAdapters\MerakiSystemsManager;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Cisco Meraki Dashboard API for the
 * Systems Manager (SM) module. Owns auth + pagination for the
 * SM devices endpoint.
 *
 * Auth model: Meraki API key from the admin user's profile, passed
 * via the vendor-specific `X-Cisco-Meraki-API-Key` header.
 *
 * Pagination: Meraki returns a `Link` HTTP response header containing
 * a rel=next URL when more pages exist. Follow it until the header
 * is absent. Batch size configurable via `perPage` up to Meraki's
 * cap of 1000.
 */
class MerakiClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly string $organizationId,
    ) {}

    /**
     * Iterate every Systems Manager device across every SM-enabled
     * network in the configured organization.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function devices(int $perPage = 500): iterable
    {
        $networks = $this->smNetworks();

        foreach ($networks as $network) {
            $networkId = $network['id'] ?? null;
            if ($networkId === null) {
                continue;
            }

            $url = '/networks/'.$networkId.'/sm/devices?perPage='.$perPage;

            do {
                $response = $this->request()->get($url)->throw();
                $devices = $response->json();

                foreach ((is_array($devices) ? $devices : []) as $device) {
                    yield $device + ['_networkId' => $networkId];
                }

                $url = $this->extractNextLink($response);
            } while ($url !== null);
        }
    }

    /**
     * SM-enabled networks in the configured organization. Filters the
     * full networks list down to those with the systemsManager product
     * type so we don't waste API calls on wireless-only or switch-only
     * networks.
     *
     * @return array<int, array<string, mixed>>
     */
    public function smNetworks(): array
    {
        $networks = $this->request()
            ->get('/organizations/'.$this->organizationId.'/networks')
            ->throw()
            ->json();

        return array_values(array_filter(
            is_array($networks) ? $networks : [],
            fn (array $n) => in_array('systemsManager', $n['productTypes'] ?? [], true),
        ));
    }

    /**
     * Follow Meraki's `Link` HTTP header to find the next cursor.
     * Returns null when the current page is the last.
     */
    private function extractNextLink(Response $response): ?string
    {
        $link = $response->header('Link');
        if ($link === '' || $link === null) {
            return null;
        }

        if (preg_match('/<([^>]+)>;\s*rel="?next"?/', $link, $m)) {
            // Meraki returns absolute URLs in the Link header. Strip
            // the base host so the PendingRequest's baseUrl still
            // applies uniformly.
            $parsed = parse_url($m[1]);

            return ($parsed['path'] ?? '/').(isset($parsed['query']) ? '?'.$parsed['query'] : '');
        }

        return null;
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withHeaders(['X-Cisco-Meraki-API-Key' => $this->apiKey])
            ->acceptJson()
            ->timeout(30);
    }
}
