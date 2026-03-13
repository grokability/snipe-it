<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class CheckoutLicenseToAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:checkout-to-all {--license_id=} {--notify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks out licenses to all users';

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

        if (! $license = License::where('id', '=', $license_id)->with('assignedusers')->first()) {
            $this->error('Invalid license ID');

            return false;
        }

        $users = User::whereNull('deleted_at')->where('autoassign_licenses', '=', 1)->with('licenses')->get();

        if ($users->count() > $license->getAvailSeatsCountAttribute()) {
            $this->info('You do not have enough free seats to complete this task, so we will check out as many as we can. ');
        }

        $this->info('Checking out '.$users->count().' of '.$license->getAvailSeatsCountAttribute().' seats for '.$license->name);

        if (! $notify) {
            $this->info('No mail will be sent.');
        }

        foreach ($users as $user) {

            // Check to make sure this user doesn't already have this license checked out
            // to them

            if ($user->licenses->where('id', '=', $license_id)->count()) {
                $this->info($user->username.' already has this license checked out to them. Skipping... ');
                continue;
            }

            // If the license is valid, check that there is an available seat
            if ($license->availCount()->count() < 1) {
                $this->error('ERROR: No available seats');

                return false;
            }

            $this->info($license->availCount()->count().' seats left');
            // Get the seat ID
            $licenseSeat = $license->freeSeat();

            // Update the seat with checkout info,
            $licenseSeat->assigned_to = $user->id;
            if ($licenseSeat->save()) {

                // Temporarily null the user's email address so we don't send mail if we're not supposed to
                if (! $notify) {
                    $user->email = null;
                }

                // Log the checkout
                $licenseSeat->logCheckout('Checked out via cli tool', $user);
                $this->info('License '.$license_id.' seat '.$licenseSeat->id.' checked out to '.$user->username);
            }
        }
    }
}
