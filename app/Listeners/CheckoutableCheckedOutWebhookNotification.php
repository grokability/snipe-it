<?php

namespace App\Listeners;

use App\Actions\Acceptances\CreateCheckoutAcceptanceAction;
use App\Events\CheckoutableCheckedOut;
use App\Mail\CheckoutAccessoryMail;
use App\Mail\CheckoutAssetMail;
use App\Mail\CheckoutComponentMail;
use App\Mail\CheckoutConsumableMail;
use App\Mail\CheckoutLicenseMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Category;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CheckoutAccessoryNotification;
use App\Notifications\CheckoutAssetNotification;
use App\Notifications\CheckoutComponentNotification;
use App\Notifications\CheckoutConsumableNotification;
use App\Notifications\CheckoutLicenseSeatNotification;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Osama\LaravelTeamsNotification\TeamsNotification;

class CheckoutableCheckedOutWebhookNotification extends CheckInOutNotificationsBase
{
    public function handle(CheckoutableCheckedOut $event)
    {
        if ($this->shouldNotSendAnyNotifications($event->checkoutable)) {
            return;
        }

        $acceptance = $this->getCheckoutAcceptance($event);

        $shouldSendWebhookNotification = $this->shouldSendWebhookNotification();

        if ($shouldSendWebhookNotification) {
            try {
                if ($this->newMicrosoftTeamsWebhookEnabled()) {
                    $message = $this->getCheckoutNotification($event, $acceptance, true)->toMicrosoftTeams();
                    $notification = new TeamsNotification(Setting::getSettings()->webhook_endpoint);
                    $notification->success()->sendMessage($message[0], $message[1]);  // Send the message to Microsoft Teams
                } else {
                    Notification::route($this->webhookSelected(), Setting::getSettings()->webhook_endpoint)
                        ->notify($this->getCheckoutNotification($event, $acceptance, true));
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

                return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected) . trans('admin/settings/message.webhook.webhook_fail'));
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
     * @param  CheckoutableCheckedOut  $event
     * @param  CheckoutAcceptance|null  $acceptance
     * @return Notification
     */
    protected function getCheckoutNotification($event, $acceptance = null, bool $refreshCheckoutable = false): BaseNotification
    {
        $notificationClass = null;
        $checkoutable = $this->getCheckoutableForNotification($event->checkoutable, $refreshCheckoutable);

        switch (get_class($checkoutable)) {
            case Accessory::class:
                $notificationClass = CheckoutAccessoryNotification::class;
                break;
            case Asset::class:
                $notificationClass = CheckoutAssetNotification::class;
                break;
            case Consumable::class:
                $notificationClass = CheckoutConsumableNotification::class;
                break;
            case LicenseSeat::class:
                $notificationClass = CheckoutLicenseSeatNotification::class;
                break;
            case Component::class:
                $notificationClass = CheckoutComponentNotification::class;
                break;
        }

        return new $notificationClass($checkoutable, $event->checkedOutTo, $event->checkedOutBy, $acceptance, $event->note);
    }

}