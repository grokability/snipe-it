<?php

namespace App\SyncAdapters\Mosyle;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableAdapter;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Mosyle adapter. Pulls device inventory from a Mosyle tenant (either
 * Manager or Business) via the Mosyle API and normalizes it into
 * HostInventoryRecord objects. The configured base URL determines
 * which product the adapter targets. Also pushes Snipe-IT-
 * authoritative asset_tag back via Mosyle's serial-number-scoped
 * set_asset_tag operation.
 */
class MosyleAdapter extends ConfigurableAdapter implements PushableAdapter
{
    public static function typeLabel(): string
    {
        return 'Mosyle';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://managerapi.mosyle.com/v2';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'token',
                'label' => 'Access Token',
                'secret' => true,
                'help' => trans('admin/settings/general.sync_adapter_mosyle_token_help'),
            ],
        ];
    }

    public function extraFields(): array
    {
        return [
            'mosyle_user_id' => 'Mosyle User ID',
            'mosyle_supervised' => ['label' => 'Mosyle Supervised', 'type' => 'boolean'],
        ];
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return 'Mosyle Location';
    }

    public function fetchGroups(): array
    {
        $client = new MosyleClient(baseUrl: $this->url(), token: $this->credential('token'));

        return array_map(
            fn (array $location) => [
                'id' => (string) ($location['id'] ?? $location['locationid'] ?? ''),
                'label' => (string) ($location['name'] ?? $location['location_name'] ?? $location['id'] ?? '?'),
            ],
            $client->locations(),
        );
    }

    public function pull(): iterable
    {
        $client = new MosyleClient(baseUrl: $this->url(), token: $this->credential('token'));

        foreach ($client->devices() as $device) {
            yield $this->normalize($device);
        }
    }

    /**
     * Convert a Mosyle device payload into the normalized record shape.
     * Mosyle's `deviceudid` is stable per enrolled device and is what
     * we key asset_external_sources on.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'deviceudid'),
            hostname: Arr::get($device, 'device_name'),
            hardwareSerial: Arr::get($device, 'serial_number'),
            hardwareModel: Arr::get($device, 'device_model_name'),
            manufacturer: 'Apple',
            primaryMac: Arr::get($device, 'wifi_mac_address'),
            primaryIp: Arr::get($device, 'ip_address'),
            os: Arr::get($device, 'os'),
            osVersion: Arr::get($device, 'osversion'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'date_info')),
            assetTag: Arr::get($device, 'asset_tag'),
            assignedUserEmail: Arr::get($device, 'useremail'),
            assignedUserName: Arr::get($device, 'usename'),
            vendorGroupId: Arr::has($device, 'locationid') ? (string) Arr::get($device, 'locationid') : null,
            extra: [
                'mosyle_user_id' => Arr::get($device, 'userid'),
                'mosyle_supervised' => Arr::get($device, 'is_supervised'),
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

    /**
     * Mosyle writes are token-scoped. Same credential used for pull
     * works for push when the API token has the "Devices - Write"
     * permission. No tier / license gate, so canPush is always true
     * for a configured instance.
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Mosyle exposes a freeform notes field per device via the
     * `set_notes_by_serial_number` operation. Returning the field
     * name here signals to the push framework that composed notes
     * can flow to this vendor.
     */
    public function notesFieldTarget(): ?string
    {
        return 'notes';
    }

    /**
     * Push Snipe-IT asset_tag back to Mosyle. Mosyle's write API is
     * a single POST endpoint dispatched by an `operation` field.
     * `set_asset_tag_by_serial_number` targets the device by serial
     * (Mosyle also accepts UDID, but serial is what we cached from
     * pull and is more stable across re-enrollment).
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): void
    {
        $pushFields = $this->pushDirectedFields();
        if ($pushFields === []) {
            return;
        }

        if ($changedFields !== [] && array_intersect($changedFields, $pushFields) === []) {
            return;
        }

        $externalSource = AssetExternalSource::query()
            ->where('asset_id', $asset->id)
            ->where('source', $this->name())
            ->first();

        if ($externalSource === null) {
            return;
        }

        // Mosyle keys writes by serial number, not the UDID we
        // stored as external_id. Fall back to the external_id if
        // the asset has no serial (Mosyle Business uses serial ==
        // external_id for some device types).
        $serial = $asset->serial;
        if ($serial === null || $serial === '') {
            $serial = $externalSource->external_id;
        }

        $pushedFields = [];
        $client = null;

        foreach ($pushFields as $field) {
            if ($field !== 'asset_tag') {
                continue;
            }

            $value = $this->assetValueForSourceField($asset, $field);
            if ($value === null || $value === '') {
                continue;
            }

            if ($this->isPushDryRun()) {
                Log::channel('sync-adapters')->info(sprintf(
                    '%s push [dry-run]: would set Mosyle device serial=%s asset_tag=%s',
                    $this->name(),
                    $serial,
                    $value,
                ));
                $pushedFields[] = $field;

                continue;
            }

            $client ??= new MosyleClient(baseUrl: $this->url(), token: $this->credential('token'));
            $client->updateDeviceAssetTagBySerial($serial, (string) $value);
            $pushedFields[] = $field;
        }

        // Composed notes push. Independent from the asset_tag push
        // because Mosyle's write API dispatches per-op, so notes get
        // a second POST to /devices with the notes operation.
        $template = $this->pushNotesTemplate();
        if ($template !== '') {
            $composed = \App\SyncAdapters\Support\NotesComposer::compose($asset, $template);
            if ($composed !== '') {
                if ($this->isPushDryRun()) {
                    Log::channel('sync-adapters')->info(sprintf(
                        '%s push [dry-run]: would set Mosyle device serial=%s notes=%s',
                        $this->name(),
                        $serial,
                        $composed,
                    ));
                } else {
                    $client ??= new MosyleClient(baseUrl: $this->url(), token: $this->credential('token'));
                    $client->updateDeviceNotesBySerial($serial, $composed);
                }
                $pushedFields[] = 'notes';
            }
        }

        if ($pushedFields === []) {
            return;
        }

        if (! $this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push: updated Mosyle device serial=%s fields [%s]',
                $this->name(),
                $serial,
                implode(', ', $pushedFields),
            ));
        }
    }

    private function assetValueForSourceField(Asset $asset, string $field): mixed
    {
        return match ($field) {
            'asset_tag' => $asset->asset_tag,
            default => null,
        };
    }
}
