<?php

namespace App\SyncAdapters\MosyleBusiness;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Mosyle Business v1 API. Owns auth +
 * pagination for the endpoints the sync adapter uses. Yields raw
 * decoded JSON, no normalization (that's the adapter's job).
 *
 * Auth model (Mosyle Business v1):
 *   1. POST /login with the accessToken in the request HEADER and
 *      { email, password } in the body. Response carries a JWT bearer
 *      token in the Authorization response header, valid 24h.
 *   2. Every subsequent request carries BOTH the accessToken in the
 *      request header AND the JWT in the Authorization: Bearer header.
 *      Business keeps the accessToken header-only. Manager keeps the
 *      accessToken body-only. Do not confuse the two.
 *
 * Device endpoint shape is also different from Manager v2: Business
 * uses a dispatcher pattern on POST /devices keyed by the `operation`
 * field (list / update_device / shutdown_devices / wipe_devices /
 * assign_devices / etc.). The response is wrapped in an array
 * (`response[0].devices`) rather than Manager's object form
 * (`response.devices`). The OS enum uses `mac` where Manager uses
 * `macos`.
 *
 * Business adds a real `notes` push field, which Manager does not
 * expose. The adapter uses that to opt into composed-notes push.
 *
 * JWT is cached on this instance for the life of the sync run. No
 * cross-run persistence. One sync spawns one client so one login
 * services every /devices page call.
 */
class MosyleBusinessClient
{
    // Mosyle Business splits devices by OS, which is a required option
    // on /devices list. A full-tenant pull iterates each OS. Note that
    // Business uses `mac` not `macos` (Manager uses `macos`).
    private const DEVICE_OSES = [
        'mac',
        'ios',
        'tvos',
        'visionos',
    ];

    private ?string $jwt = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $accessToken,
        private readonly string $email,
        private readonly string $password,
    ) {}

    /**
     * Iterate every managed device in the Mosyle Business tenant
     * across every supported OS, one page at a time. Returns a
     * generator so callers stream through large fleets without
     * holding the whole list in memory.
     *
     * @return iterable<array<string, mixed>>
     */
    public function devices(int $pageSize = 200): iterable
    {
        foreach (self::DEVICE_OSES as $os) {
            yield from $this->devicesForOs($os, $pageSize);
        }
    }

    /**
     * Paginate /devices list for a single OS. Terminates when a page
     * comes back shorter than page_size.
     *
     * @return iterable<array<string, mixed>>
     */
    private function devicesForOs(string $os, int $pageSize): iterable
    {
        $page = 1;

        do {
            $response = $this->request()
                ->post('/devices', [
                    'operation' => 'list',
                    'options' => [
                        'os' => $os,
                        'page_size' => $pageSize,
                        'page' => $page,
                    ],
                ])
                ->throw()
                ->json();

            // Business wraps the response in an array:
            // `response[0].devices`. Manager keeps it as an object:
            // `response.devices`. Preserve the array unwrap carefully.
            $rows = $response['response'][0]['devices'] ?? [];

            foreach ($rows as $device) {
                yield $device;
            }

            $done = count($rows) < $pageSize;
            $page++;
        } while (! $done);
    }

    /**
     * Update writable per-device metadata by serial number. Business
     * uses the dispatcher pattern: POST /devices with
     * operation=update_device and the serialnumber + updatable fields
     * at the top level of the body (no `elements` array like Manager).
     *
     * Fields the caller doesn't include stay untouched. Business
     * exposes asset_tag, device_name, tags, lockmessage, and notes.
     * The adapter uses asset_tag and notes.
     *
     * @param  array<string, scalar|null>  $fields
     */
    public function updateDeviceBySerial(string $serial, array $fields): void
    {
        $payload = array_merge(
            [
                'operation' => 'update_device',
                'serialnumber' => $serial,
            ],
            $fields,
        );

        $this->request()
            ->post('/devices', $payload)
            ->throw();
    }

    /**
     * Fetch (or reuse) the JWT bearer token. First call POSTs to
     * /login with accessToken in the HEADER and email/password in
     * the body. Subsequent calls reuse the cached JWT for the life
     * of this client instance.
     *
     * Mosyle returns the JWT in the Authorization response header,
     * not the body.
     */
    private function bearer(): string
    {
        if ($this->jwt !== null) {
            return $this->jwt;
        }

        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withOptions(['allow_redirects' => false])
            ->withHeaders(['accessToken' => $this->accessToken])
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post('/login', [
                'email' => $this->email,
                'password' => $this->password,
            ])
            ->throw();

        $header = $response->header('Authorization');
        if ($header === '' || ! str_starts_with($header, 'Bearer ')) {
            throw new \RuntimeException('Mosyle Business /login did not return a Bearer token in the Authorization response header. Check the access token, email, and password.');
        }

        return $this->jwt = substr($header, 7);
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withOptions(['allow_redirects' => false])
            ->withHeaders([
                'accessToken' => $this->accessToken,
                'Authorization' => 'Bearer '.$this->bearer(),
            ])
            ->acceptJson()
            ->asJson()
            ->timeout(30);
    }
}
