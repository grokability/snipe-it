<?php

namespace App\Console\Commands;

use App\Models\Asset;
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
        $output['info'] = [];
        $output['warn'] = [];
        $output['error'] = [];

        $total_assets = Asset::whereNull('deleted_at')->get();
        $bar = $this->output->createProgressBar(count($total_assets));

        // Unassigned
        $rtd_assets = Asset::whereNull('assigned_to')->whereNull('deleted_at')->with('defaultLoc')->get();
        $output['info'][] = 'There are '.$rtd_assets->count().' unassigned assets.';

        foreach ($rtd_assets as $rtd_asset) {
            $output['info'][] = 'Setting Unassigned Asset '.$rtd_asset->id.' ('.$rtd_asset->asset_tag.') to  location: '.$rtd_asset->rtd_location_id.' because their default location is: '.$rtd_asset->rtd_location_id;
            $rtd_asset->location_id = $rtd_asset->rtd_location_id;
            $rtd_asset->unsetEventDispatcher();
            $rtd_asset->save();
            $bar->advance();
        }

        $assigned_user_assets = Asset::where('assigned_type', \App\Models\User::class)->whereNotNull('assigned_to')->whereNull('deleted_at')->get();
        $output['info'][] = 'There are '.$assigned_user_assets->count().' assets checked out to users.';
        foreach ($assigned_user_assets as $assigned_user_asset) {
            if (($assigned_user_asset->assignedTo) && ($assigned_user_asset->assignedTo->userLoc)) {
                $new_location = $assigned_user_asset->assignedTo->userLoc->id;
                $output['info'][] = 'Setting User Asset '.$assigned_user_asset->id.' ('.$assigned_user_asset->asset_tag.') to  '.$assigned_user_asset->assignedTo->userLoc->name.' which is id: '.$new_location;
            } else {
                $output['warn'][] = 'Asset '.$assigned_user_asset->id.' ('.$assigned_user_asset->asset_tag.') still has no location! ';
                $new_location = $assigned_user_asset->rtd_location_id;
            }
            $assigned_user_asset->location_id = $new_location;
            $assigned_user_asset->unsetEventDispatcher();
            $assigned_user_asset->save();
            $bar->advance();
        }

        $assigned_location_assets = Asset::where('assigned_type', \App\Models\Location::class)
            ->whereNotNull('assigned_to')->whereNull('deleted_at')->get();
        $output['info'][] = 'There are '.$assigned_location_assets->count().' assets checked out to locations.';

        foreach ($assigned_location_assets as $assigned_location_asset) {
            if ($assigned_location_asset->assignedTo) {
                $assigned_location_asset->location_id = $assigned_location_asset->assignedTo->id;
                $output['info'][] = 'Setting Location Assigned  asset '.$assigned_location_asset->id.' ('.$assigned_location_asset->asset_tag.') that is checked out to '.$assigned_location_asset->assignedTo->name.' (#'.$assigned_location_asset->assignedTo->id.') to location: '.$assigned_location_asset->assetLoc()->id;
                $assigned_location_asset->unsetEventDispatcher();
                $assigned_location_asset->save();
            } else {
                $output['warn'][] = 'Asset '.$assigned_location_asset->id.' ('.$assigned_location_asset->asset_tag.') did not return a valid associated location - perhaps it was deleted?';
            }
            $bar->advance();
        }

        // Assigned to assets
        $assigned_asset_assets = Asset::where('assigned_type', \App\Models\Asset::class)
            ->whereNotNull('assigned_to')->whereNull('deleted_at')->get();
        $output['info'][] = 'Asset-assigned assets: '.$assigned_asset_assets->count();

        foreach ($assigned_asset_assets as $assigned_asset_asset) {

                // Check to make sure there aren't any invalid relationships
            if ($assigned_asset_asset->assetLoc()) {
                $assigned_asset_asset->location_id = $assigned_asset_asset->assetLoc()->id;
                $output['info'][] = 'Setting Asset Assigned asset '.$assigned_asset_asset->assetLoc()->id.' ('.$assigned_asset_asset->asset_tag.') location to: '.$assigned_asset_asset->assetLoc()->id;
                $assigned_asset_asset->unsetEventDispatcher();
                $assigned_asset_asset->save();
            } else {
                $output['warn'][] = 'Asset Assigned asset '.$assigned_asset_asset->id.' ('.$assigned_asset_asset->asset_tag.') does not seem to have a valid location';
            }

            $bar->advance();
        }

        $unlocated_assets = Asset::whereNull('location_id')->whereNull('deleted_at')->get();
        $output['info'][] = 'Assets still without a location: '.$unlocated_assets->count();
        foreach ($unlocated_assets as $unlocated_asset) {
            $output['warn'][] = 'Asset: '.$unlocated_asset->id.' still has no location. ';
            $bar->advance();
        }

        $bar->finish();
        $this->info("\n");

        if (($this->option('output') == 'all') || ($this->option('output') == 'info')) {
            foreach ($output['info'] as $key => $output_text) {
                $this->info($output_text);
            }
        }
        if (($this->option('output') == 'all') || ($this->option('output') == 'warn')) {
            foreach ($output['warn'] as $key => $output_text) {
                $this->warn($output_text);
            }
        }
        if (($this->option('output') == 'all') || ($this->option('output') == 'error')) {
            foreach ($output['error'] as $key => $output_text) {
                $this->error($output_text);
            }
        }
    }
}
