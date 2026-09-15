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

class CheckoutableCheckedInEmailNotification extends CheckInOutNotificationsBase
{
    public function handle(CheckoutableCheckedIn $event)
    {
        if ($this->shouldNotSendAnyNotifications($event->checkoutable)) {
            return;
        }

        $shouldSendEmailToUser = $this->checkoutableCategoryShouldSendEmail($event->checkoutable);
        $shouldSendEmailToAlertAddress = $this->shouldSendEmailToAlertAddress();
        if (!$shouldSendEmailToUser && !$shouldSendEmailToAlertAddress) {
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
    }

    protected function getCheckinMailType($event)
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


}