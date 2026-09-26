<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\License;
use App\Models\LicenseSeat;
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

class CheckoutLicenseSeatNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public License $item;

    public User $admin;

    public $note;

    public $target;



    /**
     * Create a new notification instance.
     */
    public function __construct(LicenseSeat $licenseSeat, $checkedOutTo, User $checkedOutBy, $acceptance, $note, public Company|Setting $webhookSource)
    {
        $this->item = $licenseSeat->license;
        $this->admin = $checkedOutBy;
        $this->note = $note;
        $this->target = $checkedOutTo;
        $this->acceptance = $acceptance;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        $notifyBy = [];

        if ($this->webhookSource->webhook_selected == 'google') {

            $notifyBy[] = GoogleChatChannel::class;
        }
        if ($this->webhookSource->webhook_selected == 'microsoft') {

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

        $fields = [
            trans('general.to') => '<'.$target->present()->viewUrl().'|'.$target->display_name.'>',
            trans('general.by') => '<'.$admin->present()->viewUrl().'|'.$admin->display_name.'>',
        ];

        // License has no location relation (it's model-tier config,
        // not a physical asset in a room), so no location field for
        // the Slack payload. The `if ($item->location)` branch that
        // used to sit here was dead code from a copy-paste of the
        // Asset checkout notification.
        if ($item->company) {
            $fields[trans('general.company')] = $item->company->name;
        }

        return (new SlackMessage)
            ->content(':arrow_up: :floppy_disk: License Checked Out')
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
                ->title(trans('mail.License_Checkout_Notification'))
                ->addStartGroupToSection('activityText')
                ->fact(htmlspecialchars_decode($item->display_name), '', 'activityTitle')
                ->fact(trans('mail.License_Checkout_Notification').' by ', (string) ($admin?->display_name ?? ''))
                ->fact(trans('mail.assigned_to'), (string) ($target?->display_name ?? ''))
                ->fact(trans('admin/consumables/general.remaining'), (string) $item->availCount()->count())
                ->fact(trans('mail.notes'), $note ?: '');
        }

        $message = trans('mail.License_Checkout_Notification');
        $details = [
            trans('mail.assigned_to') => $target->display_name,
            trans('mail.license_for') => htmlspecialchars_decode($item->display_name),
            trans('mail.License_Checkout_Notification').' by' => $admin->display_name,
            trans('admin/consumables/general.remaining') => $item->availCount()->count(),
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
                        '<strong>'.trans('mail.License_Checkout_Notification').'</strong>' ?: '',
                        htmlspecialchars_decode($item->display_name) ?: '',
                    )
                    ->section(
                        Section::create(
                            KeyValue::create(
                                trans('mail.assigned_to') ?: '',
                                $target->present()->name ?: '',
                                trans('admin/consumables/general.remaining').': '.$item->availCount()->count(),
                            )
                                ->onClick(route('users.show', $target->id))
                        )
                    )
            );

    }
}
