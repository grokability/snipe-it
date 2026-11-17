<?php

namespace App\SyncAdapters\GoogleWorkspace;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Google Admin SDK Directory API for Chrome
 * device management. Handles service-account JWT signing (RS256),
 * OAuth 2.0 token exchange with domain-wide delegation, and Admin
 * SDK's pageToken pagination.
 *
 * Auth model: the admin creates a service account in Google Cloud
 * Console, downloads its JSON key, and grants it domain-wide
 * delegation for the scopes below in Google Admin Console. On every
 * sync, the client signs a JWT that names the target admin user in
 * its `sub` claim, exchanges it at oauth2.googleapis.com for a short-
 * lived bearer, and caches that bearer for the life of the client.
 *
 * Scopes requested:
 *   - admin.directory.device.chromeos    (read + write per-device metadata)
 *   - admin.directory.orgunit.readonly   (list OUs for group scoping)
 *
 * If the admin only granted the .readonly counterpart of the first
 * scope during delegation, pull works but push fails with 403 at
 * PATCH time. Not something we can predict from client-side probing
 * so it surfaces as a normal push error.
 */
class GoogleWorkspaceClient
{
    use RetriesTransientHttpErrors;

    private const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';

    private const ADMIN_SDK_BASE_URL = 'https://admin.googleapis.com/admin/directory/v1';

    private const REQUIRED_SCOPES = [
        'https://www.googleapis.com/auth/admin.directory.device.chromeos',
        'https://www.googleapis.com/auth/admin.directory.orgunit.readonly',
    ];

    private ?string $accessToken = null;

    public function __construct(
        private readonly string $serviceAccountEmail,
        private readonly string $privateKeyPem,
        private readonly string $impersonateEmail,
        private readonly string $customerId = 'my_customer',
    ) {}

    /**
     * Iterate every Chrome device visible to the impersonated admin.
     * Admin SDK caps the page size at 300 devices. Uses `nextPageToken`
     * for forward paging.
     *
     * projection=FULL returns every field the API exposes (recent
     * users, network history, dev mode, boot mode, etc.). BASIC omits
     * most of what the adapter maps into extras, so FULL is worth the
     * per-page cost.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function chromeosDevices(int $pageSize = 200): iterable
    {
        $pageToken = null;

        do {
            $params = [
                'projection' => 'FULL',
                'maxResults' => $pageSize,
            ];
            if (is_string($pageToken)) {
                $params['pageToken'] = $pageToken;
            }

            $response = $this->request()
                ->get('/customer/'.$this->customerId.'/devices/chromeos', $params)
                ->throw()
                ->json();

            foreach (($response['chromeosdevices'] ?? []) as $device) {
                yield $device;
            }

            $pageToken = $response['nextPageToken'] ?? null;
        } while (is_string($pageToken) && $pageToken !== '');
    }

    /**
     * List every organizational unit under the customer root. Result
     * is a flat array of unit objects, each with orgUnitPath,
     * orgUnitId, name, description. The adapter uses orgUnitPath as
     * the vendor group id.
     *
     * @return array<int, array<string, mixed>>
     */
    public function orgUnits(): array
    {
        $response = $this->request()
            ->get('/customer/'.$this->customerId.'/orgunits', [
                'type' => 'all',
            ])
            ->throw()
            ->json();

        return $response['organizationUnits'] ?? [];
    }

    /**
     * PATCH writable fields on a single Chrome device. Fields the
     * caller doesn't include stay untouched. Google accepts partial
     * updates on annotatedAssetId, annotatedLocation, annotatedUser,
     * notes, and orgUnitPath.
     *
     * @param  array<string, mixed>  $payload
     */
    public function updateChromeosDevice(string $deviceId, array $payload): void
    {
        Http::baseUrl(self::ADMIN_SDK_BASE_URL)
            ->withToken($this->bearer())
            ->withOptions(['allow_redirects' => false])
            ->asJson()
            ->acceptJson()
            ->timeout(30)
            ->patch('/customer/'.$this->customerId.'/devices/chromeos/'.$deviceId, $payload)
            ->throw();
    }

    /**
     * Sign an RS256 JWT and exchange it for an OAuth bearer via the
     * urn:ietf:params:oauth:grant-type:jwt-bearer flow. Token is
     * short-lived (~1 hour) and cached on this instance so a full
     * sync doesn't retrigger auth per request.
     */
    private function bearer(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $assertion = $this->buildJwtAssertion();

        $response = Http::asForm()
            ->withOptions(['allow_redirects' => false])
            ->timeout(30)
            ->post(self::TOKEN_ENDPOINT, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ])
            ->throw()
            ->json();

        return $this->accessToken = (string) ($response['access_token'] ?? '');
    }

    /**
     * Sign an RS256 JWT with the service account private key. Google
     * requires a max 1-hour expiration. We use 55 minutes to give the
     * exchange enough slack, matching Google's own client-library
     * default. `sub` names the impersonated admin (domain-wide
     * delegation, without which the Directory API returns 401).
     */
    private function buildJwtAssertion(): string
    {
        $now = time();
        $payload = [
            'iss' => $this->serviceAccountEmail,
            'scope' => implode(' ', self::REQUIRED_SCOPES),
            'aud' => self::TOKEN_ENDPOINT,
            'iat' => $now,
            'exp' => $now + 3300,
            'sub' => $this->impersonateEmail,
        ];

        return JWT::encode($payload, $this->privateKeyPem, 'RS256');
    }

    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl(self::ADMIN_SDK_BASE_URL)
                ->withToken($this->bearer())
                ->withOptions(['allow_redirects' => false])
                ->acceptJson()
                ->timeout(30)
        );
    }
}
