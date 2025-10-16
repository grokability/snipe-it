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
    protected $signature = 'snipeit:upcoming-audits';

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

        $assets = Asset::whereNull('deleted_at')->dueOrOverdueForAudit($settings)->orderBy('assets.next_audit_date', 'asc')->get();
        $this->info($assets->count() . ' assets must be audited in on or before ' . $interval_date . ' is deadline');


        if ((count($assets) !== 0) && ($assets->count() > 0) && ($settings->alert_email != '')) {
            // Send a rollup to the admin, if settings dictate
            $recipients = collect(explode(',', $settings->alert_email))
                ->map(fn($item) => trim($item))
                ->filter(fn($item) => !empty($item))
                ->all();


            $this->info('Sending Admin SendUpcomingAuditNotification to: ' . $settings->alert_email);
            Mail::to($recipients)->send(new SendUpcomingAuditMail($assets, $settings->audit_warning_days));

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
                $assets->map(fn($item) => [
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

    }
}
