<?php

namespace App\Console\Commands;

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\License;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class RestoreDeletedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:restore-users {--start_date=} {--end_date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore users, and any associated assets and license checkouts.';

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
        $start_date = $this->option('start_date');
        $end_date = $this->option('end_date');
        $asset_totals = 0;
        $license_totals = 0;
        $user_count = 0;

        if (($start_date == '') || ($end_date == '')) {
            $this->info('ERROR: All fields are required.');

            return false;
        }

        $users = User::whereBetween('deleted_at', [$start_date, $end_date])->withTrashed()->get();
        $this->info('There are '.$users->count().' users deleted between '.$start_date.' and '.$end_date);
        $this->warn('Making a backup!');
        Artisan::call('backup:run');

        foreach ($users as $user) {
            $user_count++;
            $user_logs = Actionlog::where('target_id', $user->id)->where('target_type', User::class)
                ->where('action_type', 'checkout')->with('item')->get();

            $this->info($user_count.'. '.$user->username.' ('.$user->id.') was deleted at '.$user->deleted_at.' and has '.$user_logs->count().' checkouts associated.');

            foreach ($user_logs as $user_log) {
                $this->info('  * '.$user_log->item_type.': '.$user_log->item->name.' - item_id: '.$user_log->item_id);

                if ($user_log->item_type == Asset::class) {
                    $asset_totals++;

                    DB::table('assets')
                        ->where('id', $user_log->item_id)
                        ->update(['assigned_to' => $user->id, 'assigned_type'=> User::class]);

                    $this->info('      ** Asset '.$user_log->item->id.' ('.$user_log->item->asset_tag.') restored to user '.$user->id.'');
                } elseif ($user_log->item_type == License::class) {
                    $license_totals++;

                    $avail_seat = DB::table('license_seats')->where('license_id', '=', $user_log->item->id)
                        ->whereNull('assigned_to')->whereNull('asset_id')->whereBetween('updated_at', [$start_date, $end_date])->first();
                    if ($avail_seat) {
                        $this->info('      ** Allocating seat '.$avail_seat->id.' for this License');

                        DB::table('license_seats')
                            ->where('id', $avail_seat->id)
                            ->update(['assigned_to' => $user->id]);
                    } else {
                        $this->warn('ERROR: No available seats for '.$user_log->item->name);
                    }
                }
            }

            $this->warn('Restoring user '.$user->username.'!');
            $user->restore();
        }

        $this->info($asset_totals.' assets affected');
        $this->info($license_totals.' licenses affected');
    }
}
