<?php

namespace App\SyncAdapters\Fleet;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Fleet REST API. Owns auth + pagination for the
 * endpoints the sync adapter uses. Yields raw decoded JSON, no normalization
 * (that's the adapter's job).
 *
 * Fleet API reference: https://fleetdm.com/docs/rest-api/rest-api
 */
class FleetClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
    ) {}

    /**
     * Iterate over every host in Fleet, one page at a time. Returns a
     * generator so callers can stream through large fleets without holding
     * the whole list in memory.
     *
     * @return iterable<array<string, mixed>>
     */
    public function hosts(int $perPage = 100): iterable
    {
        $page = 0;

        do {
            $response = $this->request()
                ->get('/api/latest/fleet/hosts', [
                    'page' => $page,
                    'per_page' => $perPage,
                ])
                ->throw()
                ->json();

            $hosts = $response['hosts'] ?? [];
            foreach ($hosts as $host) {
                yield $host;
            }

            // Fleet doesn't return a total-pages count on this endpoint.
            // The accepted convention is to keep asking until a short page
            // comes back.
            $done = count($hosts) < $perPage;
            $page++;
        } while (! $done);
    }

    /**
     * List every Fleet Team on the instance. Used by the adapter's
     * fetchGroups() so admins can map Fleet Teams to Snipe-IT
     * companies. Teams endpoint returns the full list in one call.
     *
     * @return array<int, array<string, mixed>>
     */
    public function teams(): array
    {
        $response = $this->request()
            ->get('/api/latest/fleet/teams')
            ->throw()
            ->json();

        return $response['teams'] ?? [];
    }

    /**
     * Fetch the detailed host payload for a single host, including
     * device_mapping (the Fleet Free feature that surfaces email
     * associations from Google Chrome sync data). The list endpoint
     * used by hosts() returns a slimmed-down summary. Anything user-
     * assignment-related lives on this detail endpoint.
     *
     * @return array<string, mixed>|null
     */
    public function hostDetail(int $id): ?array
    {
        $response = $this->request()
            ->get('/api/latest/fleet/hosts/'.$id, ['device_mapping' => 'true'])
            ->throw()
            ->json();

        $host = $response['host'] ?? null;

        return is_array($host) ? $host : null;
    }

    /**
     * Fetch the instance's license tier by hitting /api/latest/fleet/config,
     * which returns a `license.tier` field ("free" or "premium"). Free
     * to call regardless of tier. Returns null when the config endpoint
     * is unreachable or returns an unrecognized shape so callers can
     * distinguish "confirmed free" from "we don't know" and default
     * conservatively.
     */
    public function licenseTier(): ?string
    {
        try {
            $config = $this->request()
                ->get('/api/latest/fleet/config')
                ->throw()
                ->json();
        } catch (\Throwable) {
            return null;
        }

        $tier = $config['license']['tier'] ?? null;

        return is_string($tier) ? strtolower($tier) : null;
    }

    /**
     * Upsert a manual label with the specified host as its sole member.
     * Fleet's spec-apply endpoint is gated behind Premium / gitops mode
     * on some builds, so this uses the individual /labels endpoints
     * instead: GET to find an existing label by name, PATCH to update
     * host membership, or POST to create when absent. Two API calls in
     * the update path, three in the create path (list + create + noop).
     *
     * Fleet identifies label hosts by "identifier" (hostname, hardware
     * serial, or UUID). Serial is the most stable of the three across
     * renames and re-enrollments, so callers pass that.
     *
     * Silently no-ops when either arg is blank so a partial-config
     * push doesn't punch a random label into the fleet.
     */
    public function upsertManualLabelForHost(string $labelName, string $hostIdentifier): void
    {
        if ($labelName === '' || $hostIdentifier === '') {
            return;
        }

        $existingId = $this->findLabelIdByName($labelName);

        if ($existingId !== null) {
            $this->request()
                ->patch('/api/latest/fleet/labels/'.$existingId, [
                    'hosts' => [$hostIdentifier],
                ])
                ->throw();

            return;
        }

        $this->request()
            ->post('/api/latest/fleet/labels', [
                'name' => $labelName,
                'description' => 'Managed by Snipe-IT sync push',
                'label_membership_type' => 'manual',
                'hosts' => [$hostIdentifier],
            ])
            ->throw();
    }

    /**
     * Look up a Fleet label id by its exact name. Fleet's /labels
     * endpoint doesn't support server-side name filtering, so we page
     * through and match client-side. Returns null when no label with
     * that name exists.
     */
    private function findLabelIdByName(string $name): ?int
    {
        $response = $this->request()
            ->get('/api/latest/fleet/labels', ['per_page' => 500])
            ->throw()
            ->json();

        foreach ($response['labels'] ?? [] as $label) {
            if (($label['name'] ?? null) === $name) {
                return (int) $label['id'];
            }
        }

        return null;
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withToken($this->token)
            ->acceptJson()
            ->timeout(30);
    }
}
