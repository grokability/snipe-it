<?php

namespace App\Console\Commands;

use App\Events\UserMerged;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MergeUsersByUsername extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:merge-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command allows you to merge the history of users. It looks for users without an email address as their username and merges them into the version that does have an email username.';

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
        // Get the list of users who have an email address as their username
        $users = User::where('username', 'LIKE', '%@%')->whereNull('deleted_at')->get();
        $this->info($users->count().' total non-deleted users whose usernames contain a @ symbol.');


        foreach ($users as $user) {
            $parts = explode('@', trim($user->username));
            $this->info('Checking against username '.trim($parts[0]).'.');


            $bad_users = User::where('username', '=', trim($parts[0]))
                ->whereNull('deleted_at')
                ->with('assets', 'manager', 'userlog', 'licenses', 'consumables', 'accessories', 'managedLocations','uploads', 'acceptances')
                ->get();



            foreach ($bad_users as $bad_user) {
                $this->info($bad_user->username.' ('.$bad_user->id.')  will be merged into '.$user->username.'  ('.$user->id.') ');

                // Walk the list of assets
                foreach ($bad_user->assets as $asset) {
                    $this->info('Updating asset '.$asset->asset_tag.' '.$asset->id.' to user '.$user->id);
                    $asset->assigned_to = $user->id;
                    if (! $asset->save()) {
                        $this->error('Could not update assigned_to field on asset '.$asset->asset_tag.' '.$asset->id.' to user '.$user->id);
                        $this->error('Error saving: '.$asset->getErrors());
                    }
                }

                // Walk the list of licenses
                foreach ($bad_user->licenses as $license) {
                    $this->info('Updating license '.$license->name.' '.$license->id.' to user '.$user->id);
                    $bad_user->licenses()->updateExistingPivot($license->id, ['assigned_to' => $user->id]);
                }

                // Walk the list of consumables
                foreach ($bad_user->consumables as $consumable) {
                    $this->info('Updating consumable '.$consumable->id.' to user '.$user->id);
                    $bad_user->consumables()->updateExistingPivot($consumable->id, ['assigned_to' => $user->id]);
                }

                // Walk the list of accessories
                foreach ($bad_user->accessories as $accessory) {
                    $this->info('Updating accessory '.$accessory->id.' to user '.$user->id);
                    $bad_user->accessories()->updateExistingPivot($accessory->id, ['assigned_to' => $user->id]);
                }

                // Walk the list of logs
                foreach ($bad_user->userlog as $log) {
                    $this->info('Updating action log record '.$log->id.' to user '.$user->id);
                    $log->target_id = $user->id;
                    $log->save();
                }

                // Update any manager IDs
                $this->info('Updating managed user records to user '.$user->id);
                User::where('manager_id', '=', $bad_user->id)->update(['manager_id' => $user->id]);

                // Update location manager IDs
                foreach ($bad_user->managedLocations as $managedLocation) {
                    $this->info('Updating managed location record '.$managedLocation->name.' to manager '.$user->id);
                    $managedLocation->manager_id = $user->id;
                    $managedLocation->save();
                }

                foreach ($bad_user->uploads as $upload) {
                    $this->info('Updating upload log record '.$upload->id.' to user '.$user->id);
                    $upload->item_id = $user->id;
                    $upload->save();
                }

                foreach ($bad_user->acceptances as $acceptance) {
                    $this->info('Updating acceptance log record '.$acceptance->id.' to user '.$user->id);
                    $acceptance->item_id = $user->id;
                    $acceptance->save();
                }

                // Mark the user as deleted
                $this->info('Marking the user as deleted');
                $bad_user->deleted_at = Carbon::now()->timestamp;
                $bad_user->save();

                event(new UserMerged($bad_user, $user, null));


            }
        }
    }
}
