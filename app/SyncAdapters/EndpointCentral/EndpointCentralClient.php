<?php

namespace App\SyncAdapters\EndpointCentral;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the ManageEngine Endpoint Central API. Owns auth
 * + pagination for the endpoints the sync adapter uses. Yields raw
 * decoded JSON, no normalization (that's the adapter's job).
 *
 * ME Endpoint Central ships in two flavors with different auth models:
 *
 *   Cloud (hosted on Zoho's infrastructure): OAuth 2.0 refresh-token
 *   flow. The admin generates a refresh token out-of-band via Zoho's
 *   self-client flow at api-console.zoho.com with scope
 *   DesktopCentralCloud.SOM.READ (plus .Inventory.READ for the
 *   hardware opt-in, and .SOM.CREATE for push). We exchange the
 *   long-lived refresh_token for a 1-hour access_token on first use
 *   and cache it on this instance. Cloud uses multiple regional
 *   accounts hosts (US / EU / IN / AU / CN / JP) that admins pick on
 *   the settings page.
 *
 *   On-prem (self-hosted): the admin generates an API Key /
 *   AuthToken in the Admin -> Integrations area of the on-prem
 *   console and pastes it into Snipe-IT. We send it as a
 *   `Authorization: Zoho-authtoken {token}` header, which is the
 *   historical ME header shape still used by current on-prem
 *   releases per the vendor docs. FIXME: this needs confirmation
 *   against a modern on-prem tenant since the ME docs on the auth
 *   shape have some gaps in public cloud docs. Admins seeing 401 should
 *   file an issue so we can switch to Bearer / Basic as needed.
 *
 * ME API reference: https://www.manageengine.com/products/desktop-central/help/api/cloud/api-index.html
 */
class EndpointCentralClient
{
    use RetriesTransientHttpErrors;

    public const MODE_CLOUD = 'cloud';

    public const MODE_ONPREM = 'onprem';

    /**
     * Zoho's regional accounts hosts for the OAuth token endpoint.
     * The data_center setting on the adapter maps to one of these.
     * Keeping the map here keeps the adapter's settings schema free
     * of the specific URLs and avoids drift if Zoho adds a region.
     *
     * @var array<string, string>
     */
    public const ZOHO_ACCOUNTS_HOSTS = [
        'us' => 'https://accounts.zoho.com',
        'eu' => 'https://accounts.zoho.eu',
        'in' => 'https://accounts.zoho.in',
        'au' => 'https://accounts.zoho.com.au',
        'cn' => 'https://accounts.zoho.com.cn',
        'jp' => 'https://accounts.zoho.jp',
    ];

    private ?string $accessToken = null;

    private ?int $expiresAt = null;

    public function __construct(
        private readonly string $mode,
        private readonly string $baseUrl,
        // Cloud mode: client credentials from Zoho self-client + refresh token.
        private readonly ?string $clientId = null,
        private readonly ?string $clientSecret = null,
        private readonly ?string $refreshToken = null,
        // Cloud mode: Zoho regional accounts host. Keyed by MODE_CLOUD + a data_center selector in the adapter settings.
        private readonly ?string $accountsHost = null,
        // On-prem mode: API Key / AuthToken from Admin -> Integrations in the on-prem console.
        private readonly ?string $authToken = null,
    ) {
    }

    /**
     * Iterate every managed computer on the tenant, one page at a
     * time. Returns a generator so callers stream through large
     * fleets without holding the whole list in memory.
     *
     * The list endpoint returns computerId / computerName /
     * computerFQDNName / ipAddress / macAddress / osName /
     * osVersion / osPlatform / lastContactTime / loggedUsers /
     * agentVersion / domainName / remoteOfficeName per device. It
     * does NOT return hardware serial. Serial resolution lives on
     * the per-device hardwareDetails() endpoint and is opt-in on
     * the adapter.
     *
     * @return iterable<array<string, mixed>>
     */
    public function computers(int $pageSize = 100): iterable
    {
        $page = 1;

        do {
            $response = $this->request()
                ->get('/dcapi/som/computersInfo', [
                    'page' => $page,
                    'pageLimit' => $pageSize,
                ])
                ->throw()
                ->json();

            // ME wraps list responses in {message_response: {computers: [...]}, message_type, status, message_version}. The exact wrapper key varies between the /dcapi/* paths and the legacy /api/1.4/* paths. Walk the common locations and bail cleanly on unexpected shapes.
            $results = $response['message_response']['computers']
                ?? $response['computers']
                ?? (is_array($response) ? $response : []);

            if (!is_array($results)) {
                $results = [];
            }

            foreach ($results as $computer) {
                yield $computer;
            }

            // ME paginates by page number and returns a short page on
            // the final response. No totalCount in the common response
            // shape, so a short page is the terminal signal.
            $done = count($results) < $pageSize;
            $page++;
        } while (!$done);
    }

    /**
     * Fetch the rich hardware details for one device. Only called
     * when the admin has enabled the hardware opt-in on the adapter.
     * The serial number lives under biosDetails.serialNumber on
     * most device classes.
     *
     * Returns null on any failure so one bad device does not abort
     * the entire pull.
     *
     * @return array<string, mixed>|null
     */
    public function hardwareDetails(string $resourceId): ?array
    {
        try {
            $response = $this->request()
                ->get('/dcapi/inventory/computers/' . rawurlencode($resourceId) . '/hardware')
                ->throwIfServerError()
                ->json();
        } catch (\Throwable) {
            return null;
        }

        if (!is_array($response)) {
            return null;
        }

        return $response['message_response'] ?? $response;
    }

    /**
     * Push an asset_tag update to one device. ME exposes a "Remarks"
     * freeform field on each computer that admins use for ad-hoc
     * tagging. The push surface maps Snipe-IT's asset_tag onto that
     * remarks field via POST /dcapi/som/computers/updateRemarks.
     *
     * FIXME: unverified against a live tenant. The remarks endpoint
     * is documented in the Cloud API index but the request body
     * shape is based on the ME convention of wrapping writes in
     * message_type/message_response envelopes. Expect to adjust if
     * admins hit 400/404 on first use.
     *
     * @param  array<string, mixed>  $payload
     */
    public function updateRemarks(string $resourceId, array $payload): void
    {
        $this->request()
            ->post('/dcapi/som/computers/updateRemarks', array_merge(
                ['resourceIds' => [$resourceId]],
                $payload,
            ))
            ->throw();
    }

    private function request(): PendingRequest
    {
        $request = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withOptions(['allow_redirects' => false])
            ->acceptJson()
            ->timeout(30);

        if ($this->mode === self::MODE_CLOUD) {
            $request = $request->withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $this->bearer(),
            ]);
        } else {
            // On-prem AuthToken header per ME's historical scheme.
            // FIXME: confirm against a modern on-prem tenant.
            $request = $request->withHeaders([
                'Authorization' => 'Zoho-authtoken ' . $this->authToken,
            ]);
        }

        return $this->withTransientRetry($request);
    }

    /**
     * Exchange the long-lived Zoho refresh token for a 1-hour access
     * token via accounts.zoho.{region}/oauth/v2/token. Cached on this
     * instance with a 60-second buffer before expiry so a slow sync
     * does not present a token that is about to be rejected.
     *
     * Response shape (per Zoho docs):
     *   {access_token, scope, api_domain, token_type, expires_in}
     */
    private function bearer(): string
    {
        $now = time();
        if ($this->accessToken !== null && $this->expiresAt !== null && $now < $this->expiresAt - 60) {
            return $this->accessToken;
        }

        $response = Http::asForm()
            ->withOptions(['allow_redirects' => false])
            ->timeout(30)
            ->post(rtrim((string) $this->accountsHost, '/') . '/oauth/v2/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refreshToken,
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
