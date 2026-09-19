<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\Component;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\SlackWebhookChannel;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\GoogleChat\Card;
use NotificationChannels\GoogleChat\GoogleChatChannel;
use NotificationChannels\GoogleChat\GoogleChatMessage;
use NotificationChannels\GoogleChat\Section;
use NotificationChannels\GoogleChat\Widgets\KeyValue;
use NotificationChannels\MicrosoftTeams\MicrosoftTeamsChannel;
use NotificationChannels\MicrosoftTeams\MicrosoftTeamsMessage;

class CheckinComponentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $params;

    /**
     * Create a new notification instance.
     *
     * @param  $params
     */
    public function __construct(
        public Component $item,
        public $target,
        public User $admin,
        public                 $note,
        public Company|Setting $webhookSource,
    )
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        $notifyBy = [];

        if ($this->webhookSource->webhook_selected == 'google' && $this->webhookSource->webhook_endpoint) {

            $notifyBy[] = GoogleChatChannel::class;
        }
        if ($this->webhookSource->webhook_selected == 'microsoft' && $this->webhookSource->webhook_endpoint) {

            $notifyBy[] = MicrosoftTeamsChannel::class;
        }

        if ($this->webhookSource->webhook_selected == 'slack' || $this->webhookSource->webhook_selected == 'general') {
            $notifyBy[] = SlackWebhookChannel::class;
        }

        return $notifyBy;
    }

    public function toSlack()
    {
        $target = $this->target;
        $admin = $this->admin;
        $item = $this->item;
        $note = $this->note;
        $botname = ($this->webhookSource->webhook_botname) ? $this->webhookSource->webhook_botname : 'Snipe-Bot';
        $channel = ($this->webhookSource->webhook_channel) ? $this->webhookSource->webhook_channel : '';

        if ($admin) {
            $fields = [
                trans('general.from') => '<'.$target->present()->viewUrl().'|'.$target->display_name.'>',
                trans('general.by') => '<'.$admin->present()->viewUrl().'|'.$admin->display_name.'>',
            ];

            if ($item->location) {
                $fields[trans('general.location')] = $item->location->name;
            }

            if ($item->company) {
                $fields[trans('general.company')] = $item->company->name;
            }

        } else {
            $fields = [
                'To' => '<'.$target->present()->viewUrl().'|'.$target->display_name.'>',
                'By' => 'CLI tool',
            ];
        }

        return (new SlackMessage)
            ->content(':arrow_down: :package: '.trans('mail.Component_checkin_notification'))
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
        $target = $this->target;
        $admin = $this->admin;
        $item = $this->item;
        $note = $this->note;
        if (!Str::contains($this->webhookSource->webhook_endpoint, 'workflows')) {
            return MicrosoftTeamsMessage::create()
                ->to($this->webhookSource->webhook_endpoint)
                ->type('success')
                ->addStartGroupToSection('activityTitle')
                ->title(trans('mail.Component_checkin_notification'))
                ->addStartGroupToSection('activityText')
                ->fact(htmlspecialchars_decode($item->display_name), '', 'header')
                ->fact(trans('mail.Component_checkin_notification').' by ', $admin?->display_name ?: 'CLI tool')
                ->fact(trans('mail.checkedin_from'), (string) ($target?->display_name ?? ''))
                ->fact(trans('admin/consumables/general.remaining'), (string) $item->numRemaining())
                ->fact(trans('mail.notes'), $note ?: '');
        }

        $message = trans('mail.Component_checkin_notification');
        $details = [
            trans('mail.checkedin_from') => $target->display_name,
            trans('mail.Component_checkin_notification').' by ' => $admin->display_name ?: 'CLI tool',
            trans('admin/consumables/general.remaining') => $item->numRemaining(),
            trans('mail.notes') => $note ?: '',
        ];

        return [$message, $details];
    }

    public function toGoogleChat()
    {
        $target = $this->target;
        $item = $this->item;
        $note = $this->note;

        return GoogleChatMessage::create()
            ->to($this->webhookSource->webhook_endpoint)
            ->card(
                Card::create()
                    ->header(
                        '<strong>'.trans('mail.Component_checkin_notification').'</strong>' ?: '',
                        htmlspecialchars_decode($item->display_name) ?: '',
                    )
                    ->section(
                        Section::create(
                            KeyValue::create(
                                trans('mail.checkedin_from') ?: '',
                                $target->display_name ?: '',
                                trans('admin/consumables/general.remaining').': '.$item->numRemaining(),
                            )
                                ->onClick(route('components.show', $item->id))
                        )
                    )
            );

    }
}
