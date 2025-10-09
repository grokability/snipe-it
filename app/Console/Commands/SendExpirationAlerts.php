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
                    [
                        trans('general.id'),
                        trans('admin/hardware/form.tag'),
                        trans('admin/hardware/form.model'),
                        trans('general.model_no'),
                        trans('general.purchase_date'),
                        trans('admin/hardware/form.eol_rate'),
                        trans('admin/hardware/form.eol_date'),
                        trans('admin/hardware/form.warranty_expires'),
                    ],
                    $assets->map(fn($item) =>
                    [
                        trans('general.id')  => $item->id,
                        trans('admin/hardware/form.tag') => $item->asset_tag,
                        trans('admin/hardware/form.model') => $item->model->name,
                        trans('general.model_no') => $item->model->model_number,
                        trans('general.purchase_date') => $item->purchase_date_formatted,
                        trans('admin/hardware/form.eol_rate')  => $item->model->eol,
                        trans('admin/hardware/form.eol_date') => $item->eol_date ? $item->eol_formatted_date .' ('.$item->eol_diff_for_humans.')' : '',
                        trans('admin/hardware/form.warranty_expires') => $item->warranty_expires ? $item->warranty_expires_formatted_date .' ('.$item->warranty_expires_diff_for_humans.')' : '',
                   ])
                   );
            }

            // Expiring licenses
            $licenses = License::query()->ExpiringLicenses($alert_interval)
                ->with('manufacturer','category')
                ->orderBy('expiration_date', 'ASC')
                ->orderBy('termination_date', 'ASC')
                ->get();
            if ($licenses->count() > 0) {
                Mail::to($recipients)->send(new ExpiringLicenseMail($licenses, $alert_interval));

                $this->table(
                    [
                        trans('general.id'),
                        trans('general.name'),
                        trans('general.purchase_date'),
                        trans('admin/licenses/form.expiration'),
                        trans('mail.expires'),
                        trans('admin/licenses/form.termination_date'),
                        trans('mail.terminates')],
                    $licenses->map(fn($item) => [
                        trans('general.id') => $item->id,
                        trans('general.name') => $item->name,
                        trans('general.purchase_date') => $item->purchase_date_formatted,
                        trans('admin/licenses/form.expiration') => $item->expires_formatted_date,
                        trans('mail.expires') => $item->expires_diff_for_humans,
                        trans('admin/licenses/form.termination_date') => $item->terminates_formatted_date,
                        trans('mail.terminates') => $item->terminates_diff_for_humans
                    ])
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
