<?php

namespace App\SyncAdapters\AppleBusinessManager;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Fetches product images from appledb.dev, a community-maintained
 * catalog of Apple hardware keyed by hardware-identifier strings
 * ("Mac16,10", "iPhone16,2", etc). Used exclusively by
 * AppleBusinessManagerAdapter when the "Pull model images" toggle is
 * on and an incoming device carries a productType.
 *
 * Everything here fails soft: upstream unavailability, unknown
 * productType, malformed JSON, image download failure, disk write
 * failure all return null and log a warning. Sync never aborts on an
 * image miss because images are cosmetic. The asset record still
 * carries value without one.
 *
 * appledb.dev API + CDN endpoints:
 *   - https://api.appledb.dev/device/{productType}.json
 *     Returns { imageKey: string, colors: [{key: string}] }
 *   - https://img.appledb.dev/device@main/{imageKey}/{colorKey}.png
 *     Returns raw PNG bytes.
 *
 * Storage: images land under public/uploads/models/ via the public
 * disk (Snipe-IT aliases the public disk to local_public which
 * roots at public/uploads/, so this is the same location where
 * admin-uploaded model images already live and where
 * AssetModel::getImageUrl reads from). Filenames are keyed by a
 * hash of productType + colorKey so re-fetches deduplicate and
 * browser caching stays effective.
 */
class AppleDBImageFetcher
{
    private const INFO_HOST = 'https://api.appledb.dev';

    private const IMAGE_HOST = 'https://img.appledb.dev';

    private const REQUEST_TIMEOUT_SECONDS = 10;

    /**
     * Fetch the image for a given productType and return the filename
     * stored under the models upload path. Returns null on any
     * failure (network, unknown productType, disk write). Caller
     * assigns the returned filename to AssetModel::image before save.
     *
     * $colorHint lets ABM pass through the device's vendor-reported
     * Color attribute so we prefer the matching variant when
     * appledb.dev lists multiple colors. Nulls / non-matches fall
     * back to the catalog's first-listed color.
     */
    public static function fetch(string $productType, ?string $colorHint = null): ?string
    {
        if ($productType === '') {
            return null;
        }

        $device = self::fetchDeviceInfo($productType);
        if ($device === null) {
            return null;
        }

        $imageKey = $device['imageKey'] ?? null;
        $colors = $device['colors'] ?? [];
        if (! is_string($imageKey) || $imageKey === '' || ! is_array($colors) || $colors === []) {
            Log::channel('sync-adapters')->info(sprintf(
                'appledb.dev entry for productType %s is missing imageKey or colors; skipping image',
                $productType,
            ));

            return null;
        }

        $colorKey = self::pickColorKey($colors, $colorHint);
        if ($colorKey === null) {
            Log::channel('sync-adapters')->info(sprintf(
                'appledb.dev entry for productType %s has no usable colorKey; skipping image',
                $productType,
            ));

            return null;
        }

        return self::downloadAndStore($productType, $imageKey, $colorKey);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function fetchDeviceInfo(string $productType): ?array
    {
        try {
            $response = Http::timeout(self::REQUEST_TIMEOUT_SECONDS)
                ->acceptJson()
                ->get(self::INFO_HOST.'/device/'.rawurlencode($productType).'.json');
        } catch (ConnectionException|RequestException $e) {
            Log::channel('sync-adapters')->warning(sprintf(
                'appledb.dev device-info fetch failed for %s: %s',
                $productType,
                $e->getMessage(),
            ));

            return null;
        }

        if (! $response->successful()) {
            Log::channel('sync-adapters')->info(sprintf(
                'appledb.dev has no catalog entry for productType %s (status %d); skipping image',
                $productType,
                $response->status(),
            ));

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
        $relativePath = app('models_upload_path').$filename;

        // Idempotent: if a prior pull already stored this image,
        // return the cached filename without re-downloading.
        if (Storage::disk('public')->exists($relativePath)) {
            return $filename;
        }

        try {
            $response = Http::timeout(self::REQUEST_TIMEOUT_SECONDS)
                ->get(sprintf('%s/device@main/%s/%s.png', self::IMAGE_HOST, rawurlencode($imageKey), rawurlencode($colorKey)));
        } catch (ConnectionException|RequestException $e) {
            Log::channel('sync-adapters')->warning(sprintf(
                'appledb.dev image download failed for %s (%s): %s',
                $productType,
                $colorKey,
                $e->getMessage(),
            ));

            return null;
        }

        if (! $response->successful()) {
            Log::channel('sync-adapters')->info(sprintf(
                'appledb.dev image CDN returned %d for %s (%s); skipping image',
                $response->status(),
                $productType,
                $colorKey,
            ));

            return null;
        }

        $bytes = $response->body();
        if ($bytes === '') {
            Log::channel('sync-adapters')->info(sprintf(
                'appledb.dev image CDN returned empty body for %s (%s); skipping image',
                $productType,
                $colorKey,
            ));

            return null;
        }

        $ok = Storage::disk('public')->put($relativePath, $bytes);
        if (! $ok) {
            Log::channel('sync-adapters')->warning(sprintf(
                'appledb.dev image write failed for %s (%s): Storage::put returned false',
                $productType,
                $colorKey,
            ));

            return null;
        }

        Log::channel('sync-adapters')->info(sprintf(
            'appledb.dev image saved for productType %s (color=%s, file=%s)',
            $productType,
            $colorKey,
            $filename,
        ));

        return $filename;
    }

    /**
     * Stable filename per (productType, colorKey) pair. Hash keeps
     * the filename short and safe regardless of what characters live
     * in the vendor strings.
     */
    private static function filenameFor(string $productType, string $colorKey): string
    {
        return 'abm-'.substr(hash('sha1', $productType.'|'.$colorKey), 0, 16).'.png';
    }
}
