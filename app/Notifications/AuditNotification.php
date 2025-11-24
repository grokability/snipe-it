<?php

namespace App\Notifications;

use AllowDynamicProperties;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
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
use Symfony\Component\Mime\Email;

#[AllowDynamicProperties]
class AuditNotification extends Notification
{
    use Queueable;
    /**
     * @var
     */
    private $params;

    /**
     * Create a new notification instance.
     *
     * @param $params
     */
    public function __construct($params)
    {
        //
        $this->settings = Setting::getSettings();
        $this->params = $params;
        $item =  $params['item'];
        if (!$item || !is_object($item)) {
            throw new \InvalidArgumentException('Notification requires a valid item.');
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
        if (Setting::getSettings()->webhook_selected == 'slack' || Setting::getSettings()->webhook_selected == 'general' ) {
            Log::debug('use webhook');
            $notifyBy[] = SlackWebhookChannel::class;
        }
        if (Setting::getSettings()->webhook_selected == 'microsoft' && Setting::getSettings()->webhook_endpoint) {

            $notifyBy[] = MicrosoftTeamsChannel::class;
        }
        if (Setting::getSettings()->webhook_selected == 'google' && Setting::getSettings()->webhook_endpoint) {
            Log::debug('using google webhook');
            $notifyBy[] = GoogleChatChannel::class;
        }
        return $notifyBy;
    }

    public function toSlack()
    {
        $channel = ($this->settings->webhook_channel) ? $this->settings->webhook_channel : '';
        return (new SlackMessage)
            ->success()
            ->content(class_basename(get_class($this->params['item'])).' '.trans('general.audited'))
            ->from(($this->settings->webhook_botname) ? $this->settings->webhook_botname : 'Snipe-Bot')
            ->to($channel)
            ->attachment(function ($attachment) {
                $item = $this->params['item'] ?? null;
                $admin_user = $this->params['admin'];
                $fields = [
                    'By' => '<'.$admin_user->present()->viewUrl().'|'.$admin_user->display_name.'>',
                ];
                array_key_exists('note', $this->params) && $fields['Notes'] = $this->params['note'];
                array_key_exists('location', $this->params) && $fields['Location'] = $this->params['location'];

                $attachment->title($item->present()->name, $item->present()->viewUrl())
                    ->fields($fields);
            });
    }

    public static function toMicrosoftTeams($params)
    {
        $item = $params['item'] ?? null;
        $admin_user = $params['admin'] ?? null;
        $note = $params['note'] ?? '';
        $location = $params['location'] ?? '';
        $setting = Setting::getSettings();

        //if somehow a notification triggers without an item, bail out.
        if(!$item || !is_object($item)){
            return null;
        }

        if(!Str::contains($setting->webhook_endpoint, 'workflows')) {
            return MicrosoftTeamsMessage::create()
                ->to($setting->webhook_endpoint)
                ->type('success')
                ->title(class_basename($item).' '.trans('general.audited'))
                ->addStartGroupToSection('activityText')
                ->fact(trans('mail.asset'), $item)
                ->fact(trans('general.administrator'), $admin_user->present()->viewUrl() . '|' . $admin_user->display_name);
        }
            $message = class_basename(get_class($params['item'])) . trans('general.audited_by').' '.$admin_user->display_name;
            $details = [
                trans('mail.asset') => htmlspecialchars_decode($item->display_name),
                trans('mail.notes') => $note ?: '',
                trans('general.location') => $location ?: '',
                ];
            return [$message, $details];
    }
    public function toGoogleChat()
    {
        $item      = $this->params['item']  ?? null;
        $admin_user = $this->params['admin'] ?? null;
        $note      = $this->params['note']  ?? '';
        $setting   = $this->settings ?? Setting::getSettings();

        $title    = '<strong>' . class_basename($item) . ' ' . trans('general.audited') . '</strong>';
        $subtitle = htmlspecialchars_decode($item->display_name ?? '');
        \Log::debug('Google Chat audit payload', [
            'title'    => $title,
            'subtitle' => $subtitle,
            'admin'    => $admin_user->display_name,
            'note'     => $note,
        ]);
        return GoogleChatMessage::create()
            ->to($setting->webhook_endpoint)
            ->card(
                Card::create()
                    ->header($title, $subtitle)
                    ->section(
                        Section::create(
                            KeyValue::create(
                                trans('general.audited_by'),
                                $admin_user?->display_name ?? '',
                                $note ?? ''
                            )->onClick(route('hardware.show', $item->id))
                        )
                    )
            );
    }
}
