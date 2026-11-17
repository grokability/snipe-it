<?php

namespace App\SyncAdapters\Addigy;

use App\SyncAdapters\Traits\RetriesTransientHttpErrors;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Addigy API v2. Handles auth for the pull +
 * push endpoints the sync adapter uses. Yields raw decoded JSON, no
 * normalization (that's the adapter's job).
 *
 * Auth model: a single API key generated in the Addigy admin console
 * under Integrations -> Addigy API. Passed in the `x-api-key` header
 * on every request. Replaces v1's client-id + client-secret header
 * pair, which Addigy retired in June 2026.
 *
 * The devices listing endpoint is a paginated POST ("Universal Search
 * Devices"). We page by `page` + `per_page` and dig values out of each
 * record's `facts` map keyed by fact identifier. The adapter owns the
 * list of fact identifiers it wants. This client just hands them to
 * `desired_fact_identifiers` on the request body and streams the
 * decoded response pages back.
 *
 * Addigy API reference: https://api.addigy.com/api/v2/documentation/
 */
class AddigyClient
{
    use RetriesTransientHttpErrors;

    /**
     * Addigy v2 lives at a fixed host. The admin doesn't configure
     * the base URL on the adapter form (usesConfigurableUrl() = false),
     * so we hardcode it here rather than threading it through the
     * constructor from a non-existent credential.
     */
    private const BASE_URL = 'https://api.addigy.com/api/v2';

    /**
     * Default page size for the paginated POST /devices endpoint.
     * 100 matches what Addigy's console uses and keeps each page small
     * enough to decode without pressure on the PHP memory limit.
     */
    private const DEFAULT_PER_PAGE = 100;

    public function __construct(
        private readonly string $apiKey,
    ) {}

    /**
     * List every enrolled device in the Addigy tenant. Yields decoded
     * device records (DeviceAudit shape: `agentid`, `orgid`,
     * `audit_date`, `facts` keyed by identifier) one at a time so the
     * caller can normalize + persist each without buffering the whole
     * tenant.
     *
     * @param  list<string>  $desiredFactIdentifiers  fact names to request on every device
     * @return iterable<array<string, mixed>>
     */
    public function devices(array $desiredFactIdentifiers): iterable
    {
        $page = 1;

        while (true) {
            $response = $this->request()
                ->post('/devices', [
                    'desired_fact_identifiers' => $desiredFactIdentifiers,
                    'page' => $page,
                    'per_page' => self::DEFAULT_PER_PAGE,
                ])
                ->throw()
                ->json();

            $items = is_array($response['items'] ?? null) ? $response['items'] : [];

            if ($items === []) {
                return;
            }

            foreach ($items as $device) {
                yield $device;
            }

            // Metadata.page_count is total number of pages. Stop when
            // we've drained the last one. Fallback: if the server
            // doesn't echo page_count, break when a page comes back
            // with fewer items than we asked for.
            $pageCount = (int) ($response['metadata']['page_count'] ?? 0);
            if ($pageCount > 0 && $page >= $pageCount) {
                return;
            }
            if ($pageCount === 0 && count($items) < self::DEFAULT_PER_PAGE) {
                return;
            }

            $page++;
        }
    }

    /**
     * List every Policy in the Addigy tenant. Used by the adapter's
     * fetchGroups() so admins can map Policies to Snipe-IT companies.
     * Policies are Addigy's device-grouping concept. MSPs often use
     * them as customer proxies.
     *
     * v2 moved policy listing to a POST with a filter body. An empty
     * `policies` array in the body returns every policy.
     *
     * @return array<int, array<string, mixed>>
     */
    public function policies(): array
    {
        $response = $this->request()
            ->post('/oa/policies/query', ['policies' => []])
            ->throw()
            ->json();

        if (is_array($response['items'] ?? null)) {
            return $response['items'];
        }

        return is_array($response) ? $response : [];
    }

    /**
     * Rename an Addigy device via the Set Device Name MDM command.
     * Addigy's doc: "available only on supervised devices or devices
     * running macOS 10.10 or later". On macOS the command sets only
     * the computer name and local hostname. iOS/iPadOS requires
     * supervision. Addigy's response is an MDM command receipt, not a
     * synchronous device state change.
     */
    public function setDeviceName(string $agentId, string $deviceName): void
    {
        $this->request()
            ->post('/mdm/settings/device/name', [
                'agent_id' => $agentId,
                'device_name' => $deviceName,
            ])
            ->throw();
    }

    /**
     * Resolve an Addigy end-user by email. Addigy's end-user query
     * takes a free-form `search` string so an exact-email match isn't
     * guaranteed. We filter the paged response client-side to pick
     * only entries whose `emails[*].value` matches the queried email
     * exactly (case-insensitive), returning the single id when
     * exactly one survives and null for zero or multi-match. The
     * caller logs the skip reason.
     */
    public function findEndUserIdByEmail(string $organizationId, string $email): ?string
    {
        $response = $this->request()
            ->post('/o/'.$organizationId.'/end-users/query', [
                'page' => 1,
                'per_page' => 100,
                'query' => ['search' => $email],
            ])
            ->throw()
            ->json();

        $items = is_array($response['items'] ?? null) ? $response['items'] : [];

        $matches = [];
        foreach ($items as $item) {
            if (! is_array($item) || empty($item['id'])) {
                continue;
            }
            $emails = is_array($item['emails'] ?? null) ? $item['emails'] : [];
            foreach ($emails as $entry) {
                $value = is_array($entry) ? ($entry['value'] ?? null) : null;
                if (is_string($value) && strcasecmp($value, $email) === 0) {
                    $matches[] = (string) $item['id'];
                    break;
                }
            }
        }

        $unique = array_values(array_unique($matches));

        return count($unique) === 1 ? $unique[0] : null;
    }

    /**
     * Assign a device to an Addigy end user. Addigy requires both the
     * agent id (device) and the end-user id (resolved separately via
     * findEndUserIdByEmail).
     */
    public function assignDeviceToEndUser(string $organizationId, string $agentId, string $endUserId): void
    {
        $this->request()
            ->post('/o/'.$organizationId.'/end-users/devices', [
                'agent_id' => $agentId,
                'end_user_id' => $endUserId,
            ])
            ->throw();
    }

    private function request(): PendingRequest
    {
        return $this->withTransientRetry(
            Http::baseUrl(self::BASE_URL)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->acceptJson()
                ->asJson()
                ->timeout(30)
        );
    }
}
