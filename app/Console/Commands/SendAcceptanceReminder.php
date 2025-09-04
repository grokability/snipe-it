<?php

namespace App\Console\Commands;

use App\Mail\UnacceptedAssetReminderMail;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CheckoutAssetNotification;
use App\Notifications\CurrentInventory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAcceptanceReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:acceptance-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will resend users with unaccepted assets a reminder to accept or decline them.';

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
        $pending = CheckoutAcceptance::pending()->where('checkoutable_type', 'App\Models\Asset')
                                                ->whereHas('checkoutable', function($query) {
                                                    $query->where('accepted_at', null)
                                                          ->where('declined_at', null);
                                                })
                                                ->with(['assignedTo', 'checkoutable.assignedTo', 'checkoutable.model', 'checkoutable.adminuser'])
                                                ->get();

        $count = 0;
        $unacceptedAssetGroups = $pending
            ->filter(function($acceptance) {
                return $acceptance->checkoutable_type == 'App\Models\Asset';
            })
            ->map(function($acceptance) {
                return ['assetItem' => $acceptance->checkoutable, 'acceptance' => $acceptance];
            })
            ->groupBy(function($item) {
                return $item['acceptance']->assignedTo ? $item['acceptance']->assignedTo->id : '';
            });
            $no_email_list= [];

        foreach($unacceptedAssetGroups as $unacceptedAssetGroup) {
            // The [0] is weird, but it allows for the item_count to work and grabs the appropriate info for each user.
            // Collapsing and flattening the collection doesn't work above.
            $acceptance = $unacceptedAssetGroup[0]['acceptance'];

            $locale = $acceptance->assignedTo?->locale;
            $email = $acceptance->assignedTo?->email;

            if(!$email){
                $no_email_list[] = [
                    'id' => $acceptance->assignedTo?->id,
                    'name' => $acceptance->assignedTo?->display_name,
                ];
            } else {
                $count++;
            }
            $item_count = $unacceptedAssetGroup->count();

            if ($locale && $email) {
                Mail::to($email)->send((new UnacceptedAssetReminderMail($acceptance, $item_count))->locale($locale));
            } elseif ($email) {
                Mail::to($email)->send((new UnacceptedAssetReminderMail($acceptance, $item_count)));
            }

        }

        $this->info($count.' users notified.');
        $headers = ['ID', 'Name'];
        $rows = [];

        foreach ($no_email_list as $user) {
            $rows[] = [$user['id'], $user['name']];
        }

        if (!empty($rows)) {
            $this->info("The following users do not have an email address:");
            $this->table($headers, $rows);
        }

        return 0;
    }

}
