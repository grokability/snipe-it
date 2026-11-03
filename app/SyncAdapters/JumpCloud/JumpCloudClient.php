<?php

namespace App\SyncAdapters\JumpCloud;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
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
    use RetriesTransientHttpErrors;

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

    /**
     * Iterate the V2 System Insights `system_info` collection. This is
     * the only JumpCloud endpoint that returns actual hardware_model
     * and hardware_vendor strings. V1 `/systems` has `systemType` which
     * is a free-form label (often null for non-Mac enrollments) and
     * no vendor at all.
     *
     * System Insights is a paid-tier feature on some JumpCloud plans.
     * A tenant without the entitlement gets 403 here, which the caller
     * is expected to translate into "fall back to V1 shape" rather
     * than aborting the sync. This method does not catch the 403 so
     * the adapter can decide the recovery policy explicitly.
     *
     * Returns the V2 flat shape unchanged. The adapter joins to V1
     * `/systems` on `system_id`.
     *
     * @return iterable<array<string, mixed>>
     */
    public function systemInsights(int $limit = 100): iterable
    {
        $skip = 0;

        do {
            $response = $this->request()
                ->get('/v2/systeminsights/system_info', [
                    'limit' => $limit,
                    'skip' => $skip,
                ])
                ->throw()
                ->json();

            // V2 System Insights returns a flat array of records (no
            // envelope / totalCount), so pagination terminates on a
            // short page.
            $results = is_array($response) ? $response : [];
            foreach ($results as $insight) {
                yield $insight;
            }

            $skip += $limit;
        } while (count($results) >= $limit);
    }

    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl(rtrim($this->baseUrl, '/'))
                ->withOptions(['allow_redirects' => false])
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->acceptJson()
                ->timeout(30)
        );
    }
}
