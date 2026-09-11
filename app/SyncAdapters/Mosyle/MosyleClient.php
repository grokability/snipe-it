<?php

namespace App\SyncAdapters\Mosyle;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Mosyle API. Owns auth + pagination for the
 * endpoints the sync adapter uses. Yields raw decoded JSON, no
 * normalization (that's the adapter's job).
 *
 * Auth model: bearer access token generated in the Mosyle admin console
 * (Account -> API). The same token works for both Mosyle Manager v2
 * (https://managerapi.mosyle.com/v2) and Mosyle Business v1
 * (https://businessapi.mosyle.com/v1). the base URL configured on the
 * adapter determines which product this instance talks to.
 *
 * Mosyle's devices endpoint takes POST with a JSON body (not GET with
 * query params like the other vendors here), which is unusual but
 * documented and stable.
 *
 * Mosyle API reference: https://school.mosyle.com/api/docs
 */
class MosyleClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
    ) {}

    /**
     * Iterate every managed device in the Mosyle tenant, one page at a
     * time. Returns a generator so callers stream through large fleets
     * without holding the whole list in memory.
     *
     * @return iterable<array<string, mixed>>
     */
    public function devices(int $perPage = 200): iterable
    {
        $page = 1;

        do {
            $response = $this->request()
                ->post('/devices', [
                    'options' => [
                        'page' => $page,
                        'per_page' => $perPage,
                    ],
                ])
                ->throw()
                ->json();

            // Mosyle's response nests devices under
            // response[0].response.rows on Manager v2 and under
            // response.devices on Business v1. Try both shapes so this
            // client works against either product.
            $rows = $response['response'][0]['response']['rows']
                ?? $response['response']['devices']
                ?? [];

            foreach ($rows as $device) {
                yield $device;
            }

            $done = count($rows) < $perPage;
            $page++;
        } while (! $done);
    }

    /**
     * List every Location in the Mosyle tenant. Used by the
     * adapter's fetchGroups() so admins can map Locations to
     * Snipe-IT companies. Mosyle uses POST for reads (same shape
     * as devices()).
     *
     * @return array<int, array<string, mixed>>
     */
    public function locations(): array
    {
        $response = $this->request()
            ->post('/locations', [])
            ->throw()
            ->json();

        return $response['response'][0]['response']['rows']
            ?? $response['response']['locations']
            ?? [];
    }

    /**
     * Update writable per-device metadata by serial number. Mosyle's
     * write API is a single POST endpoint dispatched by the
     * `operation` field. `set_asset_tag_by_serial_number` sets the
     * device's asset_tag field per Mosyle's Manager v2 API. Fields
     * the caller doesn't include stay untouched.
     */
    public function updateDeviceAssetTagBySerial(string $serial, string $assetTag): void
    {
        $this->request()
            ->post('/devices', [
                'operation' => 'set_asset_tag_by_serial_number',
                'serial_number' => $serial,
                'asset_tag' => $assetTag,
            ])
            ->throw();
    }

    /**
     * Set the device's notes field. Same operation-dispatch shape
     * as asset_tag. Mosyle Manager v2 exposes `set_notes_by_serial_number`
     * for admin freeform notes. On tenants where the operation
     * doesn't exist Mosyle returns a 4xx that surfaces in the log.
     */
    public function updateDeviceNotesBySerial(string $serial, string $notes): void
    {
        $this->request()
            ->post('/devices', [
                'operation' => 'set_notes_by_serial_number',
                'serial_number' => $serial,
                'notes' => $notes,
            ])
            ->throw();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->token)
            ->acceptJson()
            ->asJson()
            ->timeout(30);
    }
}
