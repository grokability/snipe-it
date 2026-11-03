<?php

namespace App\SyncAdapters\JumpCloud;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncAdapter;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * JumpCloud adapter. Pulls managed-system inventory via the JumpCloud
 * Systems API and normalizes it into HostInventoryRecord objects.
 * Auth uses the tenant's static API key in the x-api-key header, not
 * a bearer token.
 */
class JumpCloudAdapter extends SyncAdapter
{
    public static function typeLabel(): string
    {
        return 'JumpCloud';
    }

    public static function typeSlug(): string
    {
        return 'jumpcloud';
    }

    public static function docsUrl(): ?string
    {
        return 'https://docs.jumpcloud.com/api/2.0/index.html';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://console.jumpcloud.com/api';
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'token',
                'label' => trans('admin/settings/sync_adapters.label_api_key'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.jumpcloud_token_help'),
            ],
        ];
    }

    public function extraFields(): array
    {
        return [
            'jumpcloud_arch' => ['label_key' => 'admin/settings/sync_adapters.extra_architecture'],
            'jumpcloud_os_family' => ['label_key' => 'admin/settings/sync_adapters.extra_os_family'],
            'jumpcloud_agent_version' => ['label_key' => 'admin/settings/sync_adapters.extra_agent_version'],
            'jumpcloud_active' => ['label_key' => 'admin/settings/sync_adapters.extra_active', 'type' => 'boolean'],
        ];
    }

    public function pull(): iterable
    {
        $client = new JumpCloudClient(baseUrl: $this->url(), apiKey: $this->credential('token'));

        // V2 System Insights carries the authoritative hardware_model
        // and hardware_vendor strings for every OS. V1 /systems only
        // has `systemType` (free-form, often null for non-Mac) and no
        // vendor. Prefetch once, join on system_id while iterating V1.
        //
        // System Insights is included on every paid JumpCloud plan,
        // but a scoped API key without the right permission bits can
        // still 403. A 403 (or any transport error) here is not fatal,
        // we fall back to V1-only normalization and the record keeps
        // the same way it worked before this adapter added V2 support.
        $insightsBySystemId = $this->fetchSystemInsights($client);

        foreach ($client->systems() as $system) {
            $systemId = (string) Arr::get($system, '_id');
            $insight = $insightsBySystemId[$systemId] ?? null;
            yield $this->normalize($system, $insight);
        }
    }

    /**
     * Load every V2 System Insights `system_info` record into a map
     * keyed by system_id. Returns an empty array when the API key is
     * missing System Insights permission (403) or when the endpoint
     * fails for any other reason. The adapter then falls back to V1
     * fields on every record.
     *
     * @return array<string, array<string, mixed>>
     */
    private function fetchSystemInsights(JumpCloudClient $client): array
    {
        $map = [];

        try {
            foreach ($client->systemInsights() as $insight) {
                $systemId = Arr::get($insight, 'system_id');
                if (is_string($systemId) && $systemId !== '') {
                    $map[$systemId] = $insight;
                }
            }
        } catch (RequestException $e) {
            // 403 is the signal that this tenant's plan does not
            // include System Insights, which is a design-expected
            // outcome, not a configuration bug. Lower the log level
            // and keep going. Other 4xx/5xx get warning-level since
            // they likely need operator attention.
            $status = $e->response->status();
            if ($status === 403) {
                Log::channel('sync-adapters')->info(
                    $this->name().': API key does not have System Insights permission (403), falling back to V1 /systems fields only.',
                );

                return $map;
            }

            Log::channel('sync-adapters')->warning(
                $this->name().': V2 System Insights fetch failed (HTTP '.$status.'), falling back to V1 /systems fields only.',
                ['exception' => $e->getMessage()],
            );
        }

        return $map;
    }

    /**
     * Convert a JumpCloud system payload into the normalized record
     * shape. JumpCloud's `_id` is stable per enrolled system and is
     * what we key asset_external_sources on.
     *
     * When a V2 System Insights record is available for the same
     * system_id, prefer its hardware_model and hardware_vendor since
     * those are the fields JumpCloud actually populates for every OS.
     * V1 systemType / the darwin => Apple guess are the fallbacks.
     *
     * The bulk /systems endpoint doesn't return network interfaces, so
     * primaryMac stays null. A future enrichment pass could hit
     * /systems/{id}/networkinterfaces per record to fill it in, at the
     * cost of N+1 requests per sync run.
     *
     * @param  array<string, mixed>  $system
     * @param  array<string, mixed>|null  $insight
     */
    private function normalize(array $system, ?array $insight = null): HostInventoryRecord
    {
        $insightModel = $insight !== null ? Arr::get($insight, 'hardware_model') : null;
        $insightVendor = $insight !== null ? Arr::get($insight, 'hardware_vendor') : null;

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($system, '_id'),
            hostname: Arr::get($system, 'hostname') ?? Arr::get($system, 'displayName'),
            hardwareSerial: Arr::get($system, 'serialNumber'),
            hardwareModel: $this->firstNonEmptyString($insightModel, Arr::get($system, 'systemType')),
            manufacturer: $this->firstNonEmptyString($insightVendor, $this->guessManufacturer(Arr::get($system, 'osFamily'))),
            primaryMac: null,
            primaryIp: null,
            os: Arr::get($system, 'os'),
            osVersion: Arr::get($system, 'version'),
            lastSeen: $this->parseTimestamp(Arr::get($system, 'lastContact')),
            extra: [
                'jumpcloud_arch' => Arr::get($system, 'arch'),
                'jumpcloud_os_family' => Arr::get($system, 'osFamily'),
                'jumpcloud_agent_version' => Arr::get($system, 'agentVersion'),
                'jumpcloud_active' => Arr::get($system, 'active'),
            ],
        );
    }

    /**
     * Pick the first non-empty string from the arguments, or null when
     * none qualify. Lets normalize() express "prefer V2, fall back to
     * V1" without triggering on empty strings that V2 sometimes emits
     * for fields the agent could not collect.
     */
    private function firstNonEmptyString(mixed ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * JumpCloud V1 doesn't return a manufacturer field. Infer from
     * osFamily so downstream mapping still gets a reasonable value
     * when the V2 hardware_vendor fallback also isn't available.
     * darwin => Apple, windows / linux stay unknown because the vendor
     * varies per hardware.
     */
    private function guessManufacturer(?string $osFamily): ?string
    {
        return $osFamily === 'darwin' ? 'Apple' : null;
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
