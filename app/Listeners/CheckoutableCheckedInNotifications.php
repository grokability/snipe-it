<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use App\Events\CheckoutableCheckedIn;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\LicenseSeat;
use App\Models\Component;
use App\Models\Setting;
use App\Models\User;
use App\Mail\CheckinAccessoryMail;
use App\Mail\CheckinAssetMail;
use App\Mail\CheckinLicenseMail;
use App\Mail\CheckinComponentMail;
use App\Notifications\CheckinAccessoryNotification;
use App\Notifications\CheckinAssetNotification;
use App\Notifications\CheckinLicenseSeatNotification;
use App\Notifications\CheckinComponentNotification;
use Illuminate\Notifications\Notification as BaseNotification;
use GuzzleHttp\Exception\ClientException;
use Exception;
use Osama\LaravelTeamsNotification\TeamsNotification;

class CheckoutableCheckedInNotifications extends CheckInOutNotificationsBase
{
    public function handle(CheckoutableCheckedIn $event)
    {
        Log::debug('onCheckedIn in the Checkoutable listener fired');
        
        if ($this->shouldNotSendAnyNotifications($event->checkoutable)) {
            return;
        }

        $shouldSendEmailToUser = $this->checkoutableCategoryShouldSendEmail($event->checkoutable);
        $shouldSendEmailToAlertAddress = $this->shouldSendEmailToAlertAddress();
        $shouldSendWebhookNotification = $this->shouldSendWebhookNotification($event->checkoutable);
        if (!$shouldSendEmailToUser && !$shouldSendEmailToAlertAddress && !$shouldSendWebhookNotification) {
            return;
        }

        if ($shouldSendEmailToUser || $shouldSendEmailToAlertAddress) {
            /**
             * Send the appropriate notification
             */
            $mailable = $this->getCheckinMailType($event);
            $notifiable = $this->getNotifiableUser($event);

            $notifiableHasEmail = $notifiable instanceof User && $notifiable->email;

            $shouldSendEmailToUser = $shouldSendEmailToUser && $notifiableHasEmail;

            [$to, $cc] = $this->generateEmailRecipients($shouldSendEmailToUser, $shouldSendEmailToAlertAddress, $notifiable);

            if (!empty($to)) {
                try {
                    $toMail = (clone $mailable)->locale($notifiable?->locale);
                    Mail::to(array_flatten($to))->send($toMail);
                    Log::info('Checkin Mail sent to checkin target');
                } catch (ClientException $e) {
                    Log::debug('Exception caught during checkin email: ' . $e->getMessage());
                } catch (Exception $e) {
                    Log::debug('Exception caught during checkin email: ' . $e->getMessage());
                }
            }
            if (!empty($cc)) {
                try {
                    $ccMail = (clone $mailable)->locale(Setting::getSettings()->locale);
                    Mail::cc(array_flatten($cc))->send($ccMail);
                } catch (ClientException $e) {
                    Log::debug('Exception caught during checkin email: ' . $e->getMessage());
                } catch (Exception $e) {
                    Log::debug('Exception caught during checkin email: ' . $e->getMessage());
                }
            }
        }

        if ($shouldSendWebhookNotification) {
            // Send Webhook notification
            $webhookSource = $this->webhookSource($event->checkoutable);
            try {
                if ($this->newMicrosoftTeamsWebhookEnabled($event->checkoutable)) {
                    $message = $this->getCheckinNotification($event, true)->toMicrosoftTeams();
                    $notification = new TeamsNotification($webhookSource->webhook_endpoint);
                    $notification->success()->sendMessage($message[0], $message[1]); // Send the message to Microsoft Teams
                } else {
                    Notification::route($this->webhookSelected($event->checkoutable), $webhookSource->webhook_endpoint)
                        ->notify($this->getCheckinNotification($event, true));
                }
            } catch (ClientException $e) {
                $status = $e->getResponse()->getStatusCode();

                if (strpos($e->getMessage(), 'channel_not_found') !== false) {
                    Log::warning($webhookSource->webhook_selected . ' notification failed: ' . $e->getMessage());

                    return redirect()->back()->with('warning', ucfirst($webhookSource->webhook_selected) . trans('admin/settings/message.webhook.webhook_channel_not_found'));
                } else {
                    if ($status >= 500 || $status === null) {
                        Log::error($webhookSource->webhook_selected . ' notification failed: ' . $e->getMessage());
                    } else {
                        Log::warning('ClientException caught during checkin notification: ' . $e->getMessage());

                        return redirect()->back()->with('warning', ucfirst($webhookSource->webhook_selected) . trans('admin/settings/message.webhook.webhook_fail'));
                    }
                }
            } catch (Exception $e) {
                Log::warning(ucfirst($webhookSource->webhook_selected) . ' webhook notification failed:', [
                    'error' => $e->getMessage(),
                    'webhook_endpoint' => $webhookSource->webhook_endpoint,
                    'event' => $event,
                ]);

                return redirect()->back()->with('warning', ucfirst($webhookSource->webhook_selected) . trans('admin/settings/message.webhook.webhook_fail'));
            }
        }
    }

    private function getCheckinMailType($event)
    {
        $lookup = [
            Accessory::class => CheckinAccessoryMail::class,
            Asset::class => CheckinAssetMail::class,
            LicenseSeat::class => CheckinLicenseMail::class,
            Component::class => CheckinComponentMail::class,
        ];
        $mailable = $lookup[get_class($event->checkoutable)];

        return new $mailable($event->checkoutable, $event->checkedOutTo, $event->checkedInBy, $event->note);

    }

    /**
     * Get the appropriate notification for the event
     *
     * @param  CheckoutableCheckedIn  $event
     * @return Notification
     */
    private function getCheckinNotification($event, bool $refreshCheckoutable = false): BaseNotification
    {
        $notificationClass = null;
        $checkoutable = $this->getCheckoutableForNotification($event->checkoutable, $refreshCheckoutable);

        switch (get_class($checkoutable)) {
            case Accessory::class:
                $notificationClass = CheckinAccessoryNotification::class;
                break;
            case Asset::class:
                $notificationClass = CheckinAssetNotification::class;
                break;
            case LicenseSeat::class:
                $notificationClass = CheckinLicenseSeatNotification::class;
                break;
            case Component::class:
                $notificationClass = CheckinComponentNotification::class;
                break;
        }

        Log::debug('Notification class: ' . $notificationClass);

        return new $notificationClass($checkoutable, $event->checkedOutTo, $event->checkedInBy, $event->note, $this->webhookSource($checkoutable));
    }

}