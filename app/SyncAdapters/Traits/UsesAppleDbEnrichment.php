<?php

namespace App\SyncAdapters\Traits;

use App\Enums\FileStorage;
use App\Models\AssetModel;
use App\Models\SyncAdapterConfig;
use App\Services\AppleDb\AppleDbEnrichment;
use App\Services\AppleDb\AppleDbRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Shared AppleDB enrichment surface for Apple-capable sync adapters.
 * Provides the settings-schema entry for the per-instance opt-in
 * checkbox, the credential-read helper, and the common "find model,
 * look up appledb.dev, soft-write every empty field" flow that would
 * otherwise be duplicated across nine adapters.
 *
 * Adapters are responsible for extracting the AppleDB-shape hardware
 * identifier (e.g. "MacBookPro18,3") from their vendor payload. That
 * part cannot be shared because each vendor stores it differently.
 */
trait UsesAppleDbEnrichment
{
    /**
     * Settings-schema entry each adapter injects into its own
     * settingsSchema() output. One checkbox per adapter so admins
     * can enable the lookup per instance.
     *
     * @return array<string, mixed>
     */
    protected function appleDbSettingsField(): array
    {
        return [
            'key' => 'use_appledb_enrichment',
            'label' => trans('admin/settings/sync_adapters.label_use_appledb_enrichment'),
            'type' => 'checkbox',
            'required' => false,
            'help' => trans('admin/settings/sync_adapters.use_appledb_enrichment_help'),
        ];
    }

    /**
     * True when the admin has turned the enrichment toggle on for
     * this instance. Also honors ABM's legacy pull_model_images key
     * as a backward-compat read so existing installs keep enriching
     * without the admin re-visiting the settings page.
     */
    protected function appleDbEnrichmentEnabled(): bool
    {
        if ($this->credentialOrEmpty('use_appledb_enrichment') === '1') {
            return true;
        }

        return SyncAdapterConfig::get($this->instance->id, 'pull_model_images') === '1';
    }

