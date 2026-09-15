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

class CheckoutableCheckedOutEmailNotification extends CheckInOutNotificationsBase
{
    public function handle(CheckoutableCheckedOut $event)
    {
        if ($this->shouldNotSendAnyNotifications($event->checkoutable)) {
            return;
        }

        $acceptance = $this->getCheckoutAcceptance($event);

        $shouldSendEmailToUser = $this->shouldSendCheckoutEmailToUser($event->checkoutable);
        $shouldSendEmailToAlertAddress = $this->shouldSendEmailToAlertAddress($acceptance);

        if ($this->shouldSkipInitialAcceptanceEmail($event, $acceptance)) {
            $shouldSendEmailToUser = false;
            $shouldSendEmailToAlertAddress = false;
        }

        if (!$shouldSendEmailToUser && !$shouldSendEmailToAlertAddress) {
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
    }

    /**
     * Generates a checkout acceptance
     *
     * @param  Event  $event
     * @return mixed
     */

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


}