<?php

namespace App\Notifications;

use App\Models\Accessory;
use App\Models\Setting;
use App\Models\User;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
use Symfony\Component\Mime\Email;

class CheckoutAccessoryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $checkout_qty;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Accessory $item,
        public $target,
        public User $admin,
        public $acceptance,
        public                 $note,
        public Company|Setting $webhookSource,
    )
    {
        $this->checkout_qty = $item->checkout_qty;
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
            $notifyBy[] = 'slack';
        }

        /**
         * Only send notifications to users that have email addresses
         */
        if ($this->target instanceof User && $this->target->email != '') {

            /**
             * Send an email if the asset requires acceptance,
             * so the user can accept or decline the asset
             */
            if ($this->item->requireAcceptance()) {
                $notifyBy[1] = 'mail';
            }

            /**
             * Send an email if the item has a EULA, since the user should always receive it
             */
            if ($this->item->getEula()) {
                $notifyBy[1] = 'mail';
            }

            /**
             * Send an email if an email should be sent at checkin/checkout
             */
            if ($this->item->checkin_email()) {
                $notifyBy[1] = 'mail';
            }
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

        if ($item->location) {
            $fields[trans('general.location')] = $item->location->name;
        }

        if ($item->company) {
            $fields[trans('general.company')] = $item->company->name;
        }

        return (new SlackMessage)
            ->content(':arrow_up: :keyboard: Accessory Checked Out')
            ->from($botname)
            ->to($channel)
            ->attachment(function ($attachment) use ($item, $note, $fields) {
                $attachment->title(htmlspecialchars_decode($this->checkout_qty.' x '.$item->display_name), $item->present()->viewUrl())
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
                ->title(trans('mail.Accessory_Checkout_Notification'))
                ->addStartGroupToSection('activityText')
                ->fact(htmlspecialchars_decode($item->display_name), '', 'activityTitle')
                ->fact(trans('mail.assigned_to'), (string) ($target?->display_name ?? ''))
                ->fact(trans('general.qty'), (string) ($this->checkout_qty ?? 1))
                ->fact(trans('mail.checkedout_from'), $item->location?->name ?: '')
                ->fact(trans('mail.Accessory_Checkout_Notification').' by ', (string) ($admin?->display_name ?? ''))
                ->fact(trans('admin/consumables/general.remaining'), (string) $item->numRemaining())
                ->fact(trans('mail.notes'), $note ?: '');
        }

        $message = trans('mail.Accessory_Checkout_Notification');
        $details = [
            trans('mail.assigned_to') => $target->present()->name,
            trans('mail.accessory_name') => htmlspecialchars_decode($item->display_name),
            trans('general.qty') => $this->checkout_qty,
            trans('mail.checkedout_from') => $item->location->name ? $item->location->name : '',
            trans('mail.Accessory_Checkout_Notification').' by' => $admin->display_name,
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
                        '<strong>'.trans('mail.Accessory_Checkout_Notification').'</strong>' ?: '',
                        htmlspecialchars_decode($item->display_name) ?: '',
                    )
                    ->section(
                        Section::create(
                            KeyValue::create(
                                trans('mail.assigned_to') ?: '',
                                $target->present()->name ?: '',
                                trans('admin/consumables/general.remaining').': '.$item->numRemaining(),
                            )
                                ->onClick(route('users.show', $target->id))
                        )
                    )
            );

    }

    /**
     * Get the mail representation of the notification.
     *
     * @return MailMessage
     */
    public function toMail()
    {
        Log::debug($this->item->getImageUrl());
        $eula = $this->item->getEula();
        $req_accept = $this->item->requireAcceptance();

        $accept_url = is_null($this->acceptance) ? null : route('account.accept.item', $this->acceptance);

        return (new MailMessage)->markdown('notifications.markdown.checkout-accessory',
            [
                'item' => $this->item,
                'admin' => $this->admin,
                'note' => $this->note,
                'target' => $this->target,
                'eula' => $eula,
                'req_accept' => $req_accept,
                'accept_url' => $accept_url,
                'checkout_qty' => $this->checkout_qty,
            ])
            ->subject(trans('mail.Confirm_accessory_delivery'))
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });
    }
}
