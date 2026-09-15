<?php

namespace App\SyncAdapters\Osctrl;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the osctrl Admin API. Owns auth for the nodes
 * endpoint the sync adapter uses. Yields raw decoded JSON, no
 * normalization (that's the adapter's job).
 *
 * osctrl is environment-scoped: each install can host multiple osquery
 * enrollment environments (prod, dev, staging, etc.) and the API path
 * takes the environment name. Callers pass it into nodes().
 *
 * Auth model: JWT bearer token generated in the osctrl admin console
 * (Users -> API tokens).
 *
 * osctrl API reference: https://osctrl.net/usage/api/
 */
class OsctrlClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
    ) {}

    /**
     * List every enrolled node in the given osctrl environment. The
     * nodes endpoint returns the whole list in a single response, so
     * no pagination is needed. the generator shape is kept for
     * consistency with the other adapters.
     *
     * @return iterable<array<string, mixed>>
     */
    public function nodes(string $environment): iterable
    {
        $response = $this->request()
            ->get('/api/v1/nodes/'.$environment.'/all')
            ->throw()
            ->json();

        // osctrl returns a bare array of node objects on this endpoint.
        $nodes = is_array($response) ? $response : [];

        foreach ($nodes as $node) {
            yield $node;
        }
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->token)
            ->acceptJson()
            ->timeout(30);
    }
}
