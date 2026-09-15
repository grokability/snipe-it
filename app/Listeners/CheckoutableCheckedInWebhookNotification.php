<?php

namespace App\Listeners;

use App\Events\CheckoutableCheckedIn;
use App\Mail\CheckinAccessoryMail;
use App\Mail\CheckinAssetMail;
use App\Mail\CheckinComponentMail;
use App\Mail\CheckinLicenseMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\LicenseSeat;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CheckinAccessoryNotification;
use App\Notifications\CheckinAssetNotification;
use App\Notifications\CheckinComponentNotification;
use App\Notifications\CheckinLicenseSeatNotification;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Osama\LaravelTeamsNotification\TeamsNotification;

class CheckoutableCheckedInWebhookNotification extends CheckInOutNotificationsBase
{
    public function handle(CheckoutableCheckedIn $event)
    {
        Log::debug('CheckoutableCheckedInWebhookNotification fired');

        if ($this->shouldNotSendAnyNotifications($event->checkoutable)) {
            return;
        }

        $shouldSendWebhookNotification = $this->shouldSendWebhookNotification();

        if ($shouldSendWebhookNotification) {
            // Send Webhook notification
            try {
                if ($this->newMicrosoftTeamsWebhookEnabled()) {
                    $message = $this->getCheckinNotification($event, true)->toMicrosoftTeams();
                    $notification = new TeamsNotification(Setting::getSettings()->webhook_endpoint);
                    $notification->success()->sendMessage($message[0], $message[1]); // Send the message to Microsoft Teams
                } else {
                    Notification::route($this->webhookSelected(), Setting::getSettings()->webhook_endpoint)
                        ->notify($this->getCheckinNotification($event, true));
                }
            } catch (ClientException $e) {
                $status = $e->getResponse()->getStatusCode();

                if (strpos($e->getMessage(), 'channel_not_found') !== false) {
                    Log::warning(Setting::getSettings()->webhook_selected . ' notification failed: ' . $e->getMessage());

                    return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected) . trans('admin/settings/message.webhook.webhook_channel_not_found'));
                } else {
                    if ($status >= 500 || $status === null) {
                        Log::error(Setting::getSettings()->webhook_selected . ' notification failed: ' . $e->getMessage());
                    } else {
                        Log::warning('ClientException caught during checkin notification: ' . $e->getMessage());

                        return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected) . trans('admin/settings/message.webhook.webhook_fail'));
                    }
                }
            } catch (Exception $e) {
                Log::warning(ucfirst(Setting::getSettings()->webhook_selected) . ' webhook notification failed:', [
                    'error' => $e->getMessage(),
                    'webhook_endpoint' => Setting::getSettings()->webhook_endpoint,
                    'event' => $event,
                ]);

                return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected) . trans('admin/settings/message.webhook.webhook_fail'));
            }
        }
    }

    /**
     * Get the appropriate notification for the event
     *
     * @param  CheckoutableCheckedIn  $event
     * @return Notification
     */
    protected function getCheckinNotification($event, bool $refreshCheckoutable = false): BaseNotification
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

        return new $notificationClass($checkoutable, $event->checkedOutTo, $event->checkedInBy, $event->note);
    }


}