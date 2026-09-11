<?php

namespace App\SyncAdapters\Jamf;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Jamf Pro API. Owns auth + pagination for the
 * endpoints the sync adapter uses. Yields raw decoded JSON, no
 * normalization (that's the adapter's job).
 *
 * Auth model: a Jamf-generated Personal Access Token (create one under
 * Settings -> System -> API Roles and Clients -> API Roles) passed as
 * a bearer token. This skips the /api/v1/auth/token exchange dance and
 * keeps the credential storage shape identical to the other bearer-
 * token adapters (Fleet, Kandji, JumpCloud).
 *
 * Jamf Pro API reference: https://developer.jamf.com/jamf-pro/reference
 */
class JamfClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
    ) {}

    /**
     * Iterate every managed computer in the Jamf tenant, one page at a
     * time. Returns a generator so callers stream through large fleets
     * without holding the whole list in memory.
     *
     * @return iterable<array<string, mixed>>
     */
    public function computers(int $pageSize = 100): iterable
    {
        $page = 0;

        do {
            $response = $this->request()
                ->get('/api/v1/computers-inventory', [
                    'page' => $page,
                    'page-size' => $pageSize,
                    // Only the sections we normalize. skipping the
                    // heavier ones (applications, plugins, fonts,
                    // certificates) keeps payloads small and stays
                    // well under Jamf's per-tenant rate limit.
                    'section' => ['GENERAL', 'HARDWARE', 'OPERATING_SYSTEM', 'USER_AND_LOCATION'],
                ])
                ->throw()
                ->json();

            $results = $response['results'] ?? [];
            foreach ($results as $computer) {
                yield $computer;
            }

            // Jamf returns totalCount on this endpoint. Stop when we've
            // seen enough results or when a short page comes back.
            $total = $response['totalCount'] ?? null;
            $seenSoFar = ($page + 1) * $pageSize;
            $done = count($results) < $pageSize
                || ($total !== null && $seenSoFar >= $total);
            $page++;
        } while (! $done);
    }

    /**
     * List every Site in the Jamf Pro tenant. Used by the adapter's
     * fetchGroups() so admins can map Sites to Snipe-IT companies.
     * Response shape is a bare array of {id, name} objects.
     *
     * @return array<int, array<string, mixed>>
     */
    public function sites(): array
    {
        $response = $this->request()
            ->get('/api/v1/sites')
            ->throw()
            ->json();

        return is_array($response) ? $response : [];
    }

    /**
     * Update a computer's inventory detail via Jamf Pro's newer JSON
     * API. Accepts a partial payload with nested objects like
     * `userAndLocation` (assetTag lives at userAndLocation.assetTag).
     * Fields the caller doesn't include stay untouched.
     *
     * @param  array<string, mixed>  $payload
     */
    public function updateComputerDetail(string $computerId, array $payload): void
    {
        $this->request()
            ->asJson()
            ->patch('/api/v1/computers-inventory-detail/'.$computerId, $payload)
            ->throw();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->token)
            ->acceptJson()
            ->timeout(30);
    }
}
