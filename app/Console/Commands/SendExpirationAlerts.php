<?php

namespace App\Console\Commands;

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
            $assets = Asset::getExpiringWarrantee($alert_interval);

            if ($assets->count() > 0) {
                $this->info(trans_choice('mail.assets_warrantee_alert', $assets->count(), ['count' => $assets->count(), 'threshold' => $alert_interval]));
                Mail::to($recipients)->send(new ExpiringAssetsMail($assets, $alert_interval));
            }

            // Expiring licenses
            $licenses = License::getExpiringLicenses($alert_interval);
            if ($licenses->count() > 0) {
                $this->info(trans_choice('mail.license_expiring_alert', $licenses->count(), ['count' => $licenses->count(), 'threshold' => $alert_interval]));
                Mail::to($recipients)->send(new ExpiringLicenseMail($licenses, $alert_interval));
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
