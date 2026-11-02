<?php

namespace App\Services\AppleDb;

use App\Enums\FileStorage;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Lookup service for appledb.dev, a community-maintained catalog of
 * Apple hardware keyed by hardware-identifier strings ("Mac16,10",
 * "iPhone16,2", etc). Returns an AppleDbRecord with any-or-all of
 * image, marketing name, release date, and manufacturer-declared
 * obsolete date populated.
 *
 * Called from sync adapters (ABM today, more to follow) and from the
 * Models screen's bulk-enrich action. Not sync-coupled: anywhere a
 * productType string is available, this service can turn it into an
 * AppleDbRecord.
 *
 * Everything fails soft: upstream unavailability, unknown productType,
 * malformed JSON, image download failure, disk write failure all
 * return null (or null fields) and log a warning. Callers never
 * abort on a miss, enrichment is cosmetic + supplementary.
 *
 * appledb.dev API + CDN endpoints:
 *   - https://api.appledb.dev/device/{productType}.json
 *     Returns { imageKey: string, colors: [{key: string}], ... }
 *   - https://img.appledb.dev/device@main/{imageKey}/{colorKey}.png
 *     Returns raw PNG bytes.
 *
 * Storage: images land under public/uploads/models/ via the public
 * disk (Snipe-IT aliases the public disk to local_public which roots
 * at public/uploads/, so this is the same location where
 * admin-uploaded model images already live and where
 * AssetModel::getImageUrl reads from). Filenames are keyed by a hash
 * of productType + colorKey so re-fetches deduplicate and browser
 * caching stays effective.
 */
class AppleDbEnrichment
{
    private const INFO_HOST = 'https://api.appledb.dev';

    private const IMAGE_HOST = 'https://img.appledb.dev';

    private const REQUEST_TIMEOUT_SECONDS = 10;

    /**
     * Per-process cache of the raw AppleDB device info payload, keyed
     * by productType. Hit by both canonicalIdentity() (called from an
     * adapter's normalize to feed framework-level identity matching)
     * and lookup() (called later in pull() for image + date backfill),
     * so a 500-device pull sharing a handful of model identifiers makes
     * one HTTP info call per identifier rather than one per device per
     * phase.
     *
     * Null-valued entries are cached too: an identifier that returned
     * no catalog entry on first ask stays null for the rest of the
     * process, so a bad productType does not retry the network every
     * time it is encountered.
     *
     * @var array<string, array<string, mixed>|null>
     */
    private static array $deviceInfoCache = [];

    /**
     * Resolve a productType into an AppleDbRecord. Returns null when
     * the catalog has no entry for the identifier (or the fetch
     * fails outright). A non-null record may still have null fields:
     * the catalog may know the image but not the obsolete date, or
     * vice versa.
     *
     * $colorHint lets callers pass the vendor-reported device color
     * so we prefer the matching variant when appledb.dev lists
     * multiple colors. Nulls / non-matches fall back to the catalog's
     * first-listed color.
     */
    public static function lookup(string $productType, ?string $colorHint = null): ?AppleDbRecord
    {
        if ($productType === '') {
            return null;
        }

        $device = self::fetchDeviceInfoCached($productType);
        if ($device === null) {
            return null;
        }

        $imageFilename = self::resolveImageFilename($productType, $device, $colorHint);
        $marketingName = isset($device['name']) && is_string($device['name']) && $device['name'] !== ''
            ? $device['name']
            : null;

        return new AppleDbRecord(
            imageFilename: $imageFilename,
            marketingName: $marketingName,
            modelNumber: self::firstModelNumber($device['model'] ?? null),
            releaseDate: self::parseDate($device['released'] ?? null),
            discontinuedDate: self::parseDate($device['discontinued'] ?? null),
        );
    }

    /**
     * Non-image AppleDB metadata for a hardware identifier. Called
     * from each Apple-capable adapter's normalize() so the record
     * can carry identity hints into resolveModelIdByName AND feed
     * optional custom-field mapping on individual assets. No image
     * handling (that still runs through lookup() during the pull
     * enrichment pass).
     *
     * Returns null when the productType has no catalog entry (which
     * also caches the miss, so a bad identifier does not retry the
     * network on every device that shares it).
     */
    public static function canonicalIdentity(string $productType): ?AppleDbRecord
    {
        if ($productType === '') {
            return null;
        }

        $device = self::fetchDeviceInfoCached($productType);
        if ($device === null) {
            return null;
        }

        $marketingName = isset($device['name']) && is_string($device['name']) && $device['name'] !== ''
            ? $device['name']
            : null;

        return new AppleDbRecord(
            marketingName: $marketingName,
            modelNumber: self::firstModelNumber($device['model'] ?? null),
            releaseDate: self::parseDate($device['released'] ?? null),
            discontinuedDate: self::parseDate($device['discontinued'] ?? null),
        );
    }

    /**
     * AppleDB stores the Apple regulatory numbers as an array (one
     * entry per region variant, e.g. ['A2442'] or ['A2779', 'A2780',
     * 'A2881', 'A2882']). We take the first entry and let admins
     * override from the UI when a multi-region install needs a
     * specific variant.
     */
    private static function firstModelNumber(mixed $models): ?string
    {
        if (! is_array($models) || $models === []) {
            return null;
        }

        $first = $models[0] ?? null;

        return is_string($first) && $first !== '' ? $first : null;
    }

