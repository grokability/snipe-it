<?php

namespace App\SyncAdapters\JamfSchool;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Jamf School adapter. Pulls managed-device inventory via the Jamf
 * School (formerly ZuluDesk) API and normalizes it into
 * HostInventoryRecord objects. Distinct product from Jamf Pro: separate
 * API, separate auth model (HTTP Basic with Network ID + API Key), so
 * separate adapter.
 */
class JamfSchoolAdapter extends ConfigurableAdapter
{
    public static function typeLabel(): string
    {
        return 'Jamf School';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://your-subdomain.jamfcloud.com/api';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'network_id',
                'label' => 'Network ID',
                'help' => trans('admin/settings/general.sync_adapter_jamf_school_network_id_help'),
            ],
            [
                'key' => 'api_key',
                'label' => 'API Key',
                'secret' => true,
                'help' => trans('admin/settings/general.sync_adapter_jamf_school_api_key_help'),
            ],
        ];
    }

    public function extraFields(): array
    {
        return [
            'jamf_school_model_identifier' => 'Jamf School Model Identifier',
            'jamf_school_supervised' => ['label' => 'Jamf School Supervised', 'type' => 'boolean'],
            'jamf_school_location_id' => 'Jamf School Location ID',
        ];
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return 'Jamf School Location';
    }

    public function fetchGroups(): array
    {
        $client = new JamfSchoolClient(
            baseUrl: $this->url(),
            networkId: $this->credential('network_id'),
            apiKey: $this->credential('api_key'),
        );

        return array_map(
            fn (array $location) => [
                'id' => (string) ($location['id'] ?? ''),
                'label' => (string) ($location['name'] ?? $location['id'] ?? '?'),
            ],
            $client->locations(),
        );
    }

    public function pull(): iterable
    {
        $client = new JamfSchoolClient(
            baseUrl: $this->url(),
            networkId: $this->credential('network_id'),
            apiKey: $this->credential('api_key'),
        );

        foreach ($client->devices() as $device) {
            yield $this->normalize($device);
        }
    }

    /**
     * Convert a Jamf School device payload into the normalized record
     * shape. Jamf School's UDID is stable per enrolled device and is
     * what we key asset_external_sources on.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'UDID'),
            hostname: Arr::get($device, 'name'),
            hardwareSerial: Arr::get($device, 'serialNumber'),
            hardwareModel: Arr::get($device, 'model.name'),
            manufacturer: 'Apple',
            primaryMac: Arr::get($device, 'wifiMacAddress'),
            primaryIp: null,
            os: Arr::get($device, 'os'),
            osVersion: Arr::get($device, 'osVersion'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'lastCheckin')),
            assetTag: Arr::get($device, 'assetTag'),
            vendorGroupId: Arr::has($device, 'locationId') ? (string) Arr::get($device, 'locationId') : null,
            extra: [
                'jamf_school_model_identifier' => Arr::get($device, 'model.identifier'),
                'jamf_school_supervised' => Arr::get($device, 'isSupervised'),
                'jamf_school_location_id' => Arr::get($device, 'locationId'),
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
