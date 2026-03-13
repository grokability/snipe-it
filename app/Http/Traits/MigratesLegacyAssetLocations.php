<?php

namespace App\Http\Traits;

use App\Models\Asset;
use Illuminate\Support\Facades\Log;

trait MigratesLegacyAssetLocations
{
    /**
     * This is just meant to correct legacy issues where some user data would have 0
     * as a location ID, which isn't valid. Later versions of Snipe-IT have stricter validation
     * rules, so it's necessary to fix this for long-time users. It's kinda gross, but will help
     * people (and their data) in the long run
     * @param Asset $asset
     * @return void
     */
    private function migrateLegacyLocations(Asset $asset): void
    {
        if ($asset->rtd_location_id == '0') {
            Log::debug('Manually override the RTD location IDs');
            Log::debug('Original RTD Location ID: ' . $asset->rtd_location_id);
            $asset->rtd_location_id = '';
            Log::debug('New RTD Location ID: ' . $asset->rtd_location_id);
        }

        if ($asset->location_id == '0') {
            Log::debug('Manually override the location IDs');
            Log::debug('Original Location ID: ' . $asset->location_id);
            $asset->location_id = '';
            Log::debug('New Location ID: ' . $asset->location_id);
        }
    }
}
