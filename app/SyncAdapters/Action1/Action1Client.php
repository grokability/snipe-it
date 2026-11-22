<?php

namespace App\SyncAdapters\Action1;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin HTTP client for the Action1 REST API (/api/3.0). Owns the
 * OAuth2 client-credentials token exchange, bearer-token caching, and
 * `from`/`limit` paged GETs for the endpoints collection.
 *
 * Action1 hosts customers in five regions, each on its own hostname.
 * The adapter picks one and passes the full base URL through. This
 * client never derives hosts. See Action1Adapter::regionBaseUrls().
 *
 * Auth flow (verified against Action1Corp/PSAction1):
 *   POST {base}/oauth2/token
 *     Body: JSON {client_id, client_secret}
 *     Response: {access_token, expires_in}
 *   GET  {base}/endpoints/managed/{org_id}?from=0&limit=200
 *     Header: Authorization: Bearer {token}
 *     Response: {items, from, limit, total_items, next_page}
 */
class Action1Client
{
    use RetriesTransientHttpErrors;

    private ?string $accessToken = null;

    private ?int $tokenExpiresAt = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    /**
     * Iterate every managed endpoint in the given Action1 organization.
     * Walks `from`/`limit` pagination until total_items is reached or
     * an empty page comes back. Limit is capped at the Action1 max of
     * 200 per page so one scheduled sync does the fewest round-trips.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function managedEndpoints(string $orgId, int $limit = 200): iterable
    {
        $path = '/endpoints/managed/'.rawurlencode($orgId);
        $from = 0;

        do {
            $response = $this->request()
                ->get($path, ['from' => $from, 'limit' => $limit])
                ->throw()
                ->json();

            $items = is_array($response['items'] ?? null) ? $response['items'] : [];
            foreach ($items as $item) {
                yield $item;
            }

            $total = isset($response['total_items']) && is_numeric($response['total_items'])
                ? (int) $response['total_items']
                : null;
            $returned = count($items);
            $from += $returned;

            // Stop when the server reports no more rows, when a page
            // returns short of the limit, or when total_items says
            // we're done. Belt-and-suspenders because Action1's own
            // paging helper also stops on "offset did not advance" to
            // avoid infinite loops on misbehaving endpoints.
        } while ($returned === $limit && ($total === null || $from < $total));
    }

    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->withOptions(['allow_redirects' => false])
                ->withToken($this->accessToken())
                ->acceptJson()
                ->timeout(30)
        );
    }

    /**
     * Fetch (and cache) an OAuth2 access token. Action1 reports
     * expires_in in seconds. We refresh 30 seconds early so a token
     * that expires mid-request doesn't 401 after the retry window
     * already consumed its budget.
     */
    private function accessToken(): string
    {
        if ($this->accessToken !== null && $this->tokenExpiresAt !== null && $this->tokenExpiresAt > time()) {
            return $this->accessToken;
        }

        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withOptions(['allow_redirects' => false])
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post('/oauth2/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ])
            ->throw()
            ->json();

        $this->accessToken = (string) ($response['access_token'] ?? '');
        $expiresIn = is_numeric($response['expires_in'] ?? null) ? (int) $response['expires_in'] : 3600;
        $this->tokenExpiresAt = time() + max(0, $expiresIn - 30);

        return $this->accessToken;
    }
}
