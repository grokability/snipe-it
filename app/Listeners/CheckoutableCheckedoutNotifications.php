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

class CheckoutableCheckedoutNotifications extends CheckInOutNotificationsBase
{
    public function handle(CheckoutableCheckedOut $event)
    {
        if ($this->shouldNotSendAnyNotifications($event->checkoutable)) {
            return;
        }

        $acceptance = $this->getCheckoutAcceptance($event);

        $shouldSendEmailToUser = $this->shouldSendCheckoutEmailToUser($event->checkoutable);
        $shouldSendEmailToAlertAddress = $this->shouldSendEmailToAlertAddress($acceptance);
        $shouldSendWebhookNotification = $this->shouldSendWebhookNotification();

        if ($this->shouldSkipInitialAcceptanceEmail($event, $acceptance)) {
            $shouldSendEmailToUser = false;
            $shouldSendEmailToAlertAddress = false;
        }

        if (!$shouldSendEmailToUser && !$shouldSendEmailToAlertAddress && !$shouldSendWebhookNotification) {
            return;
        }

        if ($shouldSendEmailToUser || $shouldSendEmailToAlertAddress) {
            $mailable = $this->getCheckoutMailType($event, $acceptance);
            $notifiable = $this->getNotifiableUser($event);

            $notifiableHasEmail = $notifiable instanceof User && $notifiable->email;

            $shouldSendEmailToUser = $shouldSendEmailToUser && $notifiableHasEmail;

            [$to, $cc] = $this->generateEmailRecipients($shouldSendEmailToUser, $shouldSendEmailToAlertAddress, $notifiable);

            if (!empty($to)) {
                try {
                    $toMail = (clone $mailable)->locale($notifiable->locale);
                    Mail::to(array_flatten($to))->send($toMail);
                    Log::info('Checkout Mail sent to checkout target');
                } catch (ClientException $e) {
                    Log::debug('Exception caught during checkout email: ' . $e->getMessage());
                } catch (Exception $e) {
                    Log::debug('Exception caught during checkout email: ' . $e->getMessage());
                }
            }
            if (!empty($cc)) {
                try {
                    $ccMail = (clone $mailable)->locale(Setting::getSettings()->locale);
                    Mail::cc(array_flatten($cc))->send($ccMail);
                } catch (ClientException $e) {
                    Log::debug('Exception caught during checkout email: ' . $e->getMessage());
                } catch (Exception $e) {
                    Log::debug('Exception caught during checkout email: ' . $e->getMessage());
                }
            }
        }

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
     * Generates a checkout acceptance
     *
     * @param  Event  $event
     * @return mixed
     */
    protected function getCheckoutAcceptance($event)
    {
        // Resolve the acceptance target: the user who actually needs
        // to accept. When the checkoutable was handed to a User
        // directly, that's the target. When the checkoutable was
        // handed to an Asset (which happens for Components checked
        // out to an asset that's already assigned to a user), the
        // asset's assigned User is the effective target, so they can
        // accept the component from their profile. Any other target
        // shape (Location, unassigned Asset, etc.) has no user on the
        // hook, so no acceptance row is written. See GH #19570.
        $acceptanceTarget = $this->resolveAcceptanceTarget($event->checkedOutTo);
        if ($acceptanceTarget === null) {
            return null;
        }

        if (!$event->checkoutable->requireAcceptance()) {
            return null;
        }

        $category = $this->getCategoryFromCheckoutable($event->checkoutable);
        $alertOnResponseId = $category?->alert_on_response ? auth()->id() : null;

        return CreateCheckoutAcceptanceAction::run(
            $event->checkoutable,
            $acceptanceTarget,
            $event->checkoutable->checkout_qty ?? 1,
            $alertOnResponseId,
        );
    }

    /**
     * Walks a checkout target down to the User who should sign the
     * acceptance. Direct-user targets pass through. Asset targets
     * unwrap to the asset's currently-assigned User (if any). Any
     * other target shape returns null and the caller skips the
     * acceptance write.
     */
    private function resolveAcceptanceTarget($checkedOutTo): ?User
    {
        if ($checkedOutTo instanceof User) {
            return $checkedOutTo;
        }

        if ($checkedOutTo instanceof Asset && $checkedOutTo->assignedto instanceof User) {
            return $checkedOutTo->assignedto;
        }

        return null;
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


    protected function getCheckoutMailType($event, $acceptance)
    {
        $lookup = [
            Accessory::class => CheckoutAccessoryMail::class,
            Asset::class => CheckoutAssetMail::class,
            LicenseSeat::class => CheckoutLicenseMail::class,
            Consumable::class => CheckoutConsumableMail::class,
            Component::class => CheckoutComponentMail::class,
        ];
        $mailable = $lookup[get_class($event->checkoutable)];

        return new $mailable($event->checkoutable, $event->checkedOutTo, $event->checkedOutBy, $acceptance, $event->note);

    }

    protected function shouldSendCheckoutEmailToUser(Model $checkoutable): bool
    {
        /**
         * Send an email if we didn't get here from a bulk checkout
         * and any of the following conditions are met:
         * 1. The asset requires acceptance
         * 2. The item has a EULA
         * 3. The item should send an email at check-in/check-out
         */
        if (Context::get('action') === 'bulk_asset_checkout') {
            return false;
        }

        if ($checkoutable->requireAcceptance()) {
            return true;
        }

        if ($checkoutable->getEula()) {
            return true;
        }

        if ($this->checkoutableCategoryShouldSendEmail($checkoutable)) {
            return true;
        }

        return false;
    }

    protected function getCategoryFromCheckoutable(Model $checkoutable): ?Category
    {
        return match (true) {
            $checkoutable instanceof Asset => $checkoutable->model->category,
            $checkoutable instanceof Accessory,
                $checkoutable instanceof Consumable,
                $checkoutable instanceof Component => $checkoutable->category,
            $checkoutable instanceof LicenseSeat => $checkoutable->license->category,
            default => null,
        };
    }

}