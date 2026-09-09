<?php

namespace App\SyncAdapters\Osctrl;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * osctrl adapter. Pulls osquery-enrolled node inventory from an osctrl
 * instance and normalizes it into HostInventoryRecord objects.
 *
 * osctrl installs can host multiple osquery environments (prod, dev,
 * staging, etc.). the Environment schema field pins this instance to
 * one of them. Set up a second sync-adapter instance per environment
 * when a single osctrl install serves multiple.
 */
class OsctrlAdapter extends ConfigurableAdapter
{
    public static function typeLabel(): string
    {
        return 'osctrl';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'token',
                'label' => 'API Token',
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.osctrl_token_help'),
            ],
            [
                'key' => 'environment',
                'label' => 'Environment',
                'help' => trans('admin/settings/sync_adapters.osctrl_environment_help'),
            ],
        ];
    }

    public function extraFields(): array
    {
        return [
            'osctrl_environment' => ['label_key' => 'admin/settings/sync_adapters.extra_environment'],
            'osctrl_osquery_version' => ['label_key' => 'admin/settings/sync_adapters.extra_osquery_version'],
            'osctrl_cpu_type' => ['label_key' => 'admin/settings/sync_adapters.extra_cpu_type'],
        ];
    }

    public function pull(): iterable
    {
        $client = new OsctrlClient(baseUrl: $this->url(), token: $this->credential('token'));

        foreach ($client->nodes($this->credential('environment')) as $node) {
            yield $this->normalize($node);
        }
    }

    /**
     * Convert an osctrl node payload into the normalized record shape.
     * osctrl's `uuid` is stable per enrolled node and is what we key
     * asset_external_sources on.
     *
     * @param  array<string, mixed>  $node
     */
    private function normalize(array $node): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($node, 'uuid'),
            hostname: Arr::get($node, 'hostname') ?? Arr::get($node, 'localname'),
            hardwareSerial: Arr::get($node, 'hardware_serial'),
            hardwareModel: Arr::get($node, 'hardware_model'),
            manufacturer: Arr::get($node, 'hardware_vendor'),
            primaryMac: null,
            primaryIp: Arr::get($node, 'ip_address'),
            os: Arr::get($node, 'platform'),
            osVersion: Arr::get($node, 'platform_version'),
            lastSeen: $this->parseTimestamp(Arr::get($node, 'last_seen')),
            extra: [
                'osctrl_environment' => Arr::get($node, 'environment'),
                'osctrl_osquery_version' => Arr::get($node, 'osquery_version'),
                'osctrl_cpu_type' => Arr::get($node, 'cpu_type'),
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
