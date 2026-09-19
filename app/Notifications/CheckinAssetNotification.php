<?php

namespace App\Notifications;

use App\Helpers\Helper;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\SlackWebhookChannel;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NotificationChannels\GoogleChat\Card;
use NotificationChannels\GoogleChat\GoogleChatChannel;
use NotificationChannels\GoogleChat\GoogleChatMessage;
use NotificationChannels\GoogleChat\Section;
use NotificationChannels\GoogleChat\Widgets\KeyValue;
use NotificationChannels\MicrosoftTeams\MicrosoftTeamsChannel;
use NotificationChannels\MicrosoftTeams\MicrosoftTeamsMessage;

class CheckinAssetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $expected_checkin = '';

    /**
     * Create a new notification instance.
     *
     */
    public function __construct(
        public Asset $item,
        public $target,
        public User $admin,
        public                 $note,
        public Company|Setting $webhookSource,
    )
    {
        if ($this->item->expected_checkin) {
            $this->expected_checkin = Helper::getFormattedDateObject($this->item->expected_checkin, 'date',
                false);
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        if ($this->webhookSource->webhook_selected == 'google' && $this->webhookSource->webhook_endpoint) {

            $notifyBy[] = GoogleChatChannel::class;
        }

        if ($this->webhookSource->webhook_selected == 'microsoft' && $this->webhookSource->webhook_endpoint) {

            $notifyBy[] = MicrosoftTeamsChannel::class;
        }
        if ($this->webhookSource->webhook_selected == 'slack' || $this->webhookSource->webhook_selected == 'general') {
            Log::debug('use webhook');
            $notifyBy[] = SlackWebhookChannel::class;
        }

        return $notifyBy;
    }

    public function toSlack()
    {
        $admin = $this->admin;
        $item = $this->item;
        $note = $this->note;
        $botname = ($this->webhookSource->webhook_botname != '') ? $this->webhookSource->webhook_botname : 'Snipe-Bot';
        $channel = ($this->webhookSource->webhook_channel) ? $this->webhookSource->webhook_channel : '';

        $fields = [
            trans('general.administrator') => '<'.$admin->present()->viewUrl().'|'.$admin->display_name.'>',
            trans('general.status') => $item->status?->name,
            trans('general.location') => ($item->location) ? $item->location->name : '',
        ];

        if ($item->location) {
            $fields[trans('general.location')] = $item->location->name;
        }

        if ($item->company) {
            $fields[trans('general.company')] = $item->company->name;
        }

        return (new SlackMessage)
            ->content(':arrow_down: :computer: '.trans('mail.Asset_Checkin_Notification', ['tag' => '']))
            ->from($botname)
            ->to($channel)
            ->attachment(function ($attachment) use ($item, $note, $fields) {
                $attachment->title(htmlspecialchars_decode($item->display_name), $item->present()->viewUrl())
                    ->fields($fields)
                    ->content($note);
            });
    }

    public function toMicrosoftTeams()
    {
        $admin = $this->admin;
        $item = $this->item;
        $note = $this->note;

        if (!Str::contains($this->webhookSource->webhook_endpoint, 'workflows')) {
            return MicrosoftTeamsMessage::create()
                ->to($this->webhookSource->webhook_endpoint)
                ->type('success')
                ->title(trans('mail.Asset_Checkin_Notification', ['tag' => '']))
                ->addStartGroupToSection('activityText')
                ->fact(htmlspecialchars_decode($item->display_name), '', 'activityText')
                ->fact(trans('mail.checked_into'), $item->location?->name ?: '')
                ->fact(trans('general.administrator'), (string) ($admin?->display_name ?? ''))
                ->fact(trans('admin/hardware/form.status'), (string) ($item->status?->name ?? ''))
                ->fact(trans('mail.notes'), $note ?: '');
        }

        $message = trans('mail.Asset_Checkin_Notification', ['tag' => '']);
        $details = [
            trans('mail.asset') => htmlspecialchars_decode($item->display_name),
            trans('mail.checked_into') => ($item->location) ? $item->location->name : '',
            trans('general.administrator') => $admin->display_name,
            trans('admin/hardware/form.status') => $item->status?->name,
            trans('mail.notes') => $note ?: '',
        ];

        return [$message, $details];
    }

    public function toGoogleChat()
    {
        $target = $this->target;
        $item = $this->item;
        $note = $this->note;

        //
        return GoogleChatMessage::create()
            ->to($this->webhookSource->webhook_endpoint)
            ->card(
                Card::create()
                    ->header(
                        '<strong>'.trans('mail.Asset_Checkin_Notification', ['tag' => '']).'</strong>' ?: '',
                        htmlspecialchars_decode($item->display_name) ?: '',
                    )
                    ->section(
                        Section::create(
                            KeyValue::create(
                                trans('mail.checked_into') ?: '',
                                $item->location?->name ?? '-',
                                trans('admin/hardware/form.status').': '.$item->status?->name
                            )->onClick(route('hardware.show', $item->id))
                        )
                    )
            );
    }
}
