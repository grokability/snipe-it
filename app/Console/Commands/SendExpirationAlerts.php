<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use App\Mail\ExpiringAssetsMail;
use App\Mail\ExpiringLicenseMail;
use App\Models\Asset;
use App\Models\License;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendExpirationAlerts extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'snipeit:expiring-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring warrantees and service agreements, and sends out an alert email.';

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
        $alert_interval = $settings->alert_interval;

        if (($settings->alert_email != '') && ($settings->alerts_enabled == 1)) {

            // Send a rollup to the admin, if settings dictate
            $recipients = collect(explode(',', $settings->alert_email))
                ->map(fn($item) => trim($item)) // Trim each email
                ->filter(fn($item) => !empty($item))
                ->all();
            // Expiring Assets
            $assets = Asset::getExpiringWarrantyOrEol($alert_interval);

            if ($assets->count() > 0) {

                Mail::to($recipients)->send(new ExpiringAssetsMail($assets, $alert_interval));

                $this->table(
                    ['ID', 'Tag', 'Model', 'Model Number', 'EOL', 'EOL Months', 'Warranty Expires', 'Warranty Months'],
                    $assets->map(fn($item) => ['ID' => $item->id, 'Tag' => $item->asset_tag, 'Model' => $item->model->name, 'Model Number' => $item->model->model_number, 'EOL' => $item->asset_eol_date, 'EOL Months' => $item->model->eol,  'Warranty Expires' => $item->warranty_expires,  'Warranty Months' => $item->warranty_months])
                );
            }

            // Expiring licenses
            $licenses = License::getExpiringLicenses($alert_interval);
            if ($licenses->count() > 0) {
                Mail::to($recipients)->send(new ExpiringLicenseMail($licenses, $alert_interval));

                $this->table(
                    ['ID', 'Name', 'Expires', 'Termination Date'],
                    $licenses->map(fn($item) => ['ID' => $item->id, 'Name' => $item->name, 'Expires' => $item->expiration_date, 'Termination Date' => $item->termination_date])
                );
            }

            // Send a message even if the count is 0
            $this->info(trans_choice('mail.assets_warrantee_alert', $assets->count(), ['count' => $assets->count(), 'threshold' => $alert_interval]));
            $this->info(trans_choice('mail.license_expiring_alert', $licenses->count(), ['count' => $licenses->count(), 'threshold' => $alert_interval]));



        } else {
            if ($settings->alert_email == '') {
                $this->error('Could not send email. No alert email configured in settings');
            } elseif (1 != $settings->alerts_enabled) {
                $this->info('Alerts are disabled in the settings. No mail will be sent');
            }
        }
    }
}
