<?php

namespace App\SyncAdapters\KaseyaVsa10;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Kaseya VSA 10 (formerly Kaseya X, product code
 * "vsax") REST API v3. Auth is HTTP Basic with a paired token id and
 * token secret issued from the VSA 10 admin console. Both halves are
 * treated as secret because the token id is opaque and offers no
 * value to expose. Base URL is customer-specific (each tenant has its
 * own VSA server hostname), so admins paste theirs on the settings
 * page.
 *
 * Pull uses the /api/v3/assets endpoint (not /devices). VSA models
 * managed endpoints as "assets" in the API sense, with hardware
 * inventory nested under an AssetInfo array of category blocks
 * (System, BIOS, Operating System, etc.). /devices returns a coarser
 * summary without hardware info, so /assets is the right choice for a
 * Snipe-IT inventory adapter.
 *
 * Pagination follows OData conventions: $top and $skip. VSA emits a
 * NextQueryLink cursor when a result set exceeds 5,000 items. This
 * client uses $skip pagination for simplicity. If a tenant regularly
 * runs past 5k assets, we can add NextQueryLink following later.
 */
class KaseyaVsa10Client
{
    private ?string $basicAuth = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $tokenId,
        private readonly string $tokenSecret,
    ) {}

    /**
     * Iterate every asset visible to the API token. Requests /assets in
     * $top-sized pages and follows $skip forward until a short page
     * comes back. Yields the raw asset rows so the adapter's
     * normalize() can pull fields by their VSA-specific names.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function assets(int $pageSize = 500): iterable
    {
        $skip = 0;

        do {
            $response = $this->request()
                ->get('/assets', [
                    '$top' => $pageSize,
                    '$skip' => $skip,
                ])
                ->throw()
                ->json();

            // VSA wraps in {Data: [...], Meta: {TotalCount, ResponseCode}}.
            // Some smaller endpoints return a bare array, so handle both.
            $rows = $response['Data'] ?? (is_array($response) ? $response : []);
            $count = count($rows);

            foreach ($rows as $row) {
                yield $row;
            }

            $skip += $count;
        } while ($count === $pageSize);
    }

    /**
     * Fetch the full inventory record for a single asset. Not used by
     * the primary pull path (the list endpoint already returns the
     * full inventory shape) but handy for on-demand refresh.
     *
     * @return array<string, mixed>
     */
    public function asset(string $identifier): array
    {
        return $this->request()
            ->get('/assets/'.$identifier)
            ->throw()
            ->json();
    }

    /**
     * Fetch every custom-field definition applicable to devices. VSA
     * scopes custom fields to entity types via a Contexts array, so
     * we OData-filter to only those whose Contexts include "Device"
     * to keep the mapping UI free of org-only / site-only fields
     * admins can't map to a per-device Snipe-IT column anyway. The
     * ExpirationDate filter drops archived fields.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function deviceCustomFieldDefinitions(int $pageSize = 100): iterable
    {
        $skip = 0;

        do {
            $response = $this->request()
                ->get('/customfields', [
                    '$top' => $pageSize,
                    '$skip' => $skip,
                    '$filter' => "Contexts/any(p: p eq 'Device') and ExpirationDate eq null",
                ])
                ->throw()
                ->json();

            $rows = $response['Data'] ?? (is_array($response) ? $response : []);
            $count = count($rows);

            foreach ($rows as $row) {
                yield $row;
            }

            $skip += $count;
        } while ($count === $pageSize);
    }

    /**
     * Fetch the custom-field values for a single device. Response
     * shape is {Data: [{Id, Name, Value, Type}, ...]}. The Identifier
     * GUID is shared between the /assets and /devices endpoints, so
     * callers pass the same GUID they already have from the pull.
     *
     * @return array<int, array<string, mixed>>
     */
    public function deviceCustomFieldValues(string $identifier): array
    {
        $response = $this->request()
            ->get('/devices/'.$identifier.'/customfields')
            ->throw()
            ->json();

        return $response['Data'] ?? (is_array($response) ? $response : []);
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/').'/api/v3')
            ->withHeaders(['Authorization' => 'Basic '.$this->basicAuth()])
            ->acceptJson()
            ->timeout(30);
    }

    /**
     * Encode the paired token as a Basic auth header value. Cached on
     * this instance so we don't recompute per request. Empty either
     * half produces the empty string. The API will reject it with 401
     * on the first call, which surfaces the misconfiguration to the
     * admin via the sync log.
     */
    private function basicAuth(): string
    {
        if ($this->basicAuth !== null) {
            return $this->basicAuth;
        }

        return $this->basicAuth = base64_encode($this->tokenId.':'.$this->tokenSecret);
    }
}
