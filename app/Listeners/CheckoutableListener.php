<?php

namespace App\Listeners;

use App\Events\CheckoutableCheckedOut;
use App\Mail\CheckinAccessoryMail;
use App\Mail\CheckinComponentMail;
use App\Mail\CheckinLicenseMail;
use App\Mail\CheckoutAccessoryMail;
use App\Mail\CheckoutAssetMail;
use App\Mail\CheckinAssetMail;
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
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Exception;
use Illuminate\Support\Facades\Log;
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
            \App\Events\CheckoutableCheckedOut::class,
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
                    Log::debug("Exception caught during checkout email: " . $e->getMessage());
                } catch (Exception $e) {
                    Log::debug("Exception caught during checkout email: " . $e->getMessage());
                }
            }
            if (!empty($cc)) {
                try {
                    $ccMail = (clone $mailable)->locale(Setting::getSettings()->locale);
                    Mail::to(array_flatten($cc))->send($ccMail);
                } catch (ClientException $e) {
                    Log::debug("Exception caught during checkout email: " . $e->getMessage());
                } catch (Exception $e) {
                    Log::debug("Exception caught during checkout email: " . $e->getMessage());
                }
            }
        }

        if ($shouldSendWebhookNotification) {
            try {
                if ($this->newMicrosoftTeamsWebhookEnabled()) {
                    $message = $this->getCheckoutNotification($event)->toMicrosoftTeams();
                    $notification = new TeamsNotification(Setting::getSettings()->webhook_endpoint);
                    $notification->success()->sendMessage($message[0], $message[1]);  // Send the message to Microsoft Teams
                } else {
                    Notification::route($this->webhookSelected(), Setting::getSettings()->webhook_endpoint)
                        ->notify($this->getCheckoutNotification($event, $acceptance));
                }
            } catch (ClientException $e) {
                if (strpos($e->getMessage(), 'channel_not_found') !== false) {
                    Log::warning(Setting::getSettings()->webhook_selected . " notification failed: " . $e->getMessage());
                    return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected) . trans('admin/settings/message.webhook.webhook_channel_not_found'));
                } else {
                    Log::error("ClientException caught during checkin notification: " . $e->getMessage());
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
     * Notify the user and post to webhook about the checked in checkoutable
     */
    public function onCheckedIn($event)
    {
        Log::debug('onCheckedIn in the Checkoutable listener fired');

        if ($this->shouldNotSendAnyNotifications($event->checkoutable)) {
            return;
        }

        $shouldSendEmailToUser = $this->checkoutableCategoryShouldSendEmail($event->checkoutable);
        $shouldSendEmailToAlertAddress = $this->shouldSendEmailToAlertAddress();
        $shouldSendWebhookNotification = $this->shouldSendWebhookNotification();
        if (!$shouldSendEmailToUser && !$shouldSendEmailToAlertAddress && !$shouldSendWebhookNotification) {
            return;
        }

        if ($shouldSendEmailToUser || $shouldSendEmailToAlertAddress) {
            /**
             * Send the appropriate notification
             */
            if ($event->checkedOutTo && $event->checkoutable) {
                $acceptances = CheckoutAcceptance::where('checkoutable_id', $event->checkoutable->id)
                    ->where('assigned_to_id', $event->checkedOutTo->id)
                    ->get();

                foreach ($acceptances as $acceptance) {
                    if ($acceptance->isPending()) {
                        $acceptance->delete();
                    }
                }
            }

            $mailable = $this->getCheckinMailType($event);
            $notifiable = $this->getNotifiableUser($event);

            $notifiableHasEmail = $notifiable instanceof User && $notifiable->email;

            $shouldSendEmailToUser = $shouldSendEmailToUser && $notifiableHasEmail;

            [$to, $cc] = $this->generateEmailRecipients($shouldSendEmailToUser, $shouldSendEmailToAlertAddress, $notifiable);

            if (!empty($to)) {
                try {
                    $toMail = (clone $mailable)->locale($notifiable->locale);
                    Mail::to(array_flatten($to))->send($toMail);
                    Log::info('Checkin Mail sent to checkin target');
                } catch (ClientException $e) {
                    Log::debug("Exception caught during checkin email: " . $e->getMessage());
                } catch (Exception $e) {
                    Log::debug("Exception caught during checkin email: " . $e->getMessage());
                }
            }
            if (!empty($cc)) {
                try {
                    $ccMail = (clone $mailable)->locale(Setting::getSettings()->locale);
                    Mail::to(array_flatten($cc))->send($ccMail);
                } catch (ClientException $e) {
                    Log::debug("Exception caught during checkin email: " . $e->getMessage());
                } catch (Exception $e) {
                    Log::debug("Exception caught during checkin email: " . $e->getMessage());
                }
            }
        }

        if ($shouldSendWebhookNotification) {
            // Send Webhook notification
            try {
                if ($this->newMicrosoftTeamsWebhookEnabled()) {
                    $message = $this->getCheckinNotification($event)->toMicrosoftTeams();
                    $notification = new TeamsNotification(Setting::getSettings()->webhook_endpoint);
                    $notification->success()->sendMessage($message[0], $message[1]); // Send the message to Microsoft Teams
                } else {
                    Notification::route($this->webhookSelected(), Setting::getSettings()->webhook_endpoint)
                        ->notify($this->getCheckinNotification($event));
                }
            } catch (ClientException $e) {
                if (strpos($e->getMessage(), 'channel_not_found') !== false) {
                    Log::warning(Setting::getSettings()->webhook_selected . " notification failed: " . $e->getMessage());
                    return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected) . trans('admin/settings/message.webhook.webhook_channel_not_found'));
                } else {
                    Log::error("ClientException caught during checkin notification: " . $e->getMessage());
                    return redirect()->back()->with('warning', ucfirst(Setting::getSettings()->webhook_selected) . trans('admin/settings/message.webhook.webhook_fail'));
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
     * Generates a checkout acceptance
     * @param  Event $event
     * @return mixed
     */
    private function getCheckoutAcceptance($event)
    {
        $checkedOutToType = get_class($event->checkedOutTo);
        if ($checkedOutToType != "App\Models\User") {
            return null;
        }

        if (!$event->checkoutable->requireAcceptance()) {
            return null;
        }

        $acceptance = new CheckoutAcceptance;
        $acceptance->checkoutable()->associate($event->checkoutable);
        $acceptance->assignedTo()->associate($event->checkedOutTo);

        $acceptance->qty = 1;

        if (isset($event->checkoutable->checkout_qty)) {
            $acceptance->qty = $event->checkoutable->checkout_qty;
        }

        $category = $this->getCategoryFromCheckoutable($event->checkoutable);

        if ($category?->alert_on_response) {
            $acceptance->alert_on_response_id = auth()->id();
        }
        
        $acceptance->save();

        return $acceptance;
    }

    /**
     * Get the appropriate notification for the event
     *
     * @param  CheckoutableCheckedIn  $event
     * @return Notification
     */
    private function getCheckinNotification($event)
    {

        $notificationClass = null;

        switch (get_class($event->checkoutable)) {
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

        return new $notificationClass($event->checkoutable, $event->checkedOutTo, $event->checkedInBy, $event->note);
    }
    /**
     * Get the appropriate notification for the event
     * 
     * @param  CheckoutableCheckedOut $event
     * @param  CheckoutAcceptance|null $acceptance
     * @return Notification
     */
    private function getCheckoutNotification($event, $acceptance = null)
    {
        $notificationClass = null;

        switch (get_class($event->checkoutable)) {
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


        return new $notificationClass($event->checkoutable, $event->checkedOutTo, $event->checkedOutBy, $acceptance, $event->note);
    }
    private function getCheckoutMailType($event, $acceptance){
        $lookup = [
            Accessory::class => CheckoutAccessoryMail::class,
            Asset::class => CheckoutAssetMail::class,
            LicenseSeat::class => CheckoutLicenseMail::class,
            Consumable::class => CheckoutConsumableMail::class,
            Component::class => CheckoutComponentMail::class,
        ];
        $mailable= $lookup[get_class($event->checkoutable)];

        return new $mailable($event->checkoutable, $event->checkedOutTo, $event->checkedOutBy, $acceptance, $event->note);

    }

    private function getCheckinMailType($event){
        $lookup = [
            Accessory::class => CheckinAccessoryMail::class,
            Asset::class => CheckinAssetMail::class,
            LicenseSeat::class => CheckinLicenseMail::class,
            Component::class => CheckinComponentMail::class,
        ];
        $mailable= $lookup[get_class($event->checkoutable)];

        return new $mailable($event->checkoutable, $event->checkedOutTo, $event->checkedInBy, $event->note);

    }

    /**
     * This gets the recipient objects based on the type of checkoutable.
     * The 'name' property for users is set in the boot method in the User model.
     *
     * @see \App\Models\User::boot()
     * @param $event
     * @return mixed
     */
    private function getNotifiableUser($event)
    {

        // If it's assigned to an asset, get that asset's assignedTo object
        if ($event->checkedOutTo instanceof Asset){
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

    private function webhookSelected(){
        if(Setting::getSettings()->webhook_selected === 'slack' || Setting::getSettings()->webhook_selected === 'general'){
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
        return (method_exists($checkoutable, 'checkin_email') && $checkoutable->checkin_email());
    }

    private function newMicrosoftTeamsWebhookEnabled(): bool
    {
        return Setting::getSettings()->webhook_selected === 'microsoft' && Str::contains(Setting::getSettings()->webhook_endpoint, 'workflows');
    }

    private function shouldSendCheckoutEmailToUser(Model $checkoutable): bool
    {
        /**
         * Send an email if any of the following conditions are met:
         * 1. The asset requires acceptance
         * 2. The item has a EULA
         * 3. The item should send an email at check-in/check-out
         */

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

    private function shouldSendEmailToAlertAddress($acceptance = null): bool
    {
        $setting = Setting::getSettings();

        if (!$setting) {
            return false;
        }

        if (is_null($acceptance) && !$setting->admin_cc_always) {
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
        if ($shouldSendEmailToUser && !$shouldSendEmailToAlertAddress) {
            $to[] = $notifiable;
        }

        // if no user && cc: to admin
        if (!$shouldSendEmailToUser && $shouldSendEmailToAlertAddress) {
            $to[] = $this->getFormattedAlertAddresses();
        }

        return array($to, $cc);
    }

    private function getCategoryFromCheckoutable(Model $checkoutable): ?Category
    {
        return match (true) {
            $checkoutable instanceof Asset => $checkoutable->model->category,
            $checkoutable instanceof Accessory,
                $checkoutable instanceof Consumable,
                $checkoutable instanceof Component => $checkoutable->category,
            $checkoutable instanceof LicenseSeat => $checkoutable->license->category,
        };
    }
}
