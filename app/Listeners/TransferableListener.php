<?php
namespace App\Listeners;
use App\Events\AssetsTransferredInBulk;
use App\Mail\CheckoutAccessoryMail;
use App\Mail\CheckoutAssetMail;
use App\Mail\CheckoutComponentMail;
use App\Mail\CheckoutConsumableMail;
use App\Mail\CheckoutLicenseMail;
use App\Mail\TransferredMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpClient\Exception\ClientException;

class TransferableListener
{
    public function subscribe($events)
    {
        $events->listen(
            AssetsTransferredInBulk::class,
            'App\Listeners\TransferableListener@onTransfer'
        );
    }
    public function onTransfer($event){

        $acceptance = $this->getTransferAcceptance($event);

        $shouldSendEmailToUser = $this->shouldSendTransferEmailToUser($event->transferable);
        $shouldSendEmailToAlertAddress = $this->shouldSendEmailToAlertAddress($acceptance);
        $shouldSendWebhookNotification = $this->shouldSendWebhookNotification();

        if (!$shouldSendEmailToUser && !$shouldSendEmailToAlertAddress && !$shouldSendWebhookNotification) {
            return;
        }
        if ($shouldSendEmailToUser || $shouldSendEmailToAlertAddress) {
            $mailable = new TransferredMail($event->transferable, $event->transferredTo, $event->admin, $acceptance, $event->transferred_at, $event->expected_checkin, $event->note);
            $notifiable = $this->getNotifiableUser($event);
            $notifiableHasEmail = $notifiable instanceof User && $notifiable->email;
            $shouldSendEmailToUser = $shouldSendEmailToUser && $notifiableHasEmail;

            [$to, $cc] = $this->generateEmailRecipients($shouldSendEmailToUser, $shouldSendEmailToAlertAddress, $notifiable);

            if (!empty($to)) {
                try {
                    $toMail = (clone $mailable)->locale($notifiable->locale);
                    Mail::to(array_flatten($to))->send($toMail);
                    Log::info('Transfer Mail sent to transfer target');
                } catch (ClientException $e) {
                    Log::debug("Exception caught during transfer email: " . $e->getMessage());
                } catch (Exception $e) {
                    Log::debug("Exception caught during transfer email: " . $e->getMessage());
                }
            }
            if(!empty($cc)) {
                try {
                    $ccMail = (clone $mailable)->locale(Setting::getSettings()->locale);
                    Mail::to(array_flatten($cc))->send($ccMail);
                } catch (ClientException $e) {
                    Log::debug("Exception caught during transfer email: " . $e->getMessage());
                }
                catch (Exception $e) {
                    Log::debug("Exception caught during transfer email: " . $e->getMessage());
                }
            }
        }
    }
    /**
     * Generates a checkout acceptance
     * @param Event $event
     * @return mixed
     */
    private function getTransferAcceptance($event)
    {
        $transferredToType = get_class($event->transferredTo);
        if ($transferredToType != "App\Models\User") {
            return null;
        }

        if (!$event->transferable->requireAcceptance()) {
            return null;
        }

        $acceptance = new CheckoutAcceptance;
        $acceptance->checkoutable()->associate($event->transferable);
        $acceptance->assignedTo()->associate($event->transferredTo);

        $acceptance->qty = 1;

        if (isset($event->trasnferable->checkout_qty)) {
            $acceptance->qty = $event->trasnferable->checkout_qty;
        }

        $category = $event->transferable->model->category;

        if ($category?->alert_on_response) {
            $acceptance->alert_on_response_id = auth()->id();
        }

        $acceptance->save();

        return $acceptance;
    }

    private function shouldSendWebhookNotification(): bool
    {
        return Setting::getSettings() && Setting::getSettings()->webhook_endpoint;
    }

    private function shouldSendTransferEmailToUser(Model $transferable): bool
    {
        /**
         * Send an email if any of the following conditions are met:
         * 1. The asset requires acceptance
         * 2. The item has a EULA
         * 3. The item should send an email at check-in/check-out
         */

        if (Context::get('action') === 'transfer') {
            return true;
        }

        if ($transferable->requireAcceptance()) {
            return true;
        }

        if ($transferable->getEula()) {
            return true;
        }

        if ($this->checkoutableCategoryShouldSendEmail($transferable)) {
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
        if ($event->transferredTo instanceof Asset){
            $event->transferredTo->load('assignedTo');
            return $event->transferredTo->assignedto;

            // If it's assigned to a location, get that location's manager object
        } elseif ($event->transferredTo instanceof Location) {
            return $event->transferredTo->manager;

            // Otherwise just return the assigned to object
        } else {
            return $event->transferredTo;
        }
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
}