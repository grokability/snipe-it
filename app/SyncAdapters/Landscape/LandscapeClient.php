<?php

namespace App\SyncAdapters\Landscape;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Landscape v2 REST API. Owns pagination over
 * the computers endpoint and yields raw decoded JSON, normalization is
 * the adapter's job.
 *
 * Landscape API reference: https://ubuntu.com/landscape/docs/reference/api/
 */
class LandscapeClient
{
    use RetriesTransientHttpErrors;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
    ) {}

    /**
     * Stream every computer in the account, one page at a time.
     * Requests grouped hardware so downstream normalization can
     * extract serial, vendor, and model from the same call rather
     * than paying an N+1 detail-fetch per host.
     *
     * @return iterable<array<string, mixed>>
     */
    public function computers(int $perPage = 500): iterable
    {
        $offset = 0;

        do {
            $response = $this->request()
                ->get('/api/v2/computers', [
                    'limit' => $perPage,
                    'offset' => $offset,
                    'with_grouped_hardware' => 'true',
                ])
                ->throw()
                ->json();

            $results = $response['results'] ?? [];
            foreach ($results as $computer) {
                yield $computer;
            }

            // Landscape returns a `next` URL on paginated responses
            // and null once the caller has walked the full list. Prefer
            // that signal over offset arithmetic so we stop as soon as
            // the server says we are done.
            $hasNext = ! empty($response['next'] ?? null);
            $offset += $perPage;
        } while ($hasNext);
    }

    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->withOptions(['allow_redirects' => false])
                ->withToken($this->token)
                ->acceptJson()
                ->timeout(60)
        );
    }
}
