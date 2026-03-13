<?php

namespace App\Console\Commands;

use App\Models\Actionlog;
use App\Models\Asset;
use Illuminate\Console\Command;

class FixMismatchedAssetsAndLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:fix-assets-and-logs {--dryrun : Run the sync process but don\'t update the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This script attempts to check the log table and check that the assets.assigned_to matches the last checkout.';

    /**
     * Is dry-run?
     *
     * @var bool
     */
    private $dryrun = false;

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
        if ($this->option('dryrun')) {
            $this->dryrun = true;
        }

        if ($this->dryrun) {
            $this->info('This is a DRY RUN - no changes will be saved.');
        }

        $mismatch_count = 0;
        $assets = Asset::whereNotNull('assigned_to')
            ->where('assigned_type', '=', \App\Models\User::class)
            ->orderBy('id', 'ASC')->get();
        foreach ($assets as $asset) {

            // get the last checkout of the asset
            if ($checkout_log = Actionlog::where('target_type', '=', \App\Models\User::class)
                ->where('action_type', '=', 'checkout')
                ->where('item_id', '=', $asset->id)
                ->orderBy('created_at', 'DESC')
                ->first()) {

                    // Now check for a subsequent checkin log - we want to ignore those
                if (! $checkin_log = Actionlog::where('target_type', '=', \App\Models\User::class)
                        ->where('action_type', '=', 'checkin from')
                        ->where('item_id', '=', $asset->id)
                        ->whereDate('created_at', '>', $checkout_log->created_at)
                        ->orderBy('created_at', 'DESC')
                        ->first()) {

                        //print_r($asset);
                    if ($checkout_log->target_id != $asset->assigned_to) {
                        $this->error('Log ID: '.$checkout_log->id.' -- Asset ID '.$checkout_log->item_id.' SHOULD BE checked out to User '.$checkout_log->target_id.' but its assigned_to is '.$asset->assigned_to);

                        if (! $this->dryrun) {
                            $asset->assigned_to = $checkout_log->target_id;
                            if ($asset->save()) {
                                $this->info('Asset record updated.');
                            } else {
                                $this->error('Error updating asset: '.$asset->getErrors());
                            }
                        }
                        $mismatch_count++;
                    }
                } else {
                    //$this->info('Asset ID '.$asset->id.': There is a checkin '.$checkin_log->created_at.' after this checkout '.$checkout_log->created_at);
                }
            }
        }
        $this->info($mismatch_count.' mismatched assets.');
    }
}
