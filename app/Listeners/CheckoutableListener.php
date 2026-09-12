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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Osama\LaravelTeamsNotification\TeamsNotification;

class CheckoutableListener
{
    private array $skipNotificationsFor = [
        //        Component::class,
    ];

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            \App\Events\CheckoutableCheckedIn::class,
            'App\Listeners\CheckoutableListener@onCheckedIn'
        );

        $events->listen(
            CheckoutableCheckedOut::class,
            'App\Listeners\CheckoutableListener@onCheckedOut'
        );
    }

    /**
     * Notify the user and post to webhook about the checked out checkoutable
     * and add a record to the checkout_requests table.
     */
    public function onCheckedOut($event)
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

        if (! $shouldSendEmailToUser && ! $shouldSendEmailToAlertAddress && ! $shouldSendWebhookNotification) {
            return;
        }

        if ($shouldSendEmailToUser || $shouldSendEmailToAlertAddress) {
            $mailable = $this->getCheckoutMailType($event, $acceptance);
            $notifiable = $this->getNotifiableUser($event);

            $notifiableHasEmail = $notifiable instanceof User && $notifiable->email;

            $shouldSendEmailToUser = $shouldSendEmailToUser && $notifiableHasEmail;

            [$to, $cc] = $this->generateEmailRecipients($shouldSendEmailToUser, $shouldSendEmailToAlertAddress, $notifiable);

            if (! empty($to)) {
                try {
                    $toMail = (clone $mailable)->locale($notifiable->locale);
                    Mail::to(array_flatten($to))->send($toMail);
                    Log::info('Checkout Mail sent to checkout target');
                } catch (ClientException $e) {
                    Log::debug('Exception caught during checkout email: '.$e->getMessage());
                } catch (Exception $e) {
                    Log::debug('Exception caught during checkout email: '.$e->getMessage());
                }
            }
            if (! empty($cc)) {
                try {
                    $ccMail = (clone $mailable)->locale(Setting::getSettings()->locale);
                    Mail::cc(array_flatten($cc))->send($ccMail);
                } catch (ClientException $e) {
                    Log::debug('Exception caught during checkout email: '.$e->getMessage());
                } catch (Exception $e) {
                    Log::debug('Exception caught during checkout email: '.$e->getMessage());
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
                    Log::warning(Setting::getSettings()->webhook_selected.' notification failed: '.$e->getMessage());

                    return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected).trans('admin/settings/message.webhook.webhook_channel_not_found'));
                } else {
                    if ($status >= 500 || $status === null) {
                        Log::error(Setting::getSettings()->webhook_selected.' notification failed: '.$e->getMessage());
                    } else {
                        Log::warning('ClientException caught during checkin notification: '.$e->getMessage());

                        return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected).trans('admin/settings/message.webhook.webhook_fail'));
                    }
                }

                return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected).trans('admin/settings/message.webhook.webhook_fail'));
            } catch (Exception $e) {
                Log::warning(ucfirst(Setting::getSettings()->webhook_selected).' webhook notification failed:', [
                    'error' => $e->getMessage(),
                    'webhook_endpoint' => Setting::getSettings()->webhook_endpoint,
                    'event' => $event,
                ]);

                return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected).trans('admin/settings/message.webhook.webhook_fail'));
            }
        }
    }

    /**
     * Notify the user and post to webhook about the checked in checkoutable
     */
    public function onCheckedIn($event)
    {
        Log::debug('onCheckedIn in the Checkoutable listener fired');

        if ($event->checkedOutTo instanceof User && $event->checkoutable) {
            $this->retirePendingAcceptances($event->checkoutable, $event->checkedOutTo);
        }

        if ($this->shouldNotSendAnyNotifications($event->checkoutable)) {
            return;
        }

        $shouldSendEmailToUser = $this->checkoutableCategoryShouldSendEmail($event->checkoutable);
        $shouldSendEmailToAlertAddress = $this->shouldSendEmailToAlertAddress();
        $shouldSendWebhookNotification = $this->shouldSendWebhookNotification();
        if (! $shouldSendEmailToUser && ! $shouldSendEmailToAlertAddress && ! $shouldSendWebhookNotification) {
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

            if (! empty($to)) {
                try {
                    $toMail = (clone $mailable)->locale($notifiable->locale);
                    Mail::to(array_flatten($to))->send($toMail);
                    Log::info('Checkin Mail sent to checkin target');
                } catch (ClientException $e) {
                    Log::debug('Exception caught during checkin email: '.$e->getMessage());
                } catch (Exception $e) {
                    Log::debug('Exception caught during checkin email: '.$e->getMessage());
                }
            }
            if (! empty($cc)) {
                try {
                    $ccMail = (clone $mailable)->locale(Setting::getSettings()->locale);
                    Mail::cc(array_flatten($cc))->send($ccMail);
                } catch (ClientException $e) {
                    Log::debug('Exception caught during checkin email: '.$e->getMessage());
                } catch (Exception $e) {
                    Log::debug('Exception caught during checkin email: '.$e->getMessage());
                }
            }
        }

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
                    Log::warning(Setting::getSettings()->webhook_selected.' notification failed: '.$e->getMessage());

                    return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected).trans('admin/settings/message.webhook.webhook_channel_not_found'));
                } else {
                    if ($status >= 500 || $status === null) {
                        Log::error(Setting::getSettings()->webhook_selected.' notification failed: '.$e->getMessage());
                    } else {
                        Log::warning('ClientException caught during checkin notification: '.$e->getMessage());

                        return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected).trans('admin/settings/message.webhook.webhook_fail'));
                    }
                }
            } catch (Exception $e) {
                Log::warning(ucfirst(Setting::getSettings()->webhook_selected).' webhook notification failed:', [
                    'error' => $e->getMessage(),
                    'webhook_endpoint' => Setting::getSettings()->webhook_endpoint,
                    'event' => $event,
                ]);

                return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected).trans('admin/settings/message.webhook.webhook_fail'));
            }
        }
    }

    /**
     * Clear the holder's outstanding acceptance requests for checked-in item.
     *
     * Assets and license seats are 1:1 with their acceptance rows. Accessories
     * are not: accessories_checkout holds one row per unit while an acceptance
     * row covers a whole checkout action and carries its qty, so checking one
     * unit in retires one unit rather than a row that may be worth three.
     *
     * Only ever called for a User holder. Acceptances are created for users
     * alone, so assigned_to_id holds a user id — matching a Location or Asset
     * id against it would clear a different holder's rows by collision.
     */
    private function retirePendingAcceptances(Model $checkoutable, User $checkedOutTo): void
    {
        $acceptances = CheckoutAcceptance::pending()
            ->where('checkoutable_type', $checkoutable->getMorphClass())
            ->where('checkoutable_id', $checkoutable->getKey())
            ->where('assigned_to_id', $checkedOutTo->id)
            ->orderBy('id')
            ->get();

        if ($checkoutable instanceof Accessory) {
            $this->retireOneUnitOfPendingQty($acceptances);

            return;
        }

        $acceptances->each(fn (CheckoutAcceptance $acceptance) => $acceptance->delete());
    }

    /**
     * Retire one unit from the oldest pending row, deleting it at zero.
     *
     * Accessory units are fungible — no serial, no tag — so there is no fact
     * about which unit came back; a checkin is defined to retire an unaccepted
     * one, and to do nothing when none are left.
     *
     * @param  Collection<int, CheckoutAcceptance>  $acceptances
     */
    private function retireOneUnitOfPendingQty($acceptances): void
    {
        $acceptance = $acceptances->first();

        if (! $acceptance) {
            return;
        }

        // Null qty means one unit, as in AcceptanceController and LogListener.
        if (($acceptance->qty ?? 1) <= 1) {
            $acceptance->delete();

            return;
        }

        $acceptance->decrement('qty');
    }

    /**
     * Generates a checkout acceptance
     *
     * @param  Event  $event
     * @return mixed
     */
    private function getCheckoutAcceptance($event)
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

        if (! $event->checkoutable->requireAcceptance()) {
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

        Log::debug('Notification class: '.$notificationClass);

        return new $notificationClass($checkoutable, $event->checkedOutTo, $event->checkedInBy, $event->note);
    }

    /**
     * Get the appropriate notification for the event
     *
     * @param  CheckoutableCheckedOut  $event
     * @param  CheckoutAcceptance|null  $acceptance
     * @return Notification
     */
    private function getCheckoutNotification($event, $acceptance = null, bool $refreshCheckoutable = false): BaseNotification
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

    private function getCheckoutableForNotification(Model $checkoutable, bool $shouldRefresh): Model
    {
        if (! $shouldRefresh) {
            return $checkoutable;
        }

        return $checkoutable->fresh() ?? $checkoutable;
    }

    private function getCheckoutMailType($event, $acceptance)
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
     * This gets the recipient objects based on the type of checkoutable.
     * The 'name' property for users is set in the boot method in the User model.
     *
     * @see User::boot()
     *
     * @return mixed
     */
    private function getNotifiableUser($event)
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

    private function webhookSelected()
    {
        if (Setting::getSettings()->webhook_selected === 'slack' || Setting::getSettings()->webhook_selected === 'general') {
            return 'slack';
        }

        return Setting::getSettings()->webhook_selected;
    }

    private function shouldNotSendAnyNotifications($checkoutable): bool
    {
        return in_array(get_class($checkoutable), $this->skipNotificationsFor);
    }

    private function shouldSendWebhookNotification(): bool
    {
        return Setting::getSettings() && Setting::getSettings()->webhook_endpoint;
    }

    private function checkoutableCategoryShouldSendEmail(Model $checkoutable): bool
    {
        if ($checkoutable instanceof LicenseSeat) {
            return $checkoutable->license->checkin_email();
        }

        return method_exists($checkoutable, 'checkin_email') && $checkoutable->checkin_email();
    }

    private function newMicrosoftTeamsWebhookEnabled(): bool
    {
        return Setting::getSettings()->webhook_selected === 'microsoft' && Str::contains(Setting::getSettings()->webhook_endpoint, 'workflows');
    }

    private function shouldSendCheckoutEmailToUser(Model $checkoutable): bool
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

    private function shouldSkipInitialAcceptanceEmail(CheckoutableCheckedOut $event, ?CheckoutAcceptance $acceptance): bool
    {
        if (! $event->signInPlace) {
            return false;
        }

        return ($acceptance instanceof CheckoutAcceptance) || ! empty($event->checkoutable->getEula());
    }

    private function shouldSendEmailToAlertAddress($acceptance = null): bool
    {
        if (Context::get('action') === 'bulk_asset_checkout') {
            return false;
        }

        $setting = Setting::getSettings();

        if (! $setting) {
            return false;
        }

        if (is_null($acceptance) && ! $setting->admin_cc_always) {
            return false;
        }

        return (bool) $setting->admin_cc_email;
    }

    private function getFormattedAlertAddresses(): array
    {
        $alertAddresses = Setting::getSettings()->admin_cc_email;

        if ($alertAddresses !== '') {
            return array_filter(array_map('trim', explode(',', $alertAddresses)));
        }

        return [];
    }

    private function generateEmailRecipients(
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
        if ($shouldSendEmailToUser && ! $shouldSendEmailToAlertAddress) {
            $to[] = $notifiable;
        }

        // if no user && cc: to admin
        if (! $shouldSendEmailToUser && $shouldSendEmailToAlertAddress) {
            $to[] = $this->getFormattedAlertAddresses();
        }

        return [$to, $cc];
    }

    private function getCategoryFromCheckoutable(Model $checkoutable): ?Category
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
