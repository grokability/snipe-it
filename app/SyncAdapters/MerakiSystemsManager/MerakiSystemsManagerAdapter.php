<?php

namespace App\SyncAdapters\MerakiSystemsManager;

use App\SyncAdapters\Support\ConfigurableAdapter;

/**
 * Cisco Meraki Systems Manager adapter. STUB, registration + settings-
 * page presence exist so the shared /admin/adapters page renders the
 * tab. The pull() against the Meraki Dashboard API
 * (https://api.meraki.com/api/v1/organizations/{orgId}/sm/devices)
 * with an X-Cisco-Meraki-API-Key header is a follow-up.
 *
 * Meraki's API is org-scoped, so a real pull() needs to walk from the
 * API key's accessible organizations down through each org's Systems
 * Manager networks to the device list. The organization ID lives as
 * schema config rather than being discovered per-sync so admins can
 * pin a specific org when the account owns several.
 */
class MerakiSystemsManagerAdapter extends ConfigurableAdapter
{
    public static function typeLabel(): string
    {
        return 'Meraki Systems Manager';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://api.meraki.com/api/v1';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'api_key',
                'label' => 'API Key',
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.meraki_sm_api_key_help'),
            ],
            [
                'key' => 'organization_id',
                'label' => 'Organization ID',
                'help' => trans('admin/settings/sync_adapters.meraki_sm_organization_id_help'),
            ],
        ];
    }

    public function pull(): iterable
    {
        $client = new MerakiClient(
            baseUrl: $this->url(),
            apiKey: $this->credential('api_key'),
            organizationId: $this->credential('organization_id'),
        );

        foreach ($client->devices() as $device) {
            yield $this->normalize($device);
        }
    }

    /**
     * Convert a Meraki SM device payload into the normalized record
     * shape. Meraki's `id` is stable per enrolled device and is what
     * we key asset_external_sources on. Network id is stuffed on
     * every device by the client so we can carry it into extras.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): \App\SyncAdapters\HostInventoryRecord
    {
        return new \App\SyncAdapters\HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) \Illuminate\Support\Arr::get($device, 'id'),
            hostname: \Illuminate\Support\Arr::get($device, 'name')
                ?? \Illuminate\Support\Arr::get($device, 'systemModel'),
            hardwareSerial: \Illuminate\Support\Arr::get($device, 'serialNumber'),
            hardwareModel: \Illuminate\Support\Arr::get($device, 'systemModel'),
            manufacturer: \Illuminate\Support\Arr::get($device, 'manufacturer'),
            primaryMac: \Illuminate\Support\Arr::get($device, 'wifiMac'),
            primaryIp: \Illuminate\Support\Arr::get($device, 'ip'),
            os: \Illuminate\Support\Arr::get($device, 'osName'),
            osVersion: \Illuminate\Support\Arr::get($device, 'systemType'),
            lastSeen: $this->parseTimestamp(\Illuminate\Support\Arr::get($device, 'lastConnectAt')),
            extra: [
                'meraki_network_id' => \Illuminate\Support\Arr::get($device, '_networkId'),
                'meraki_tags' => \Illuminate\Support\Arr::get($device, 'tags'),
                'meraki_user' => \Illuminate\Support\Arr::get($device, 'ownerUsername'),
            ],
        );
    }

    private function parseTimestamp(mixed $value): ?\Carbon\Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return \Carbon\Carbon::parse($value);
    }
}
