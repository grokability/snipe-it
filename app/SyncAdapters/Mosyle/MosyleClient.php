<?php

namespace App\SyncAdapters\Mosyle;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Mosyle Manager v2 API. Owns auth + pagination
 * for the endpoints the sync adapter uses. Yields raw decoded JSON, no
 * normalization (that's the adapter's job).
 *
 * Auth model (Mosyle Manager v2):
 *   1. POST /login with { accessToken, email, password } returns a JWT
 *      bearer token in the Authorization response header, valid 24h.
 *   2. Every subsequent request carries BOTH the accessToken in the
 *      request body AND the JWT in the Authorization: Bearer header.
 *      Dropping either returns a 401 "accessToken Required".
 *
 * JWT is cached on this instance for the life of the sync run (same
 * pattern IntuneClient uses for its OAuth 2.0 token). No cross-run
 * persistence. A single sync spawns a single client so one login
 * services every /listdevices page call.
 *
 * Reads use POST (/listdevices with options), writes use POST
 * (/devices with elements). Both carry accessToken in the body.
 *
 * The Mosyle API is not publicly documented. The endpoint and payload
 * shapes here match the Manager v2 doc transcription from issue #19790.
 * Business v1 (businessapi.mosyle.com/v1) is not covered by this
 * client. If an admin points the base URL at Business the login flow
 * may still succeed but device endpoints are expected to differ.
 */
class MosyleClient
{
    use RetriesTransientHttpErrors;

    // Mosyle Manager splits devices by OS, which is a required option
    // on /listdevices. A full-tenant pull iterates each OS.
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
     * Iterate every managed device in the Mosyle tenant across every
     * supported OS, one page at a time. Returns a generator so callers
     * stream through large fleets without holding the whole list in
     * memory.
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
     * Paginate /listdevices for a single OS. Terminates when a page
     * comes back shorter than page_size.
     *
     * @return iterable<array<string, mixed>>
     */
    private function devicesForOs(string $os, int $pageSize): iterable
    {
        $page = 1;

        do {
            $response = $this->request()
                ->post('/listdevices', [
                    'accessToken' => $this->accessToken,
                    'options' => [
                        'os' => $os,
                        'page_size' => $pageSize,
                        'page' => $page,
                    ],
                ])
                ->throw()
                ->json();

            $rows = $response['response']['devices'] ?? [];

            foreach ($rows as $device) {
                yield $device;
            }

            $done = count($rows) < $pageSize;
            $page++;
        } while (! $done);
    }

    /**
     * Update writable per-device metadata by serial number. Mosyle's
     * push API is POST /devices with an `elements` array keyed by
     * `serialnumber`. Each element may carry asset_tag and the other
     * documented writable fields. Fields the caller doesn't include
     * stay untouched.
     *
     * Mosyle Manager v2 does not expose a notes field for push, so the
     * adapter never calls this for notes.
     */
    public function updateDeviceAssetTagBySerial(string $serial, string $assetTag): void
    {
        $this->request()
            ->post('/devices', [
                'accessToken' => $this->accessToken,
                'elements' => [
                    [
                        'serialnumber' => $serial,
                        'asset_tag' => $assetTag,
                    ],
                ],
            ])
            ->throw();
    }

    /**
     * Fetch (or reuse) the JWT bearer token. First call POSTs to
     * /login. Subsequent calls reuse the cached value for the life of
     * this client instance.
     *
     * Mosyle returns the JWT in the Authorization response header, not
     * the body (unusual but documented).
     */
    private function bearer(): string
    {
        if ($this->jwt !== null) {
            return $this->jwt;
        }

        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withOptions(['allow_redirects' => false])
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post('/login', [
                'accessToken' => $this->accessToken,
                'email' => $this->email,
                'password' => $this->password,
            ])
            ->throw();

        $header = $response->header('Authorization');
        if ($header === '' || ! str_starts_with($header, 'Bearer ')) {
            throw new \RuntimeException('Mosyle /login did not return a Bearer token in the Authorization response header. Check the access token, email, and password.');
        }

        return $this->jwt = substr($header, 7);
    }

    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->withOptions(['allow_redirects' => false])
                ->withToken($this->bearer())
                ->acceptJson()
                ->asJson()
                ->timeout(30)
        );
    }
}
