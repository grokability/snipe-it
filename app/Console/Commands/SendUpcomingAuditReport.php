<?php

namespace App\Console\Commands;

use App\Mail\SendUpcomingAuditMail;
use App\Models\Asset;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendUpcomingAuditReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:upcoming-audits {--with-output : Display the results in a table in your console in addition to sending the email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email/slack notifications for upcoming asset audits.';

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
        $settings = Setting::getSettings();
        $interval = $settings->audit_warning_days ?? 0;
        $today = Carbon::now();
        $interval_date = $today->copy()->addDays($interval);

        $assets_query = Asset::whereNull('deleted_at')->dueOrOverdueForAudit($settings)->orderBy('assets.next_audit_date', 'asc')->with('supplier');
        $asset_count = $assets_query->count();
        $this->info(number_format($asset_count) . ' assets must be audited on or before ' . $interval_date);
        if (!$this->option('with-output')) {
            $this->info('Run this command with the --with-output option to see the full list in the console.');
        }


        if ($asset_count > 0) {

            $assets_for_email = $assets_query->limit(30)->get();

            // Send a rollup to the admin, if settings dictate
            if ($settings->alert_email != '') {

                $recipients = collect(explode(',', $settings->alert_email))
                    ->map(fn($item) => trim($item))
                    ->filter(fn($item) => !empty($item))
                    ->all();

                Mail::to($recipients)->send(new SendUpcomingAuditMail($assets_for_email, $settings->audit_warning_days, $asset_count));
                $this->info('Audit notification sent to: ' . $settings->alert_email);

            } else {
                $this->info('There is no admin alert email set so no email will be sent.');
            }



            if ($this->option('with-output')) {


                // Get the full list if the user wants output in the console
                $assets_for_output = $assets_query->limit(null)->get();

                $this->table(
                    [
                        trans('general.id'),
                        trans('general.name'),
                        trans('general.last_audit'),
                        trans('general.next_audit_date'),
                        trans('mail.Days'),
                        trans('mail.supplier'),
                        trans('mail.assigned_to'),

                    ],
                    $assets_for_output->map(fn($item) => [
                        trans('general.id') => $item->id,
                        trans('general.name') => $item->display_name,
                        trans('general.last_audit') => $item->last_audit_formatted_date,
                        trans('general.next_audit_date') => $item->next_audit_formatted_date,
                        trans('mail.Days') => round($item->next_audit_diff_in_days),
                        trans('mail.supplier') => $item->supplier ? $item->supplier->name : '',
                        trans('mail.assigned_to') => $item->assignedTo ? $item->assignedTo->display_name : '',
                    ])
                );
            }

        } else {
            $this->info('There are no assets due for audit in the next ' . $interval . ' days.');
        }



    }
}
