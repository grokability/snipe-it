<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class CheckinLicensesFromAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:checkin-from-all {--license_id=} {--notify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks in licenses from all users';

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
        $license_id = $this->option('license_id');
        $notify = $this->option('notify');

        if (! $license_id) {
            $this->error('ERROR: License ID is required.');

            return false;
        }

        if (! $license = License::where('id', '=', $license_id)->first()) {
            $this->error('Invalid license ID');

            return false;
        }

        $this->info('Checking in ALL seats for '.$license->name);

        $licenseSeats = LicenseSeat::where('license_id', '=', $license_id)
            ->whereNotNull('assigned_to')
            ->with('user')
            ->get();

        $this->info(' There are '.$licenseSeats->count().' seats checked out: ');

        if (! $notify) {
            $this->info('No mail will be sent.');
        }

        foreach ($licenseSeats as $seat) {
            $this->info($seat->user->username.' has a license seat for '.$license->name);
            $seat->assigned_to = null;

            if ($seat->save()) {

                // Override the email address so we don't notify on checkin
                if (! $notify) {
                    $seat->user->email = null;
                }

                // Log the checkin
                $seat->logCheckin($seat->user, 'Checked in via cli tool');
            }
        }
    }
}
