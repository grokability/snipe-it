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

class CheckoutAssetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $last_checkout = '';

    public $expected_checkin = '';

    /**
     * Create a new notification instance.
     *
     * @param  $params
     */
    public function __construct(
        public Asset $item,
        public $target,
        public User $admin,
        public $acceptance, // ???? what is this? (doesn't seem used)
        public                 $note,
        public Company|Setting $webhookSource,
    ) {
        if ($this->item->last_checkout) {
            $this->last_checkout = Helper::getFormattedDateObject($this->item->last_checkout, 'date',
                false);
        }

        if ($this->item->expected_checkin) {
            $this->expected_checkin = Helper::getFormattedDateObject($this->item->expected_checkin, 'datetime',
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
        $notifyBy = [];

        if ($this->webhookSource->webhook_selected === 'google' && $this->webhookSource->webhook_endpoint) {

            $notifyBy[] = GoogleChatChannel::class;
        }

        if ($this->webhookSource->webhook_selected === 'microsoft' && $this->webhookSource->webhook_endpoint) {

            $notifyBy[] = MicrosoftTeamsChannel::class;
        }

        if ($this->webhookSource->webhook_selected === 'slack' || $this->webhookSource->webhook_selected === 'general') {

            Log::debug('use webhook');
            $notifyBy[] = SlackWebhookChannel::class;
        }

        return $notifyBy;
    }

    public function toSlack(): SlackMessage
    {
        $target = $this->target;
        $admin = $this->admin;
        $item = $this->item;
        $note = $this->note;
        $botname = ($this->webhookSource->webhook_botname) ?: 'Snipe-Bot';
        $channel = ($this->webhookSource->webhook_channel) ? $this->webhookSource->webhook_channel : '';

        $fields = [
            trans('general.to_user') => '<'.$target->present()->viewUrl().'|'.$target->display_name.'>',
            trans('general.by_user') => '<'.$admin->present()->viewUrl().'|'.$admin->display_name.'>',
        ];

        if ($item->location) {
            $fields[trans('general.location')] = $item->location->name;
        }

        if ($item->company) {
            $fields[trans('general.company')] = $item->company->name;
        }

        if ($this->expected_checkin) {
            $fields[trans('general.expected_checkin')] = $this->expected_checkin;
        }

        return (new SlackMessage)
            ->content(':arrow_up: :computer: '.trans('mail.Asset_Checkout_Notification', ['tag' => '']))
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
                ->title(trans('mail.Asset_Checkout_Notification', ['tag' => '']))
                ->addStartGroupToSection('activityText')
                ->fact(trans('mail.assigned_to'), (string) ($target?->display_name ?? ''))
                ->fact(htmlspecialchars_decode($item->display_name), '', 'activityText')
                ->fact(trans('general.administrator'), (string) ($admin?->display_name ?? ''))
                ->fact(trans('mail.notes'), $note ?: '');
        }

        $message = trans('mail.Asset_Checkout_Notification', ['tag' => '']);
        $details = [
            trans('mail.assigned_to') => $target->present()->name,
            trans('mail.asset') => htmlspecialchars_decode($item->display_name),
            trans('general.administrator') => $admin->display_name,
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
                        '<strong>'.trans('mail.Asset_Checkout_Notification', ['tag' => '']).'</strong>' ?: '',
                        htmlspecialchars_decode($item->display_name) ?: '',
                    )
                    ->section(
                        Section::create(
                            KeyValue::create(
                                trans('mail.assigned_to') ?: '',
                                $target->present()->name ?: '',
                                $note ?: '',
                            )
                                ->onClick(route('users.show', $target->id))
                        )
                    )
            );

    }
}
