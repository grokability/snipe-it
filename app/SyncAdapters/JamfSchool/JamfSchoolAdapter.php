<?php

namespace App\SyncAdapters\JamfSchool;

use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\SyncAdapter;
use App\SyncAdapters\Traits\UsesAppleDbEnrichment;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Jamf School adapter. Pulls managed-device inventory via the Jamf
 * School (formerly ZuluDesk) API and normalizes it into
 * HostInventoryRecord objects. Distinct product from Jamf Pro: separate
 * API, separate auth model (HTTP Basic with Network ID + API Key), so
 * separate adapter.
 */
class JamfSchoolAdapter extends SyncAdapter
{
    use UsesAppleDbEnrichment;

    public static function typeLabel(): string
    {
        return 'Jamf School';
    }

    public static function typeSlug(): string
    {
        return 'jamf_school';
    }

    public static function docsUrl(): ?string
    {
        return 'https://learn.jamf.com/r/en-US/jamf-school-documentation/Jamf_School_API';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://your-subdomain.jamfcloud.com/api';
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'network_id',
                'label' => trans('admin/settings/sync_adapters.label_network_id'),
                'help' => trans('admin/settings/sync_adapters.jamf_school_network_id_help'),
            ],
            [
                'key' => 'api_key',
                'label' => trans('admin/settings/sync_adapters.label_api_key'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.jamf_school_api_key_help'),
            ],
            $this->appleDbSettingsField(),
        ];
    }

    public function extraFields(): array
    {
        return [
            ...$this->appleDbExtraFieldsDeclaration(),
            'jamf_school_model_identifier' => ['label_key' => 'admin/settings/sync_adapters.extra_model_identifier'],
            'jamf_school_supervised' => ['label_key' => 'admin/settings/sync_adapters.extra_supervised', 'type' => 'boolean'],
            'jamf_school_location_id' => ['label_key' => 'admin/settings/sync_adapters.extra_location_id'],
            'jamf_school_device_type' => ['label_key' => 'admin/settings/sync_adapters.extra_device_type'],
        ];
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return trans('admin/settings/sync_adapters.vendor_group_jamf_school_location');
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

        $enrichmentEnabled = $this->appleDbEnrichmentEnabled();
        $enrichmentHandled = [];

        foreach ($client->devices() as $device) {
            $record = $this->normalize($device);
            if ($enrichmentEnabled) {
                $enrichmentHandled = $this->enrichFromAppleDb($record, $enrichmentHandled);
            }
            yield $record;
        }
    }

    /**
     * Pass the Jamf School record to the shared enrichment flow.
     * Jamf School exposes the AppleDB-shape identifier at
     * `model.identifier`; `model.name` is the marketing-ish string
     * admins see as the AssetModel name.
     *
     * @param  array<string, bool>  $handled
     * @return array<string, bool>
     */
    private function enrichFromAppleDb(HostInventoryRecord $record, array $handled): array
    {
        $modelName = $record->hardwareModel;
        if (! is_string($modelName) || $modelName === '') {
            return $handled;
        }

        $productType = $record->extra['jamf_school_model_identifier'] ?? null;
        if (! is_string($productType) || $productType === '') {
            return $handled;
        }

        return $this->enrichModelFromAppleDb($modelName, $productType, null, $handled);
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
        $productType = Arr::get($device, 'model.identifier');
        $modelName = Arr::get($device, 'model.name');
        $canonical = $this->canonicalIdentityFor($productType, $modelName);
        $appleDbExtras = $this->appleDbExtrasFor($productType, $modelName);

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'UDID'),
            hostname: Arr::get($device, 'name'),
            hardwareSerial: Arr::get($device, 'serialNumber'),
            hardwareModel: Arr::get($device, 'model.name'),
            manufacturer: 'Apple',
            // Jamf School nests network info under `networkInformation`
            // on every device class (confirmed on iPhone, iPad, and
            // Mac payloads via issue #19856). The top-level
            // `wifiMacAddress` key the earlier version read is never
            // present, so the field always landed null regardless of
            // device class.
            primaryMac: Arr::get($device, 'networkInformation.WiFiMAC'),
            primaryIp: Arr::get($device, 'networkInformation.IPAddress'),
            os: Arr::get($device, 'os.prefix'),
            osVersion: Arr::get($device, 'os.version'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'lastCheckin')),
            assetTag: Arr::get($device, 'assetTag'),
            // Jamf School nests the device's assigned user under
            // `owner`. Pipeline user-lookup matches on either email or
            // username per adapter config, so populate both when the
            // vendor emits them.
            assignedUserEmail: Arr::get($device, 'owner.email'),
            assignedUserName: Arr::get($device, 'owner.username'),
            vendorGroupId: Arr::has($device, 'locationId') ? (string) Arr::get($device, 'locationId') : null,
            extra: [
                ...$appleDbExtras,
                'jamf_school_model_identifier' => Arr::get($device, 'model.identifier'),
                'jamf_school_supervised' => Arr::get($device, 'isSupervised'),
                'jamf_school_location_id' => Arr::get($device, 'locationId'),
                'jamf_school_device_type' => Arr::get($device, 'deviceType'),
            ],
            canonicalMarketingName: $canonical['canonicalMarketingName'],
            canonicalModelNumber: $canonical['canonicalModelNumber'],
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