    private static function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Stable filename per (productType, colorKey) pair. Hash keeps
     * the filename short and safe regardless of what characters live
     * in the vendor strings. Exposed so tests + callers that already
     * know the color can predict the filename without re-deriving
     * the hash scheme.
     */
    public static function filenameFor(string $productType, string $colorKey): string
    {
        return 'appledb-'.substr(hash('sha1', $productType.'|'.$colorKey), 0, 16).'.png';
    }

    /**
     * @param  array<string, mixed>  $device
     */
    private static function resolveImageFilename(string $productType, array $device, ?string $colorHint): ?string
    {
        $imageKey = $device['imageKey'] ?? null;
        $colors = $device['colors'] ?? [];
        if (! is_string($imageKey) || $imageKey === '' || ! is_array($colors) || $colors === []) {
            Log::channel('sync-adapters')->info("appledb.dev entry for productType {$productType} is missing imageKey or colors, skipping image");

            return null;
        }

        $colorKey = self::pickColorKey($colors, $colorHint);
        if ($colorKey === null) {
            Log::channel('sync-adapters')->info("appledb.dev entry for productType {$productType} has no usable colorKey, skipping image");

            return null;
        }

        return self::downloadAndStore($productType, $imageKey, $colorKey);
    }

    /**
     * Reset the per-process device info cache. Tests reach for this
     * when they want adjacent Http::fake runs to start from scratch
     * (otherwise the first run's device info persists into the next
     * run's static state and later fakes get ignored).
     */
    public static function clearCache(): void
    {
        self::$deviceInfoCache = [];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function fetchDeviceInfoCached(string $productType): ?array
    {
        if (array_key_exists($productType, self::$deviceInfoCache)) {
            return self::$deviceInfoCache[$productType];
        }

        $device = self::fetchDeviceInfo($productType);
        self::$deviceInfoCache[$productType] = $device;

        return $device;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function fetchDeviceInfo(string $productType): ?array
    {
        try {
            $response = Http::withOptions(['allow_redirects' => false])
                ->timeout(self::REQUEST_TIMEOUT_SECONDS)
                ->acceptJson()
                ->get(self::INFO_HOST.'/device/'.rawurlencode($productType).'.json');
        } catch (ConnectionException|RequestException $e) {
            Log::channel('sync-adapters')->warning("appledb.dev device-info fetch failed for {$productType}: {$e->getMessage()}");

            return null;
        }

        if (! $response->successful()) {
            $status = $response->status();
            Log::channel('sync-adapters')->info("appledb.dev has no catalog entry for productType {$productType} (status {$status}), skipping image");

            return null;
        }

        $body = $response->json();

        return is_array($body) ? $body : null;
    }

    /**
     * Pick the colorKey that best matches the hinted color. Falls
     * back to the first-listed key when the hint is null / unknown.
     *
     * @param  array<int, array<string, mixed>>  $colors
     */
    private static function pickColorKey(array $colors, ?string $colorHint): ?string
    {
        if (is_string($colorHint) && $colorHint !== '') {
            $needle = strtolower($colorHint);
            foreach ($colors as $color) {
                $key = $color['key'] ?? null;
                if (is_string($key) && strtolower($key) === $needle) {
                    return $key;
                }
            }
        }

        $first = $colors[0]['key'] ?? null;

        return is_string($first) && $first !== '' ? $first : null;
    }

    /**
     * Download the PNG bytes from the CDN and write them to the
     * public disk under models/. Returns the filename portion so the
     * caller can assign to AssetModel::image.
     */
    private static function downloadAndStore(string $productType, string $imageKey, string $colorKey): ?string
    {
        $filename = self::filenameFor($productType, $colorKey);
        $relativePath = FileStorage::Models->publicPath().$filename;

        // Idempotent: if a prior pull already stored this image,
        // return the cached filename without re-downloading.
        if (Storage::disk('public')->exists($relativePath)) {
            return $filename;
        }

        try {
            $url = self::IMAGE_HOST.'/device@main/'.rawurlencode($imageKey).'/'.rawurlencode($colorKey).'.png';
            $response = Http::withOptions(['allow_redirects' => false])
                ->timeout(self::REQUEST_TIMEOUT_SECONDS)
                ->get($url);
        } catch (ConnectionException|RequestException $e) {
            Log::channel('sync-adapters')->warning("appledb.dev image download failed for {$productType} ({$colorKey}): {$e->getMessage()}");

            return null;
        }

        if (! $response->successful()) {
            $status = $response->status();
            Log::channel('sync-adapters')->info("appledb.dev image CDN returned {$status} for {$productType} ({$colorKey}), skipping image");

            return null;
        }

        $bytes = $response->body();
        if ($bytes === '') {
            Log::channel('sync-adapters')->info("appledb.dev image CDN returned empty body for {$productType} ({$colorKey}), skipping image");

            return null;
        }

        $ok = Storage::disk('public')->put($relativePath, $bytes);
        if (! $ok) {
            Log::channel('sync-adapters')->warning("appledb.dev image write failed for {$productType} ({$colorKey}): Storage::put returned false");

            return null;
        }

        Log::channel('sync-adapters')->info("appledb.dev image saved for productType {$productType} (color={$colorKey}, file={$filename})");

        return $filename;
    }
}
