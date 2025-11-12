<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use App\Models\Recipients\AlertRecipient;
use App\Models\Setting;
use App\Notifications\InventoryAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendInventoryAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:inventory-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command checks for low inventory, and sends out an alert email.';

    /**
     * Create a new command instance.
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
        $settings = Setting::getSettings();

        if (($settings->alert_email != '') && ($settings->alerts_enabled == 1)) {
            $items = Helper::checkLowInventory();

            if (($items) && (count($items) > 0)) {
                $this->info(trans_choice('mail.low_inventory_alert', count($items)));
                // Send a rollup to the admin, if settings dictate
                $recipients = collect(explode(',', $settings->alert_email))->map(function ($item, $key) {
                    return new AlertRecipient($item);
                });

                Notification::send($recipients, new InventoryAlert($items, $settings->alert_threshold));
            } else {
                $this->info('No low inventory items found. No mail sent.');
            }
        } else {
            if ($settings->alert_email == '') {
                $this->error('Could not send email. No alert email configured in settings');
            } elseif (1 != $settings->alerts_enabled) {
                $this->info('Alerts are disabled in the settings. No mail will be sent');
            }
        }
    }
}
