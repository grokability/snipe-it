<?php

namespace App\SyncAdapters\Zentral;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Zentral inventory API. Owns auth +
 * pagination for the endpoints the sync adapter uses.
 *
 * Auth model: service account API token from the Zentral admin
 * console, passed via the `Authorization: Token <token>` header
 * (Django REST Framework's TokenAuthentication shape).
 *
 * Zentral aggregates inventory from many sources (Munki, Santa,
 * MDM, Osquery, etc.). The /api/inventory/machines/ endpoint returns
 * the merged view: one row per physical machine keyed on serial.
 */
class ZentralClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
    ) {}

    /**
     * Iterate every machine snapshot in the Zentral inventory.
     *
     * @return iterable<int, array<string, mixed>>
     */
    public function machines(int $limit = 100): iterable
    {
        $offset = 0;

        do {
            $response = $this->request()
                ->get('/api/inventory/machines/', [
                    'limit' => $limit,
                    'offset' => $offset,
                ])
                ->throw()
                ->json();

            $results = $response['results'] ?? (is_array($response) ? $response : []);
            foreach ($results as $machine) {
                yield $machine;
            }

            $total = $response['count'] ?? null;
            $seenSoFar = $offset + count($results);
            $done = count($results) < $limit
                || ($total !== null && $seenSoFar >= $total);
            $offset += $limit;
        } while (! $done);
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withHeaders(['Authorization' => 'Token '.$this->token])
            ->acceptJson()
            ->timeout(30);
    }
}
