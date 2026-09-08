<?php

namespace App\SyncAdapters\Addigy;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Addigy adapter. Pulls device inventory from the Addigy API and
 * normalizes it into HostInventoryRecord objects. Uses Addigy's
 * client-id + client-secret header pair (not bearer-token auth).
 */
class AddigyAdapter extends ConfigurableAdapter
{
    public static function typeLabel(): string
    {
        return 'Addigy';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://prod.addigy.com';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'key_id',
                'label' => 'Client ID',
                'help' => trans('admin/settings/general.sync_adapter_addigy_key_id_help'),
            ],
            [
                'key' => 'key_secret',
                'label' => 'Client Secret',
                'secret' => true,
                'help' => trans('admin/settings/general.sync_adapter_addigy_key_secret_help'),
            ],
        ];
    }

    public function extraFields(): array
    {
        return [
            'addigy_policy_id' => 'Addigy Policy ID',
            'addigy_supervised' => ['label' => 'Addigy Supervised', 'type' => 'boolean'],
            'addigy_agent_version' => 'Addigy Agent Version',
        ];
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return 'Addigy Policy';
    }

    public function fetchGroups(): array
    {
        $client = new AddigyClient(
            baseUrl: $this->url(),
            clientId: $this->credential('key_id'),
            clientSecret: $this->credential('key_secret'),
        );

        return array_map(
            fn (array $policy) => [
                'id' => (string) ($policy['policyId'] ?? $policy['id'] ?? ''),
                'label' => (string) ($policy['name'] ?? $policy['policyId'] ?? '?'),
            ],
            $client->policies(),
        );
    }

    public function pull(): iterable
    {
        $client = new AddigyClient(
            baseUrl: $this->url(),
            clientId: $this->credential('key_id'),
            clientSecret: $this->credential('key_secret'),
        );

        foreach ($client->devices() as $device) {
            yield $this->normalize($device);
        }
    }

    /**
     * Convert an Addigy device payload into the normalized record
     * shape. Addigy's `agentid` is stable per enrolled device and is
     * what we key asset_external_sources on.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'agentid'),
            hostname: Arr::get($device, 'name'),
            hardwareSerial: Arr::get($device, 'serial_number'),
            hardwareModel: Arr::get($device, 'hardware_model'),
            manufacturer: 'Apple',
            primaryMac: Arr::get($device, 'network_mac_address'),
            primaryIp: Arr::get($device, 'network_ip_address'),
            os: Arr::get($device, 'os_type'),
            osVersion: Arr::get($device, 'os_version'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'last_online')),
            // Addigy's "asset_tag" is the operator-assigned tag from
            // their inventory settings, distinct from the Snipe-IT one.
            assetTag: Arr::get($device, 'asset_tag'),
            assignedUserEmail: Arr::get($device, 'user_email'),
            vendorGroupId: Arr::has($device, 'policy_id') ? (string) Arr::get($device, 'policy_id') : null,
            extra: [
                'addigy_policy_id' => Arr::get($device, 'policy_id'),
                'addigy_supervised' => Arr::get($device, 'is_supervised'),
                'addigy_agent_version' => Arr::get($device, 'agent_version'),
            ],
        );
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
