<?php

namespace App\Services;

use App\Events\CheckoutableCheckedOut;
use App\Models\PredefinedKit;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class incapsulates checkout logic for reuse in different controllers
 * @author [D. Minaev.] [<dmitriy.minaev.v@gmail.com>]
 */
class PredefinedKitCheckoutService
{
    use AuthorizesRequests;

    /**
     * @param Request $request, this function works with fields: checkout_at, expected_checkin, note
     * @param PredefinedKit $kit kit for checkout
     * @param User $user checkout target
     * @return array Empty array if all ok, else [string_error1, string_error2...]
     */
    public function checkout(Request $request, PredefinedKit $kit, User $user)
    {
        try {

            // Check if the user exists
            if (is_null($user)) {
                return ['errors' => trans('admin/users/message.user_not_found')];
            }

            $errors = [];

            $assets_to_add = $this->getAssetsToAdd($kit, $user, $errors);
            $license_seats_to_add = $this->getLicenseSeatsToAdd($kit, $errors);
            $consumables_to_add = $this->getConsumablesToAdd($kit, $errors);
            $accessories_to_add = $this->getAccessoriesToAdd($kit, $errors);

            if (count($errors) > 0) {
                return ['errors' => $errors];
            }

            $checkout_at = date('Y-m-d H:i:s');
            if (($request->filled('checkout_at')) && ($request->input('checkout_at') != date('Y-m-d'))) {
                $checkout_at = $request->input('checkout_at');
            }

            $expected_checkin = '';
            if ($request->filled('expected_checkin')) {
                $expected_checkin = $request->input('expected_checkin');
            }

            $admin = auth()->user();

            $note = e($request->input('note'));

            $errors = $this->saveToDb($user, $admin, $checkout_at, $expected_checkin, $errors, $assets_to_add, $license_seats_to_add, $consumables_to_add, $accessories_to_add, $note);

            return ['errors' => $errors, 'assets' => $assets_to_add, 'accessories' => $accessories_to_add, 'consumables' => $consumables_to_add];
        } catch (ModelNotFoundException $e) {
            return ['errors' => [$e->getMessage()]];
        } catch (CheckoutNotAllowed $e) {
            return ['errors' => [$e->getMessage()]];
        }
    }

    protected function getAssetsToAdd($kit, $user, &$errors)
    {
        $models = $kit->models()
            ->with(['assets' => function ($hasMany) {
                $hasMany->RTD();
            }])
            ->get();
        $assets_to_add = [];
        foreach ($models as $model) {
            $assets = $model->assets;
            $quantity = $model->pivot->quantity;
            foreach ($assets as $asset) {
                if (
                    $asset->availableForCheckout()
                    && ! $asset->is($user)
                ) {
                    $this->authorize('checkout', $asset);
                    $quantity -= 1;
                    $assets_to_add[] = $asset;
                    if ($quantity <= 0) {
                        break;
                    }
                }
            }
            if ($quantity > 0) {
                $errors[] = trans('admin/kits/general.none_models', ['model'=> $model->name, 'qty' => $model->pivot->quantity]);
            }
        }

        return $assets_to_add;
    }

    protected function getLicenseSeatsToAdd($kit, &$errors)
    {
        $seats_to_add = [];
        $licenses = $kit->licenses()
            ->with('freeSeats')
            ->get();
        foreach ($licenses as $license) {
            $quantity = $license->pivot->quantity;
            if ($quantity > count($license->freeSeats)) {
                $errors[] = trans('admin/kits/general.none_licenses', ['license'=> $license->name, 'qty' => $license->pivot->quantity]);
            }
            for ($i = 0; $i < $quantity; $i++) {
                $seats_to_add[] = $license->freeSeats[$i];
            }
        }

        return $seats_to_add;
    }

    protected function getConsumablesToAdd($kit, &$errors)
    {
        $consumables = $kit->consumables()->with('users')->get();
        foreach ($consumables as $consumable) {
            if ($consumable->numRemaining() < $consumable->pivot->quantity) {
                $errors[] = trans('admin/kits/general.none_consumables', ['consumable'=> $consumable->name, 'qty' => $consumable->pivot->quantity]);
            }
        }

        return $consumables;
    }

    protected function getAccessoriesToAdd($kit, &$errors)
    {
        $accessories = $kit->accessories()->with('users')->get();
        foreach ($accessories as $accessory) {
            if ($accessory->numRemaining() < $accessory->pivot->quantity) {
                $errors[] = trans('admin/kits/general.none_accessory', ['accessory'=> $accessory->name, 'qty' => $accessory->pivot->quantity]);
            }
        }

        return $accessories;
    }

    protected function saveToDb($user, $admin, $checkout_at, $expected_checkin, $errors, $assets_to_add, $license_seats_to_add, $consumables_to_add, $accessories_to_add, $note)
    {
        $errors = DB::transaction(
            function () use ($user, $admin, $checkout_at, $expected_checkin, $errors, $assets_to_add, $license_seats_to_add, $consumables_to_add, $accessories_to_add, $note) {
                // assets
                foreach ($assets_to_add as $asset) {
                    $asset->location_id = $user->location_id;
                    $error = $asset->checkOut($user, $admin, $checkout_at, $expected_checkin, $note, null);
                    if ($error) {
                        array_merge_recursive($errors, $asset->getErrors()->toArray());
                    }
                }
                // licenses
                foreach ($license_seats_to_add as $licenseSeat) {
                    $licenseSeat->created_by = $admin->id;
                    $licenseSeat->assigned_to = $user->id;
                    if ($licenseSeat->save()) {
                        event(new CheckoutableCheckedOut($licenseSeat, $user, $admin, $note));
                    } else {
                        $errors[] = 'Something went wrong saving a license seat';
                    }
                }
                // consumables
                foreach ($consumables_to_add as $consumable) {
                    $consumable->assigned_to = $user->id;
                    $consumable->users()->attach($consumable->id, [
                        'consumable_id' => $consumable->id,
                        'user_id' => $admin->id,
                        'assigned_to' => $user->id,
                    ]);
                    event(new CheckoutableCheckedOut($consumable, $user, $admin, $note));
                }
                //accessories
                foreach ($accessories_to_add as $accessory) {
                    $accessory->assigned_to = $user->id;
                    $accessory->users()->attach($accessory->id, [
                        'accessory_id' => $accessory->id,
                        'user_id' => $admin->id,
                        'assigned_to' => $user->id,
                    ]);
                    event(new CheckoutableCheckedOut($accessory, $user, $admin, $note));
                }

                return $errors;
            }
        );

        return $errors;
    }
}
