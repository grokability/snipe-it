<?php

namespace App\Listeners;

use App\Events\CheckoutablesCheckedOutInBulk;
use App\Mail\BulkAssetCheckoutMail;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutablesCheckedOutInBulkListener
{

    public function subscribe($events)
    {
        $events->listen(
            CheckoutablesCheckedOutInBulk::class,
            CheckoutablesCheckedOutInBulkListener::class
        );
    }

    public function handle(CheckoutablesCheckedOutInBulk $event): void
    {
        $notifiableUser = $this->getNotifiableUser($event);

        $shouldSendEmailToUser = $this->shouldSendCheckoutEmailToUser($notifiableUser, $event->assets);
        $shouldSendEmailToAlertAddress = $this->shouldSendEmailToAlertAddress($event->assets);

        if ($shouldSendEmailToUser && $notifiableUser) {
            try {
                Mail::to($notifiableUser)->send(new BulkAssetCheckoutMail(
                    $event->assets,
                    $event->target,
                    $event->admin,
                    $event->checkout_at,
                    $event->expected_checkin,
                    $event->note,
                ));

                Log::info('BulkAssetCheckoutMail sent to checkout target');
            } catch (Exception $e) {
                Log::debug("Exception caught during BulkAssetCheckoutMail to target: " . $e->getMessage());
            }
        }

        if ($shouldSendEmailToAlertAddress && Setting::getSettings()->admin_cc_email) {
            try {
                Mail::to(Setting::getSettings()->admin_cc_email)->send(new BulkAssetCheckoutMail(
                    $event->assets,
                    $event->target,
                    $event->admin,
                    $event->checkout_at,
                    $event->expected_checkin,
                    $event->note,
                ));

                Log::info('BulkAssetCheckoutMail sent to admin_cc_email');
            } catch (Exception $e) {
                Log::debug("Exception caught during BulkAssetCheckoutMail to admin_cc_email: " . $e->getMessage());
            }
        }
    }

    private function shouldSendCheckoutEmailToUser(?User $user, Collection $assets): bool
    {
        if (!$user?->email) {
            return false;
        }

        if ($this->hasAssetWithEula($assets)) {
            return true;
        }

        if ($this->hasAssetWithCategorySettingToSendEmail($assets)) {
            return true;
        }

        return $this->hasAssetThatRequiresAcceptance($assets);
    }

    private function shouldSendEmailToAlertAddress(Collection $assets): bool
    {
        $setting = Setting::getSettings();

        if (!$setting) {
            return false;
        }

        if ($setting->admin_cc_always) {
            return true;
        }

        if (!$this->hasAssetThatRequiresAcceptance($assets)) {
            return false;
        }

        return (bool) $setting->admin_cc_email;
    }

    private function hasAssetWithEula(Collection $assets): bool
    {
        foreach ($assets as $asset) {
            if ($asset->getEula()) {
                return true;
            }
        }

        return false;
    }

    private function hasAssetWithCategorySettingToSendEmail(Collection $assets): bool
    {
        foreach ($assets as $asset) {
            if ($asset->checkin_email()) {
                return true;
            }
        }

        return false;
    }

    private function hasAssetThatRequiresAcceptance(Collection $assets): bool
    {
        foreach ($assets as $asset) {
            if ($asset->requireAcceptance()) {
                return true;
            }
        }

        return false;
    }

    private function getNotifiableUser(CheckoutablesCheckedOutInBulk $event): ?Model
    {
        $target = $event->target;

        if ($target instanceof Asset) {
            $target->load('assignedTo');
            return $target->assignedto;
        }

        if ($target instanceof Location) {
            return $target->manager;
        }

        return $target;
    }
}
