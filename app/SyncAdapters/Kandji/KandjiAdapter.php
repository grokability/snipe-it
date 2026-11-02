<?php

namespace App\SyncAdapters\Kandji;

use App\Models\Asset;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableSyncAdapter;
use App\SyncAdapters\Traits\UsesAppleDbEnrichment;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Kandji adapter. Pulls device inventory from a Kandji tenant via the
 * Kandji Enterprise API and normalizes it into HostInventoryRecord
 * objects. Also pushes Snipe-IT-authoritative fields back to Kandji
 * (asset_tag today) when the admin marks the mapping row as 'push'.
 */
class KandjiAdapter extends PushableSyncAdapter
{
    use UsesAppleDbEnrichment;

    public static function typeLabel(): string
    {
        return 'Iru (Kandji)';
    }

    public static function typeSlug(): string
    {
        return 'kandji';
    }

    public static function docsUrl(): ?string
    {
        return 'https://api-docs.kandji.io/';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://your-subdomain.api.kandji.io';
    }

    public function baseUrlHelp(): ?string
    {
        return trans('admin/settings/sync_adapters.kandji_base_url_help');
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'token',
                'label' => trans('admin/settings/sync_adapters.label_api_token'),
                'secret' => true,
                'help' => trans('admin/settings/sync_adapters.kandji_token_help'),
            ],
            $this->appleDbSettingsField(),
        ];
    }

    public function extraFields(): array
    {
        return [
            ...$this->appleDbExtraFieldsDeclaration(),
            'kandji_blueprint_id' => ['label_key' => 'admin/settings/sync_adapters.extra_blueprint_id'],
            'kandji_mdm_enabled' => ['label_key' => 'admin/settings/sync_adapters.extra_mdm_enabled', 'type' => 'boolean'],
            'kandji_is_missing' => ['label_key' => 'admin/settings/sync_adapters.extra_marked_missing', 'type' => 'boolean'],
            'kandji_model_identifier' => ['label_key' => 'admin/settings/sync_adapters.extra_model_identifier'],
        ];
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return trans('admin/settings/sync_adapters.vendor_group_kandji_blueprint');
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

        $enrichmentEnabled = $this->appleDbEnrichmentEnabled();
        $enrichmentHandled = [];

        foreach ($client->devices() as $device) {
            // Kandji's list endpoint omits the AppleDB-shape hardware
            // identifier so an enrichment-enabled pull hits
            // /devices/{id}/details per record. Pays N+1 API cost for
            // the enrichment, which is why the toggle is opt-in.
            $identifier = null;
            if ($enrichmentEnabled && Arr::get($device, 'platform') !== null) {
                $details = $client->deviceDetails((string) Arr::get($device, 'device_id'));
                $identifier = $details !== null ? $this->extractKandjiIdentifier($details) : null;
            }

            $record = $this->normalize($device, $identifier);
            if ($enrichmentEnabled) {
                $enrichmentHandled = $this->enrichFromAppleDb($record, $enrichmentHandled);
            }
            yield $record;
        }
    }

    /**
     * Kandji's /devices/{id}/details response nests the AppleDB-
     * shape identifier under hardware_overview on Macs. iPads /
     * iPhones place the equivalent under a different key depending
     * on the model class, so walk the two most common locations and
     * accept whichever responds. Non-Apple devices (if Kandji ever
     * adds them) are already filtered out before this is called via
     * the enrichment toggle gate.
     *
     * @param  array<string, mixed>  $details
     */
    private function extractKandjiIdentifier(array $details): ?string
    {
        $identifier = Arr::get($details, 'hardware_overview.model_identifier')
            ?? Arr::get($details, 'general.model_identifier');

        return is_string($identifier) && $identifier !== '' ? $identifier : null;
    }

    /**
     * Pass the Kandji record to the shared enrichment flow. Uses
     * the normalized hardwareModel as the AssetModel name, same as
     * the list-endpoint behavior before enrichment landed. The
     * details-fetched identifier flows through the record extras.
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

        $productType = $record->extra['kandji_model_identifier'] ?? null;
        if (! is_string($productType) || $productType === '') {
            return $handled;
        }

        return $this->enrichModelFromAppleDb($modelName, $productType, null, $handled);
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
    private function normalize(array $device, ?string $modelIdentifier = null): HostInventoryRecord
    {
        $modelName = Arr::get($device, 'model');
        $canonical = $this->canonicalIdentityFor($modelIdentifier, $modelName);
        $appleDbExtras = $this->appleDbExtrasFor($modelIdentifier, $modelName);

        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($device, 'device_id'),
            hostname: Arr::get($device, 'device_name'),
            hardwareSerial: Arr::get($device, 'serial_number'),
            hardwareModel: $modelName,
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
                ...$appleDbExtras,
                'kandji_blueprint_id' => Arr::get($device, 'assigned_blueprint_id'),
                'kandji_mdm_enabled' => Arr::get($device, 'mdm_enabled'),
                'kandji_is_missing' => Arr::get($device, 'is_missing'),
                'kandji_model_identifier' => $modelIdentifier,
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
     * Snipe-IT. When the admin has set a push_notes_template we
     * render it via composeNotesForPush() and PATCH the notes field
     * alongside any other push-directed fields.
     */
    public function notesFieldTarget(): ?string
    {
        return 'notes';
    }

    /**
     * Push Snipe-IT-authoritative fields to Kandji. Delegates to the
     * base template, which runs the prologue guards, splices composed
     * notes, handles the dry-run branch, and writes the success log
     * line. Kandji provides the vendor-shape payload and the vendor
     * dispatch call via the two hooks below.
     *
     * @param  array<int, string>  $changedFields
     */
    public function push(Asset $asset, array $changedFields = []): bool
    {
        return $this->pushViaSinglePayload($asset, $changedFields);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<int, string>}
     */
    protected function buildPushPayload(Asset $asset): array
    {
        $payload = [];
        foreach ($this->pushDirectedFields() as $field) {
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

        return [$payload, []];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function dispatchPush(\App\Models\AssetExternalSource $externalSource, array $payload): void
    {
        $client = new KandjiClient(baseUrl: $this->url(), token: $this->credential('token'));
        $client->updateDevice($externalSource->external_id, $payload);
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
}
