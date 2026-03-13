<?php

namespace App\Console\Commands;

use App\Models\Asset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAssetCounters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:counter-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs checkedout, checked in, and requested counters for assets';

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
        $start = microtime(true);

        // We need the whole count of all assets in order to set up the progress bar
        $assets_count = Asset::withTrashed()->count();
        $bar = $this->output->createProgressBar($assets_count);

        $assets = Asset::withCount('checkins as checkins_count', 'checkouts as checkouts_count', 'userRequests as user_requests_count')
            ->withTrashed()->chunk(100, function ($assets) use ($bar) {

                if ($assets->count() > 0) {

                    foreach ($assets as $asset) {

                        $asset->checkin_counter = (int) $asset->checkins_count;
                        $asset->checkout_counter = (int) $asset->checkouts_count;
                        $asset->requests_counter = (int) $asset->user_requests_count;
                        $asset->unsetEventDispatcher();
                        $asset->save();
                        $bar->advance();

                        Log::debug('Asset: '.$asset->id.' has '.$asset->checkin_counter.' checkins, '.$asset->checkout_counter.' checkouts, and '.$asset->requests_counter.' requests');

                    }

            } else {
                $this->info('No assets to sync');
            }
        });
        

        $bar->finish();
        $time_elapsed_secs = microtime(true) - $start;
        $this->info("\nSync of ".$assets_count.' assets executed in '.$time_elapsed_secs.' seconds');


    }
}
