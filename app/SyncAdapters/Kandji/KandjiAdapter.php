<?php

namespace App\SyncAdapters\Kandji;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableAdapter;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Kandji adapter. Pulls device inventory from a Kandji tenant via the
 * Kandji Enterprise API and normalizes it into HostInventoryRecord
 * objects. Also pushes Snipe-IT-authoritative fields back to Kandji
 * (asset_tag today) when the admin marks the mapping row as 'push'.
 */
class KandjiAdapter extends ConfigurableAdapter implements PushableAdapter
{
    public static function typeLabel(): string
    {
        return 'Kandji';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://your-subdomain.api.kandji.io';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'token',
                'label' => 'API Token',
                'secret' => true,
                'help' => trans('admin/settings/general.sync_adapter_kandji_token_help'),
            ],
        ];
    }

    public function extraFields(): array
    {
        return [
            'kandji_blueprint_id' => 'Kandji Blueprint ID',
            'kandji_mdm_enabled' => ['label' => 'Kandji MDM Enabled', 'type' => 'boolean'],
            'kandji_is_missing' => ['label' => 'Kandji Marked Missing', 'type' => 'boolean'],
        ];
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return 'Kandji Blueprint';
    }

    public function fetchGroups(): array
    {
        $client = new KandjiClient(baseUrl: $this->url(), token: $this->credential('token'));

        return array_map(
            fn (array $bp) => [
                'id' => (string) ($bp['id'] ?? ''),
                'label' => (string) ($bp['name'] ?? $bp['id'] ?? '?'),
            ],
            $client->blueprints(),
        );
    }

    public function pull(): iterable
    {
        $client = new KandjiClient(baseUrl: $this->url(), token: $this->credential('token'));

        foreach ($client->devices() as $device) {
            yield $this->normalize($device);
        }
    }

    /**
     * Convert a Kandji device payload into the normalized record shape.
     * Kandji's `device_id` is stable per enrolled device and is what we
     * key asset_external_sources on.
     *
     * The bulk /devices endpoint doesn't return network interfaces, so
     * primaryMac stays null here. A future enrichment pass could hit
     * /devices/{id}/details per record to fill it in, at the cost of
     * N+1 requests per sync run.
     *
     * @param  array<string, mixed>  $device
     */
    private function normalize(array $device): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'device_id'),
            hostname: Arr::get($device, 'device_name'),
            hardwareSerial: Arr::get($device, 'serial_number'),
            hardwareModel: Arr::get($device, 'model'),
            manufacturer: Arr::get($device, 'platform') === 'Mac' ? 'Apple' : null,
            primaryMac: null,
            primaryIp: null,
            os: Arr::get($device, 'platform'),
            osVersion: Arr::get($device, 'os_version'),
            lastSeen: $this->parseTimestamp(Arr::get($device, 'last_check_in')),
            assetTag: Arr::get($device, 'asset_tag'),
            assignedUserEmail: Arr::get($device, 'user.email'),
            assignedUserName: Arr::get($device, 'user.name'),
            vendorGroupId: Arr::has($device, 'assigned_blueprint_id') ? (string) Arr::get($device, 'assigned_blueprint_id') : null,
            extra: [
                'kandji_blueprint_id' => Arr::get($device, 'assigned_blueprint_id'),
                'kandji_mdm_enabled' => Arr::get($device, 'mdm_enabled'),
                'kandji_is_missing' => Arr::get($device, 'is_missing'),
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
     * Kandji's device API accepts writes on any Admin API token so
     * push is always available for a configured instance (no tier
     * gate the way Fleet's manual labels have).
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Kandji devices carry a single freeform `notes` field that
     * admins commonly repurpose for composed asset info from
     * Snipe-IT. When pushNotesTemplate() is set, we render it and
     * PATCH the notes field alongside any other push-directed
     * fields.
     */
    public function notesFieldTarget(): ?string
    {
        return 'notes';
    }

    /**
     * Push Snipe-IT-authoritative fields to the vendor. Filters the
     * asset's fields down to those marked 'push' in the mapping table
     * for this instance, serializes them into Kandji's field-name
     * shape, and PATCHes the device.
     *
     * Silently no-ops when the asset has never been synced from this
     * instance (no asset_external_sources row -> no vendor device id
     * to write against) or when nothing on the mapping is directed
     * 'push'.
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): void
    {
        $pushFields = $this->pushDirectedFields();
        if ($pushFields === []) {
            return;
        }

        // Optional early-exit: if the caller told us which source
        // fields changed and none of them are push-directed, skip
        // the API call entirely. Empty $changedFields = "push all
        // push-directed fields regardless".
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

        $payload = [];
        foreach ($pushFields as $field) {
            $mapped = self::sourceFieldToKandjiField($field);
            if ($mapped === null) {
                continue;
            }

            $value = $this->assetValueForSourceField($asset, $field);
            if ($value === null) {
                continue;
            }

            $payload[$mapped] = $value;
        }

        // Composed notes: template + effectiveNotesTarget (admin
        // override or adapter default) merge into the same payload
        // so admins can push both an asset_tag AND a composed notes
        // blob in a single API call.
        $template = $this->pushNotesTemplate();
        $notesTarget = $this->effectiveNotesTarget();
        if ($template !== '' && $notesTarget !== null) {
            $composed = \App\SyncAdapters\Support\NotesComposer::compose($asset, $template);
            if ($composed !== '') {
                $payload[$notesTarget] = $composed;
            }
        }

        if ($payload === []) {
            return;
        }

        // Dry-run: log the payload we WOULD send and return without
        // hitting Kandji. Lets admins verify their config end-to-end
        // (mapping resolution, direction, credential wiring, external_id
        // lookup) without a real API call.
        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push [dry-run]: would PATCH Kandji device %s with %s',
                $this->name(),
                $externalSource->external_id,
                json_encode($payload, JSON_UNESCAPED_SLASHES),
            ));

            return;
        }

        $client = new KandjiClient(baseUrl: $this->url(), token: $this->credential('token'));
        $client->updateDevice($externalSource->external_id, $payload);

        Log::channel('sync-adapters')->info(sprintf(
            '%s push: updated Kandji device %s with fields [%s]',
            $this->name(),
            $externalSource->external_id,
            implode(', ', array_keys($payload)),
        ));
    }

    /**
     * Map a source-field name (from MappingTargets::FIELDS or the
     * adapter's extraFields) to Kandji's device-endpoint form field
     * name. Returns null when there's no writable equivalent, which
     * excludes read-only vendor state like hostname (Kandji derives
     * it from the device itself, not admin metadata).
     */
    private static function sourceFieldToKandjiField(string $field): ?string
    {
        return match ($field) {
            'asset_tag' => 'asset_tag',
            default => null,
        };
    }

    /**
     * Resolve a Snipe-IT source of truth for a given source-field
     * name. Currently only asset_tag is push-supported for Kandji.
     * Additional fields (notes, assigned_to, etc.) add cases here.
     */
    private function assetValueForSourceField(Asset $asset, string $field): mixed
    {
        return match ($field) {
            'asset_tag' => $asset->asset_tag,
            default => null,
        };
    }
}
