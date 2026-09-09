<?php

namespace App\SyncAdapters\JumpCloud;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * JumpCloud adapter. Pulls managed-system inventory via the JumpCloud
 * Systems API and normalizes it into HostInventoryRecord objects.
 * Auth uses the tenant's static API key in the x-api-key header, not
 * a bearer token.
 */
class JumpCloudAdapter extends ConfigurableAdapter
{
    public static function typeLabel(): string
    {
        return 'JumpCloud';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://console.jumpcloud.com/api';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'token',
                'label' => 'API Key',
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

        foreach ($client->systems() as $system) {
            yield $this->normalize($system);
        }
    }

    /**
     * Convert a JumpCloud system payload into the normalized record
     * shape. JumpCloud's `_id` is stable per enrolled system and is
     * what we key asset_external_sources on.
     *
     * The bulk /systems endpoint doesn't return network interfaces, so
     * primaryMac stays null. A future enrichment pass could hit
     * /systems/{id}/networkinterfaces per record to fill it in, at the
     * cost of N+1 requests per sync run.
     *
     * @param  array<string, mixed>  $system
     */
    private function normalize(array $system): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($system, '_id'),
            hostname: Arr::get($system, 'hostname') ?? Arr::get($system, 'displayName'),
            hardwareSerial: Arr::get($system, 'serialNumber'),
            hardwareModel: Arr::get($system, 'systemType'),
            manufacturer: $this->guessManufacturer(Arr::get($system, 'osFamily')),
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
     * JumpCloud doesn't return a manufacturer field. Infer from
     * osFamily so downstream mapping still gets a reasonable value.
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
