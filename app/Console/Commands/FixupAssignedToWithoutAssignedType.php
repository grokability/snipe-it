<?php

namespace App\Console\Commands;

use App\Models\Asset;
use Illuminate\Console\Command;

class FixupAssignedToWithoutAssignedType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:assigned-to-fixup
                            {--debug : Display debugging output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes up assets that have an assigned_to but no assigned_type';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assets = Asset::whereNull("assigned_type")->whereNotNull("assigned_to")->withTrashed();
        $this->withProgressBar($assets->get(), function (Asset $asset) {
            //now check each action log, from the most recent backwards, to find the last checkin or checkout
            foreach($asset->log()->orderBy("id","desc")->get() as $action_log) {
                if($this->option("debug")) {
                    $this->info("Asset id: " . $asset->id . " action log, action type is: " . $action_log->action_type);
                }
                switch($action_log->action_type) {
                    case 'checkin from':
                        if($this->option("debug")) {
                            $this->info("Doing a checkin for ".$asset->id);
                        }
                        $asset->assigned_to = null;
                        // if you have a required custom field, we still want to save, and we *don't* want an action_log
                        $asset->saveQuietly();
                        return;

                    case 'checkout':
                        if($this->option("debug")) {
                            $this->info("Doing a checkout for " . $asset->id . " picking target type: " . $action_log->target_type);
                        }
                        if($asset->assigned_to != $action_log->target_id) {
                            $this->error("Asset's assigned_to does *NOT* match Action Log's target_id. \$asset->assigned_to=".$asset->assigned_to." vs. \$action_log->target_id=".$action_log->target_id);
                            //FIXME - do we abort here? Do we try to keep looking? I don't know, this means your data is *really* messed up...
                        }
                        $asset->assigned_type = $action_log->target_type;
                        $asset->saveQuietly(); // see above
                        return;
                }
            }
            $asset->assigned_to = null; //asset was never checked in or out in its lifetime - it stays 'checked in'
            $asset->saveQuietly(); //see above
        });
        $this->newLine();
        $this->info("Assets assigned_type are fixed");
    }
}
