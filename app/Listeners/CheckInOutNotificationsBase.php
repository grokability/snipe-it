<?php

namespace App\Listeners;

use App\Actions\Acceptances\CreateCheckoutAcceptanceAction;
use App\Events\CheckoutableCheckedOut;
use App\Mail\CheckinAccessoryMail;
use App\Mail\CheckinAssetMail;
use App\Mail\CheckinComponentMail;
use App\Mail\CheckinLicenseMail;
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
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CheckinAccessoryNotification;
use App\Notifications\CheckinAssetNotification;
use App\Notifications\CheckinComponentNotification;
use App\Notifications\CheckinLicenseSeatNotification;
use App\Notifications\CheckoutAccessoryNotification;
use App\Notifications\CheckoutAssetNotification;
use App\Notifications\CheckoutComponentNotification;
use App\Notifications\CheckoutConsumableNotification;
use App\Notifications\CheckoutLicenseSeatNotification;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Osama\LaravelTeamsNotification\TeamsNotification;

abstract class CheckInOutNotificationsBase
{
    protected $skipNotificationsFor = [];

    protected function getCheckoutableForNotification(Model $checkoutable, bool $shouldRefresh): Model
    {
        if (!$shouldRefresh) {
            return $checkoutable;
        }

        return $checkoutable->fresh() ?? $checkoutable;
    }

    /**
     * This gets the recipient objects based on the type of checkoutable.
     * The 'name' property for users is set in the boot method in the User model.
     *
     * @return mixed
     * @see User::boot()
     *
     */
    protected function getNotifiableUser($event)
    {

        // If it's assigned to an asset, get that asset's assignedTo object
        if ($event->checkedOutTo instanceof Asset) {
            $event->checkedOutTo->load('assignedTo');

            return $event->checkedOutTo->assignedto;

            // If it's assigned to a location, get that location's manager object
        } elseif ($event->checkedOutTo instanceof Location) {
            return $event->checkedOutTo->manager;

            // Otherwise just return the assigned to object
        } else {
            return $event->checkedOutTo;
        }
    }

    protected function webhookSelected()
    {
        if (Setting::getSettings()->webhook_selected === 'slack' || Setting::getSettings()->webhook_selected === 'general') {
            return 'slack';
        }

        return Setting::getSettings()->webhook_selected;
    }

    protected function shouldNotSendAnyNotifications($checkoutable): bool
    {
        return in_array(get_class($checkoutable), $this->skipNotificationsFor);
    }

    protected function shouldSendWebhookNotification(): bool
    {
        return Setting::getSettings() && Setting::getSettings()->webhook_endpoint;
    }

    protected function checkoutableCategoryShouldSendEmail(Model $checkoutable): bool
    {
        if ($checkoutable instanceof LicenseSeat) {
            return $checkoutable->license->checkin_email();
        }

        return method_exists($checkoutable, 'checkin_email') && $checkoutable->checkin_email();
    }

    protected function newMicrosoftTeamsWebhookEnabled(): bool
    {
        return Setting::getSettings()->webhook_selected === 'microsoft' && Str::contains(Setting::getSettings()->webhook_endpoint, 'workflows');
    }

    protected function shouldSkipInitialAcceptanceEmail(CheckoutableCheckedOut $event, ?CheckoutAcceptance $acceptance): bool
    {
        if (!$event->signInPlace) {
            return false;
        }

        return ($acceptance instanceof CheckoutAcceptance) || !empty($event->checkoutable->getEula());
    }

    protected function shouldSendEmailToAlertAddress($acceptance = null): bool
    {
        if (Context::get('action') === 'bulk_asset_checkout') {
            return false;
        }

        $setting = Setting::getSettings();

        if (!$setting) {
            return false;
        }

        if (is_null($acceptance) && !$setting->admin_cc_always) {
            return false;
        }

        return (bool) $setting->admin_cc_email;
    }

    protected function getFormattedAlertAddresses(): array
    {
        $alertAddresses = Setting::getSettings()->admin_cc_email;

        if ($alertAddresses !== '') {
            return array_filter(array_map('trim', explode(',', $alertAddresses)));
        }

        return [];
    }

    protected function generateEmailRecipients(
        bool $shouldSendEmailToUser,
        bool $shouldSendEmailToAlertAddress,
        mixed $notifiable
    ): array {
        $to = [];
        $cc = [];

        // if user && cc: to user, cc admin
        if ($shouldSendEmailToUser && $shouldSendEmailToAlertAddress) {
            $to[] = $notifiable;
            $cc[] = $this->getFormattedAlertAddresses();
        }

        // if user && no cc: to user
        if ($shouldSendEmailToUser && !$shouldSendEmailToAlertAddress) {
            $to[] = $notifiable;
        }

        // if no user && cc: to admin
        if (!$shouldSendEmailToUser && $shouldSendEmailToAlertAddress) {
            $to[] = $this->getFormattedAlertAddresses();
        }

        return [$to, $cc];
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

        // Both the email and webhook listeners react to this same event instance and both
        // need the acceptance record. Whichever runs first creates it and caches it on the
        // event so the other reuses it instead of creating a duplicate row.
        if (!$event->checkoutAcceptance) {
            $category = $this->getCategoryFromCheckoutable($event->checkoutable);
            $alertOnResponseId = $category?->alert_on_response ? auth()->id() : null;
            $event->checkoutAcceptance = CreateCheckoutAcceptanceAction::run(
                $event->checkoutable,
                $event->checkedOutTo,
                $event->checkoutable->checkout_qty ?? 1,
                $alertOnResponseId
            );
        }
        return $event->checkoutAcceptance;
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


}
