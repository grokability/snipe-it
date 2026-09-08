<?php

namespace App\SyncAdapters\Zentral;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Zentral adapter. Pulls the aggregated inventory snapshot (merged
 * from every Zentral source module: Munki, Santa, MDM, Osquery, etc.)
 * and normalizes it into HostInventoryRecord objects.
 */
class ZentralAdapter extends ConfigurableAdapter
{
    public static function typeLabel(): string
    {
        return 'Zentral';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'token',
                'label' => 'API Token',
                'secret' => true,
                'help' => trans('admin/settings/general.sync_adapter_zentral_token_help'),
            ],
        ];
    }

    public function pull(): iterable
    {
        $client = new ZentralClient(baseUrl: $this->url(), token: $this->credential('token'));

        foreach ($client->machines() as $machine) {
            yield $this->normalize($machine);
        }
    }

    /**
     * Convert a Zentral machine snapshot into the normalized record
     * shape. Zentral keys machines on serial number (its primary
     * identifier across every source module), so that doubles as
     * external_id here.
     *
     * @param  array<string, mixed>  $machine
     */
    private function normalize(array $machine): HostInventoryRecord
    {
        $serial = Arr::get($machine, 'serial_number');

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) $serial,
            hostname: Arr::get($machine, 'computer_name') ?? Arr::get($machine, 'hostname'),
            hardwareSerial: $serial,
            hardwareModel: Arr::get($machine, 'system_info.hardware_model')
                ?? Arr::get($machine, 'hardware_model'),
            manufacturer: Arr::get($machine, 'system_info.hardware_vendor')
                ?? Arr::get($machine, 'hardware_vendor'),
            primaryMac: Arr::get($machine, 'network_interfaces.0.mac'),
            primaryIp: Arr::get($machine, 'network_interfaces.0.address'),
            os: Arr::get($machine, 'os_version.name') ?? Arr::get($machine, 'platform'),
            osVersion: Arr::get($machine, 'os_version.version')
                ?? Arr::get($machine, 'os_version'),
            lastSeen: $this->parseTimestamp(Arr::get($machine, 'last_seen')),
            extra: [
                'zentral_pk' => Arr::get($machine, 'id'),
                'zentral_source' => Arr::get($machine, 'source.name'),
                'zentral_cpu_type' => Arr::get($machine, 'system_info.cpu_type'),
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
