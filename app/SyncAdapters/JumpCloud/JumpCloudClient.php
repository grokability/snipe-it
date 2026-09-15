<?php

namespace App\SyncAdapters\JumpCloud;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the JumpCloud Systems API. Owns auth + pagination
 * for the endpoints the sync adapter uses. Yields raw decoded JSON, no
 * normalization (that's the adapter's job).
 *
 * Auth model: the tenant's static API key from the JumpCloud admin
 * console, passed via `x-api-key` header (JumpCloud's convention, not
 * a bearer token like the other adapters).
 *
 * JumpCloud API reference: https://docs.jumpcloud.com/api/1.0
 */
class JumpCloudClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {}

    /**
     * Iterate every managed system in the JumpCloud tenant, one page at
     * a time. Returns a generator so callers stream through large
     * fleets without holding the whole list in memory.
     *
     * @return iterable<array<string, mixed>>
     */
    public function systems(int $limit = 100): iterable
    {
        $skip = 0;

        do {
            $response = $this->request()
                ->get('/systems', [
                    'limit' => $limit,
                    'skip' => $skip,
                ])
                ->throw()
                ->json();

            $results = $response['results'] ?? [];
            foreach ($results as $system) {
                yield $system;
            }

            // JumpCloud returns totalCount on this endpoint. Stop when
            // seen-so-far reaches it or the vendor sends a short page.
            $total = $response['totalCount'] ?? null;
            $seenSoFar = $skip + count($results);
            $done = count($results) < $limit
                || ($total !== null && $seenSoFar >= $total);
            $skip += $limit;
        } while (! $done);
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withHeaders(['x-api-key' => $this->apiKey])
            ->acceptJson()
            ->timeout(30);
    }
}
