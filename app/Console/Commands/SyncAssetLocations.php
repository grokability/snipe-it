<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Console\Command;

class SyncAssetLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:sync-asset-locations {--output= : info|warn|error|all} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This utility will sync the location_id of assets based on current state. It should not normally be needed, but is a safeguard in case we missed something in the Great Migration when flattening the assets to location relationship.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $outputLevel = $this->option('output');

        $total_assets = Asset::whereNull('deleted_at')->count();
        $bar = $this->output->createProgressBar($total_assets);

        // Unassigned
        $rtd_count = Asset::whereNull('assigned_to')->whereNull('deleted_at')->count();
        $this->writeInfo($outputLevel, 'There are '.$rtd_count.' unassigned assets.');

        Asset::whereNull('assigned_to')
            ->whereNull('deleted_at')
            ->with('defaultLoc')
            ->chunkById(500, function ($chunk) use ($bar, $outputLevel) {
                foreach ($chunk as $rtd_asset) {
                    $this->writeInfo($outputLevel, 'Setting Unassigned Asset '.$rtd_asset->id.' ('.$rtd_asset->asset_tag.') to  location: '.$rtd_asset->rtd_location_id.' because their default location is: '.$rtd_asset->rtd_location_id);
                    $rtd_asset->location_id = $rtd_asset->rtd_location_id;
                    $rtd_asset->unsetEventDispatcher();
                    $rtd_asset->save();
                    $bar->advance();
                }
            });

        $user_asset_count = Asset::where('assigned_type', User::class)
            ->whereNotNull('assigned_to')
            ->whereNull('deleted_at')
            ->count();
        $this->writeInfo($outputLevel, 'There are '.$user_asset_count.' assets checked out to users.');

        Asset::where('assigned_type', User::class)
            ->whereNotNull('assigned_to')
            ->whereNull('deleted_at')
            ->with('assignedTo.userLoc')
            ->chunkById(500, function ($chunk) use ($bar, $outputLevel) {
                foreach ($chunk as $assigned_user_asset) {
                    if (($assigned_user_asset->assignedTo) && ($assigned_user_asset->assignedTo->userLoc)) {
                        $new_location = $assigned_user_asset->assignedTo->userLoc->id;
                        $this->writeInfo($outputLevel, 'Setting User Asset '.$assigned_user_asset->id.' ('.$assigned_user_asset->asset_tag.') to  '.$assigned_user_asset->assignedTo->userLoc->name.' which is id: '.$new_location);
                    } else {
                        $this->writeWarn($outputLevel, 'Asset '.$assigned_user_asset->id.' ('.$assigned_user_asset->asset_tag.') still has no location! ');
                        $new_location = $assigned_user_asset->rtd_location_id;
                    }
                    $assigned_user_asset->location_id = $new_location;
                    $assigned_user_asset->unsetEventDispatcher();
                    $assigned_user_asset->save();
                    $bar->advance();
                }
            });

        $location_asset_count = Asset::where('assigned_type', Location::class)
            ->whereNotNull('assigned_to')
            ->whereNull('deleted_at')
            ->count();
        $this->writeInfo($outputLevel, 'There are '.$location_asset_count.' assets checked out to locations.');

        Asset::where('assigned_type', Location::class)
            ->whereNotNull('assigned_to')
            ->whereNull('deleted_at')
            ->with('assignedTo')
            ->chunkById(500, function ($chunk) use ($bar, $outputLevel) {
                foreach ($chunk as $assigned_location_asset) {
                    if ($assigned_location_asset->assignedTo) {
                        $assigned_location_asset->location_id = $assigned_location_asset->assignedTo->id;
                        $this->writeInfo($outputLevel, 'Setting Location Assigned  asset '.$assigned_location_asset->id.' ('.$assigned_location_asset->asset_tag.') that is checked out to '.$assigned_location_asset->assignedTo->name.' (#'.$assigned_location_asset->assignedTo->id.') to location: '.$assigned_location_asset->assetLoc()->id);
                        $assigned_location_asset->unsetEventDispatcher();
                        $assigned_location_asset->save();
                    } else {
                        $this->writeWarn($outputLevel, 'Asset '.$assigned_location_asset->id.' ('.$assigned_location_asset->asset_tag.') did not return a valid associated location - perhaps it was deleted?');
                    }
                    $bar->advance();
                }
            });

        $asset_asset_count = Asset::where('assigned_type', Asset::class)
            ->whereNotNull('assigned_to')
            ->whereNull('deleted_at')
            ->count();
        $this->writeInfo($outputLevel, 'Asset-assigned assets: '.$asset_asset_count);

        Asset::where('assigned_type', Asset::class)
            ->whereNotNull('assigned_to')
            ->whereNull('deleted_at')
            ->with('assignedTo')
            ->chunkById(500, function ($chunk) use ($bar, $outputLevel) {
                foreach ($chunk as $assigned_asset_asset) {
                    // Check to make sure there aren't any invalid relationships
                    if ($assigned_asset_asset->assetLoc()) {
                        $assigned_asset_asset->location_id = $assigned_asset_asset->assetLoc()->id;
                        $this->writeInfo($outputLevel, 'Setting Asset Assigned asset '.$assigned_asset_asset->assetLoc()->id.' ('.$assigned_asset_asset->asset_tag.') location to: '.$assigned_asset_asset->assetLoc()->id);
                        $assigned_asset_asset->unsetEventDispatcher();
                        $assigned_asset_asset->save();
                    } else {
                        $this->writeWarn($outputLevel, 'Asset Assigned asset '.$assigned_asset_asset->id.' ('.$assigned_asset_asset->asset_tag.') does not seem to have a valid location');
                    }
                    $bar->advance();
                }
            });

        $unlocated_count = Asset::whereNull('location_id')->whereNull('deleted_at')->count();
        $this->writeInfo($outputLevel, 'Assets still without a location: '.$unlocated_count);

        Asset::whereNull('location_id')
            ->whereNull('deleted_at')
            ->chunkById(500, function ($chunk) use ($bar, $outputLevel) {
                foreach ($chunk as $unlocated_asset) {
                    $this->writeWarn($outputLevel, 'Asset: '.$unlocated_asset->id.' still has no location. ');
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->info("\n");
    }

    private function writeInfo(?string $outputLevel, string $message): void
    {
        if ($outputLevel === 'all' || $outputLevel === 'info') {
            $this->info($message);
        }
    }

    private function writeWarn(?string $outputLevel, string $message): void
    {
        if ($outputLevel === 'all' || $outputLevel === 'warn') {
            $this->warn($message);
        }
    }
}
