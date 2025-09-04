<?php

namespace App\Console\Commands;

use App\Models\CheckoutAcceptance;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Console\Command;

class CleanIncorrectCheckoutAcceptances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:clean-checkout-acceptances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Delete checkout acceptances for checkouts to non-users";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deletions = 0;
        $skips = 0;

        // This walks *every* checkoutacceptance. That's gnarly. But necessary
        $this->withProgressBar(CheckoutAcceptance::all(), function ($checkoutAcceptance) use (&$deletions, &$skips) {
            $item = $checkoutAcceptance->checkoutable;
            $checkout_to_id = $checkoutAcceptance->assigned_to_id;
            if(is_null($item)) {
                $this->info("'Checkoutable' Item is null, going to next record");
                return; //'false' allegedly breaks execution entirely, so 'true' maybe doesn't? hrm. just straight return maybe?
            }
            if(get_class($item) == LicenseSeat::class) {
                $item = $item->license;
            }
            foreach($item->assetlog()->where('action_type','checkout')->get() as $assetlog) {
                if ($assetlog->target_id == $checkout_to_id && $assetlog->target_type != User::class) {
                    //We have a checkout-to an ID for a non-User, which matches to an ID in the checkout_acceptances table

                    //now, let's compare the _times_ - are they close?
                    //I'm picking `created_at` over `action_date` because I'm more interested in when the actionlogs
                    //were _created_, not when they were alleged to have happened - those created_at times need to be within 'X' seconds of
                    //each other (currently 5)
                    if ($assetlog->created_at->diffInSeconds($checkoutAcceptance->created_at, true) <= 5) { //we're allowing for five _ish_ seconds of slop
                        $deletions++;
                        $checkoutAcceptance->forceDelete(); // HARD delete this record; it should have never been
                        return;
                    } else {
                        //$this->info("The two records are too far apart");
                    }
                } else {
                    //$this->info("No match! checkout to id: " . $checkout_to_id." target_id: ".$assetlog->target_id." target_type: ".$assetlog->target_type);
                }
            }
            $skips++;
        });
        $this->error("Final deletion count: $deletions, and skip count: $skips");
    }
}