    private function credentialOrEmpty(string $key): string
    {
        try {
            return $this->credential($key);
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Shared extra-field declarations each Apple-capable adapter
     * merges into its own extraFields() output. Exposing the AppleDB
     * values as extras lets admins map them to custom fields on the
     * asset (so a release-date column can sort assets in a report,
     * not just display on the model row). The underlying mfg_*
     * columns on `models` are still written by enrichment either
     * way, these mappings are additive.
     *
     * @return array<string, array{label?: string, label_key?: string, type?: string, admin_defined?: bool}|string>
     */
    protected function appleDbExtraFieldsDeclaration(): array
    {
        if (! $this->appleDbEnrichmentEnabled()) {
            return [];
        }

        return [
            'appledb_marketing_name' => ['label_key' => 'admin/settings/sync_adapters.extra_appledb_marketing_name'],
            'appledb_model_number' => ['label_key' => 'admin/settings/sync_adapters.extra_appledb_model_number'],
            'appledb_release_date' => ['label_key' => 'admin/settings/sync_adapters.extra_appledb_release_date', 'type' => 'date'],
            'appledb_discontinued_date' => ['label_key' => 'admin/settings/sync_adapters.extra_appledb_discontinued_date', 'type' => 'date'],
        ];
    }

    /**
     * Values the matching extras-mapping path consumes. Each adapter
     * calls this in normalize() and merges the result into the
     * record's extra array, so the framework can route the values to
     * whatever custom field (or native target) the admin picked.
     *
     * Reuses the per-process AppleDB device info cache that
     * canonicalIdentityFor also hits, so there is no additional
     * network call per device.
     *
     * The $modelName shortcut behaves the same as canonicalIdentityFor's: when a
     * fully-populated AssetModel row matches the name, no network
     * fetch is worth making. An admin adding a new AppleDB-to-custom-
     * field mapping later would need to clear one of the model's
     * mfg_* columns to trigger a refresh on the next sync.
     *
     * @return array<string, string|null>
     */
    protected function appleDbExtrasFor(?string $productType, ?string $modelName = null): array
    {
        $blank = [
            'appledb_marketing_name' => null,
            'appledb_model_number' => null,
            'appledb_release_date' => null,
            'appledb_discontinued_date' => null,
        ];

        if (! is_string($productType) || $productType === '' || ! $this->appleDbEnrichmentEnabled()) {
            return $blank;
        }

        if (is_string($modelName) && $modelName !== '') {
            $existing = AssetModel::query()->where('name', $modelName)->first();
            if ($existing !== null && $this->allAppleDbEnrichableFieldsSet($existing)) {
                return $blank;
            }
        }

        $record = AppleDbEnrichment::canonicalIdentity($productType);
        if ($record === null) {
            return $blank;
        }

        return [
            'appledb_marketing_name' => $record->marketingName,
            'appledb_model_number' => $record->modelNumber,
            'appledb_release_date' => $record->releaseDate?->toDateString(),
            'appledb_discontinued_date' => $record->discontinuedDate?->toDateString(),
        ];
    }

    /**
     * Resolve the AppleDB canonical marketing name + model number for
     * a hardware identifier. Called from each adapter's normalize()
     * so the shared HostInventoryRecord carries identity hints into
     * resolveModelIdByName. Returns null-null when the toggle is off,
     * the identifier is blank, or AppleDB has no catalog entry.
     *
     * Keyed on the AppleDB-shape identifier string
     * ("MacBookPro18,3", "iPhone16,2", etc). Each adapter is
     * responsible for finding that string in its own vendor payload
     * before calling here.
     *
     * When $modelName is supplied and an existing fully-populated
     * AssetModel row matches it, the appledb.dev info call is
     * skipped: the current adapter's name match will hit that row
     * directly, and the row's own mfg_marketing_name + model_number
     * already serve any future cross-adapter OR match.
     *
     * @return array{canonicalMarketingName: ?string, canonicalModelNumber: ?string}
     */
    protected function canonicalIdentityFor(?string $productType, ?string $modelName = null): array
    {
        $blank = [
            'canonicalMarketingName' => null,
            'canonicalModelNumber' => null,
        ];

        if (! is_string($productType) || $productType === '') {
            return $blank;
        }

        if (! $this->appleDbEnrichmentEnabled()) {
            return $blank;
        }

        if (is_string($modelName) && $modelName !== '') {
            $existing = AssetModel::query()->where('name', $modelName)->first();
            if ($existing !== null && $this->allAppleDbEnrichableFieldsSet($existing)) {
                return $blank;
            }
        }

        $record = AppleDbEnrichment::canonicalIdentity($productType);
        if ($record === null) {
            return $blank;
        }

        return [
            'canonicalMarketingName' => $record->marketingName,
            'canonicalModelNumber' => $record->modelNumber,
        ];
    }

    /**
     * Look up $productType on appledb.dev and soft-write every value
     * it returns to the AssetModel row named $modelName. $handled is
     * a per-pull dedupe map (modelName => true) so a 500-device pull
     * sharing one model only hits the network once.
     *
     * Soft-write semantics: a column is populated only if it is
     * currently empty. Admin edits and prior-sync values are never
     * clobbered. If the model row doesn't exist yet (first-pass sync
     * before the framework builds it), the image still downloads to
     * the cache so the next sync picks it up without a re-fetch.
     *
     * @param  array<string, bool>  $handled
     * @return array<string, bool>
     */
    protected function enrichModelFromAppleDb(
        string $modelName,
        string $productType,
        ?string $colorHint,
        array $handled,
    ): array {
        if ($modelName === '' || $productType === '') {
            return $handled;
        }

        if (array_key_exists($modelName, $handled)) {
            return $handled;
        }
        $handled[$modelName] = true;

        $existing = AssetModel::query()
            ->where('name', $modelName)
            ->first();

        if ($existing !== null && $this->allAppleDbEnrichableFieldsSet($existing)) {
            Log::channel('sync-adapters')->info("{$this->name()} enrichment skip for model \"{$modelName}\": image and all mfg_* fields are already populated");

            return $handled;
        }

        $enrichment = AppleDbEnrichment::lookup($productType, $colorHint);
        if ($enrichment === null) {
            Log::channel('sync-adapters')->info("{$this->name()} enrichment skip for model \"{$modelName}\" (productType={$productType}): appledb.dev returned no usable record");

            return $handled;
        }

        if ($existing === null) {
            Log::channel('sync-adapters')->info("{$this->name()} enrichment staged for model \"{$modelName}\" (productType={$productType}): AssetModel row not yet created, fields will be assigned on the next sync");

            return $handled;
        }

        $this->softWriteAppleDbEnrichment($existing, $enrichment, $modelName, $productType);

        return $handled;
    }

    private function allAppleDbEnrichableFieldsSet(AssetModel $model): bool
    {
        return $this->modelImagePresentOnDisk($model)
            && ! empty($model->mfg_marketing_name)
            && ! empty($model->model_number)
            && ! empty($model->mfg_release_date)
            && ! empty($model->mfg_discontinued_date);
    }

    /**
     * True when model.image is set AND the referenced file actually
     * exists on the public disk. Catches stale references left behind
     * by a prior tenant migration or an older release that wrote to a
     * disk the install no longer uses, so enrichment can repair the
     * model.image column instead of leaving a 404 image forever.
     */
    private function modelImagePresentOnDisk(AssetModel $model): bool
    {
        if (empty($model->image)) {
            return false;
        }

        return Storage::disk('public')->exists(FileStorage::Models->publicPath().$model->image);
    }

    private function softWriteAppleDbEnrichment(AssetModel $model, AppleDbRecord $enrichment, string $modelName, string $productType): void
    {
        $applied = [];

        if (! $this->modelImagePresentOnDisk($model) && $enrichment->imageFilename !== null) {
            $previous = $model->image;
            $model->image = $enrichment->imageFilename;
            $applied[] = $previous
                ? "image={$enrichment->imageFilename} (replaced stale {$previous})"
                : "image={$enrichment->imageFilename}";
        }

        if (empty($model->mfg_marketing_name) && $enrichment->marketingName !== null) {
            $model->mfg_marketing_name = $enrichment->marketingName;
            $applied[] = "mfg_marketing_name={$enrichment->marketingName}";
        }

        if (empty($model->model_number) && $enrichment->modelNumber !== null) {
            $model->model_number = $enrichment->modelNumber;
            $applied[] = "model_number={$enrichment->modelNumber}";
        }

        if (empty($model->mfg_release_date) && $enrichment->releaseDate !== null) {
            // AppleDbRecord carries CarbonImmutable, AssetModel's
            // ide-helper annotations declare the column as mutable
            // Carbon. Carbon::instance accepts any DateTimeInterface
            // and returns a mutable Carbon instance.
            $model->mfg_release_date = Carbon::instance($enrichment->releaseDate);
            $applied[] = 'mfg_release_date='.$enrichment->releaseDate->toDateString();
        }

        if (empty($model->mfg_discontinued_date) && $enrichment->discontinuedDate !== null) {
            $model->mfg_discontinued_date = Carbon::instance($enrichment->discontinuedDate);
            $applied[] = 'mfg_discontinued_date='.$enrichment->discontinuedDate->toDateString();
        }

        if ($applied === []) {
            Log::channel('sync-adapters')->info("{$this->name()} enrichment noop for model \"{$modelName}\" (productType={$productType}): appledb.dev returned data but every target field already has a value");

            return;
        }

        $model->save();

        $summary = implode(', ', $applied);
        Log::channel('sync-adapters')->info("{$this->name()} enrichment applied to model \"{$modelName}\" (productType={$productType}): {$summary}");
    }
}
