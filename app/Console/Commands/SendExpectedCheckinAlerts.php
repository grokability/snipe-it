<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Recipients\AlertRecipient;
use App\Models\Setting;
use App\Notifications\ExpectedCheckinAdminNotification;
use App\Notifications\ExpectedCheckinNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Helpers\Helper;

class SendExpectedCheckinAlerts extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'snipeit:expected-checkin {--with-output : Display the results in a table in your console in addition to sending the email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue or upcoming expected checkins.';

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
        $interval = $settings->due_checkin_days ?? 0;
        $today = Carbon::now();
        $interval_date = $today->copy()->addDays($interval);
        $count = 0;

        if (!$this->option('with-output')) {
            $this->info('Run this command with the --with-output option to see the full list in the console.');
        }

        $assets = Asset::whereNull('deleted_at')->DueOrOverdueForCheckin($settings)->orderBy('assets.expected_checkin', 'desc')->get();

        $this->info($assets->count().' assets must be checked on or before '.Helper::getFormattedDateObject($interval_date, 'date', false));


        foreach ($assets as $asset) {
            if ($asset->assignedTo && (isset($asset->assignedTo->email)) && ($asset->assignedTo->email!='') && $asset->checkedOutToUser()) {
                $asset->assignedTo->notify((new ExpectedCheckinNotification($asset)));
                $count++;
            }
        }

        if ($this->option('with-output')) {
            if (($assets) && ($assets->count() > 0) && ($settings->alert_email != '')) {
                $this->table(
                    [
                        trans('general.id'),
                        trans('admin/hardware/form.tag'),
                        trans('admin/hardware/form.model'),
                        trans('general.model_no'),
                        trans('general.purchase_date'),
                        trans('admin/hardware/form.expected_checkin'),
                    ],
                    $assets->map(fn($assets) => [
                        trans('general.id') => $assets->id,
                        trans('admin/hardware/form.tag') => $assets->asset_tag,
                        trans('admin/hardware/form.model') => $assets->model->name,
                        trans('general.model_no') => $assets->model->model_number,
                        trans('general.purchase_date') => $assets->purchase_date_formatted,
                        trans('admin/hardware/form.eol_date') => $assets->expected_checkin_formattedDate ? $assets->expected_checkin_formattedDate . ' (' . $assets->expected_checkin_diff_for_humans . ')' : '',
                    ])
                );
            }
        }

        if (($assets) && ($assets->count() > 0) && ($settings->alert_email != '')) {
            // Send a rollup to the admin, if settings dictate
            $recipients = collect(explode(',', $settings->alert_email))->map(function ($item) {
                return new AlertRecipient($item);
            });
            Notification::send($recipients, new ExpectedCheckinAdminNotification($assets));

        }
        
        $this->info('Sent checkin reminders to to '.$count.' users.');

    }
}
