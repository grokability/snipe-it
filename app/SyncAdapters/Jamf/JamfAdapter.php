<?php

namespace App\SyncAdapters\Jamf;

use App\Models\Asset;
use App\Models\AssetExternalSource;
use App\SyncAdapters\HostInventoryRecord;
use App\SyncAdapters\PushableAdapter;
use App\SyncAdapters\Support\ConfigurableAdapter;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Jamf Pro adapter. Pulls computer inventory via the Jamf Pro API and
 * normalizes it into HostInventoryRecord objects. Uses a Jamf-generated
 * Personal Access Token as a bearer credential. Also pushes
 * Snipe-IT-authoritative fields (asset_tag today) back to Jamf via
 * the /api/v1/computers-inventory-detail/{id} PATCH endpoint.
 */
class JamfAdapter extends ConfigurableAdapter implements PushableAdapter
{
    public static function typeLabel(): string
    {
        return 'Jamf Pro';
    }

    public function baseUrlPlaceholder(): ?string
    {
        return 'https://your-subdomain.jamfcloud.com';
    }

    public function credentialSchema(): array
    {
        return [
            [
                'key' => 'token',
                'label' => 'API Token',
                'secret' => true,
                'help' => trans('admin/settings/general.sync_adapter_jamf_token_help'),
            ],
        ];
    }

    public function extraFields(): array
    {
        return [
            'jamf_udid' => 'Jamf UDID',
            'jamf_last_enrolled' => 'Jamf Last Enrolled',
            'jamf_model_identifier' => 'Jamf Model Identifier',
        ];
    }

    public function supportsGroupScoping(): bool
    {
        return true;
    }

    public function vendorGroupLabel(): string
    {
        return 'Jamf Site';
    }

    public function fetchGroups(): array
    {
        $client = new JamfClient(baseUrl: $this->url(), token: $this->credential('token'));

        return array_map(
            fn (array $site) => [
                'id' => (string) ($site['id'] ?? ''),
                'label' => (string) ($site['name'] ?? $site['id'] ?? '?'),
            ],
            $client->sites(),
        );
    }

    public function pull(): iterable
    {
        $client = new JamfClient(baseUrl: $this->url(), token: $this->credential('token'));

        foreach ($client->computers() as $computer) {
            yield $this->normalize($computer);
        }
    }

    /**
     * Convert a Jamf computer payload into the normalized record shape.
     * Jamf's numeric `id` is stable per enrollment and is what we key
     * asset_external_sources on. Fields come from the GENERAL, HARDWARE, and
     * OPERATING_SYSTEM sections (the client only requests those).
     *
     * @param  array<string, mixed>  $computer
     */
    private function normalize(array $computer): HostInventoryRecord
    {
        return new HostInventoryRecord(
            sourceKey: $this->name(),
            sourceId: (string) Arr::get($computer, 'id'),
            hostname: Arr::get($computer, 'general.name'),
            hardwareSerial: Arr::get($computer, 'hardware.serialNumber'),
            hardwareModel: Arr::get($computer, 'hardware.model'),
            manufacturer: Arr::get($computer, 'hardware.make'),
            primaryMac: Arr::get($computer, 'hardware.macAddress'),
            primaryIp: null,
            os: Arr::get($computer, 'operatingSystem.name'),
            osVersion: Arr::get($computer, 'operatingSystem.version'),
            lastSeen: $this->parseTimestamp(Arr::get($computer, 'general.lastContactTime')),
            assetTag: Arr::get($computer, 'general.assetTag'),
            assignedUserEmail: Arr::get($computer, 'userAndLocation.email'),
            assignedUserName: Arr::get($computer, 'userAndLocation.username'),
            vendorGroupId: Arr::has($computer, 'general.site.id') ? (string) Arr::get($computer, 'general.site.id') : null,
            extra: [
                'jamf_udid' => Arr::get($computer, 'udid'),
                'jamf_last_enrolled' => Arr::get($computer, 'general.lastEnrolledDate'),
                'jamf_model_identifier' => Arr::get($computer, 'hardware.modelIdentifier'),
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
     * Jamf Pro writes are gated by the PAT's API role scopes. Both
     * pull + push work with the same token when the role grants the
     * "Update Computers" privilege. No tier / license gate, so canPush
     * is always true for a configured instance.
     */
    public function canPush(): bool
    {
        return true;
    }

    /**
     * Jamf Pro's computer record has a freeform notes field at
     * general.notes on the inventory-detail JSON. Dotted path so
     * Arr::set can slot the composed value into the right nested
     * object.
     */
    public function notesFieldTarget(): ?string
    {
        return 'general.notes';
    }

    /**
     * Push Snipe-IT-authoritative fields to Jamf Pro via the newer
     * JSON detail endpoint. The payload uses Jamf's nested-object
     * shape (assetTag lives inside userAndLocation, not at the top
     * level), so sourceFieldToJamfPath() returns dotted paths that
     * get assembled via Arr::set before the PATCH.
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

        $payload = [];
        $touched = [];
        foreach ($pushFields as $field) {
            $path = self::sourceFieldToJamfPath($field);
            if ($path === null) {
                continue;
            }

            $value = $this->assetValueForSourceField($asset, $field);
            if ($value === null) {
                continue;
            }

            Arr::set($payload, $path, $value);
            $touched[] = $path;
        }

        $template = $this->pushNotesTemplate();
        $notesTarget = $this->effectiveNotesTarget();
        if ($template !== '' && $notesTarget !== null) {
            $composed = \App\SyncAdapters\Support\NotesComposer::compose($asset, $template);
            if ($composed !== '') {
                Arr::set($payload, $notesTarget, $composed);
                $touched[] = $notesTarget;
            }
        }

        if ($payload === []) {
            return;
        }

        if ($this->isPushDryRun()) {
            Log::channel('sync-adapters')->info(sprintf(
                '%s push [dry-run]: would PATCH Jamf computer %s with %s',
                $this->name(),
                $externalSource->external_id,
                json_encode($payload, JSON_UNESCAPED_SLASHES),
            ));

            return;
        }

        $client = new JamfClient(baseUrl: $this->url(), token: $this->credential('token'));
        $client->updateComputerDetail($externalSource->external_id, $payload);

        Log::channel('sync-adapters')->info(sprintf(
            '%s push: updated Jamf computer %s at paths [%s]',
            $this->name(),
            $externalSource->external_id,
            implode(', ', $touched),
        ));
    }

    /**
     * Map a source-field name to its dotted path in Jamf's
     * computers-inventory-detail JSON. Return null for fields Jamf
     * doesn't expose as writable (hostname is derived from the device
     * itself, not admin-settable).
     */
    private static function sourceFieldToJamfPath(string $field): ?string
    {
        return match ($field) {
            'asset_tag' => 'userAndLocation.assetTag',
            default => null,
        };
    }

    private function assetValueForSourceField(Asset $asset, string $field): mixed
    {
        return match ($field) {
            'asset_tag' => $asset->asset_tag,
            default => null,
        };
    }
}
