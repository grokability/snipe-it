<?php

namespace App\Actions\Breadcrumbs;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tabuna\Breadcrumbs\Trail;

final class BuildAcceptanceBreadcrumbs
{
    public static function forAcceptance(Trail $trail, CheckoutAcceptance|int|string $acceptance): void
    {
        $acceptance = self::resolveAcceptance($acceptance);
        $trail->parent('home');

        if (! $acceptance instanceof CheckoutAcceptance) {
            self::appendProfileContext($trail);

            return;
        }

        if (! self::isSignInPlaceFlow($acceptance)) {
            self::appendProfileContext($trail);
            $trail->push(trans('general.accept_item'), route('account.accept.item', $acceptance));

            return;
        }

        self::appendCheckoutFlowContext($trail, $acceptance);
        $trail->push(self::buildSignInPlaceLabel($acceptance));
    }

    private static function resolveAcceptance(CheckoutAcceptance|int|string $acceptance): ?CheckoutAcceptance
    {
        if ($acceptance instanceof CheckoutAcceptance) {
            return $acceptance;
        }

        if (is_numeric($acceptance)) {
            return CheckoutAcceptance::find((int) $acceptance);
        }

        return null;
    }

    private static function isSignInPlaceFlow(CheckoutAcceptance $acceptance): bool
    {
        return (int) session('sign_in_place_acceptance_id') === (int) $acceptance->id;
    }

    private static function appendProfileContext(Trail $trail): void
    {
        $trail->push(trans('general.profile'), route('account'));
        $trail->push(trans('general.accept_items'), route('account.accept'));
    }

    private static function appendCheckoutFlowContext(Trail $trail, CheckoutAcceptance $acceptance): void
    {
        $checkoutable = $acceptance->checkoutable;

        if ($checkoutable instanceof Asset) {
            $trail->push(trans('general.assets'), route('hardware.index'));
            $trail->push($checkoutable->display_name ?? trans('general.asset'), route('hardware.show', $checkoutable));
            $trail->push(trans('general.checkout'));

            return;
        }

        if ($checkoutable instanceof LicenseSeat) {
            $license = $checkoutable->license;

            if ($license instanceof License) {
                $trail->push(trans('general.licenses'), route('licenses.index'));
                $trail->push($license->display_name ?? trans('general.license'), route('licenses.show', $license));
                $trail->push(trans('general.checkout'));
            }

            return;
        }

        if ($checkoutable instanceof Consumable) {
            $trail->push(trans('general.consumables'), route('consumables.index'));
            $trail->push($checkoutable->display_name ?? trans('general.consumable'), route('consumables.show', $checkoutable));
            $trail->push(trans('general.checkout'));

            return;
        }

        if ($checkoutable instanceof Accessory) {
            $trail->push(trans('general.accessories'), route('accessories.index'));
            $trail->push($checkoutable->display_name ?? trans('general.accessory'), route('accessories.show', $checkoutable));
            $trail->push(trans('general.checkout'));
        }
    }

    private static function buildSignInPlaceLabel(CheckoutAcceptance $acceptance): string
    {
        if ($acceptance->assignedTo instanceof User) {
            return sprintf('%s for %s', trans('general.sign_in_place'), $acceptance->assignedTo->display_name);
        }

        return trans('general.sign_in_place');
    }
}
