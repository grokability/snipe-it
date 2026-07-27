<?php

namespace App\Http\Controllers\Users;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransferUserItemsRequest;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class UserItemTransferController extends Controller
{
    public function show(User $user): View|RedirectResponse
    {
        $this->authorize('view', $user);
        $this->authorize('checkin', Asset::class);
        $this->authorize('checkout', Asset::class);

        $assets = $user->assets()
            ->with(['model.category', 'company'])
            ->whereNull('deleted_at')
            ->get();

        $accessoryCheckouts = AccessoryCheckout::with(['accessory.category', 'accessory.company'])
            ->where('assigned_to', $user->id)
            ->where('assigned_type', User::class)
            ->get();

        $licenseSeats = LicenseSeat::with(['license.category', 'license.company'])
            ->where('assigned_to', $user->id)
            ->whereNull('asset_id')
            ->get();

        if ($assets->isEmpty() && $accessoryCheckouts->isEmpty() && $licenseSeats->isEmpty()) {
            return redirect()->route('users.show', $user)
                ->with('error', trans('admin/users/general.transfer.nothing_to_transfer'));
        }

        return view('users.transfer', [
            'sourceUser' => $user,
            'assets' => $assets,
            'accessoryCheckouts' => $accessoryCheckouts,
            'licenseSeats' => $licenseSeats,
        ]);
    }

    public function store(TransferUserItemsRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $target = User::findOrFail($validated['target_user_id']);
        $note = $validated['note'];

        $result = DB::transaction(fn () => [
            'assets' => $this->transferAssets($validated['asset_ids'] ?? [], $user, $target, $note),
            'accessories' => $this->transferAccessories($validated['accessory_checkout_ids'] ?? [], $user, $target, $note),
            'licenses' => $this->transferLicenseSeats($validated['license_seat_ids'] ?? [], $user, $target, $note),
        ]);

        $flash = trans('admin/users/general.transfer.success', [
            'assets' => $result['assets']['count'],
            'accessories' => $result['accessories']['count'],
            'licenses' => $result['licenses']['count'],
            'target' => $target->display_name,
        ]);

        $redirect = redirect()->route('users.show', $target)->with('success', $flash);

        $skipped = array_merge(
            $result['assets']['skipped'],
            $result['accessories']['skipped'],
            $result['licenses']['skipped'],
        );

        if (! empty($skipped)) {
            $redirect->with('warning', trans('admin/users/general.transfer.some_skipped', [
                'count' => count($skipped),
            ]));
        }

        return $redirect;
    }

    /**
     * @return array{count: int, skipped: array<int, string>}
     */
    private function transferAssets(array $ids, User $source, User $target, ?string $note): array
    {
        $count = 0;
        $skipped = [];

        foreach ($ids as $assetId) {
            $asset = Asset::find($assetId);
            if (! $this->assetBelongsToSource($asset, $source) || ! $asset->canCheckoutTo($target)) {
                $skipped[] = 'asset:'.$assetId;

                continue;
            }

            $this->checkInAsset($asset, $source, $note);
            $asset->checkOut($target, auth()->user(), date('Y-m-d H:i:s'), null, $note);
            $count++;
        }

        return ['count' => $count, 'skipped' => $skipped];
    }

    /**
     * @return array{count: int, skipped: array<int, string>}
     */
    private function transferAccessories(array $ids, User $source, User $target, ?string $note): array
    {
        $count = 0;
        $skipped = [];

        foreach ($ids as $checkoutId) {
            $checkout = AccessoryCheckout::with('accessory')->find($checkoutId);
            $accessory = $checkout?->accessory;
            if (! $this->accessoryCheckoutBelongsToSource($checkout, $source) || ! $accessory?->canCheckoutTo($target)) {
                $skipped[] = 'accessory:'.$checkoutId;

                continue;
            }

            $this->checkInAccessory($checkout, $accessory, $note);
            $this->checkOutAccessory($accessory, $target, $note);
            $count++;
        }

        return ['count' => $count, 'skipped' => $skipped];
    }

    /**
     * Non-reassignable licenses stay with the original assignee. Transferring
     * one would defeat the whole point of the flag, so we drop it into the
     * skipped bucket and surface a warning.
     *
     * @return array{count: int, skipped: array<int, string>}
     */
    private function transferLicenseSeats(array $ids, User $source, User $target, ?string $note): array
    {
        $count = 0;
        $skipped = [];

        foreach ($ids as $seatId) {
            $seat = LicenseSeat::with('license')->find($seatId);
            $license = $seat?->license;
            if (! $this->licenseSeatBelongsToSource($seat, $source) || ! $license?->reassignable || ! $license->canCheckoutTo($target)) {
                $skipped[] = 'license:'.$seatId;

                continue;
            }

            $this->transferLicenseSeat($seat, $source, $target, $note);
            $count++;
        }

        return ['count' => $count, 'skipped' => $skipped];
    }

    private function assetBelongsToSource(?Asset $asset, User $source): bool
    {
        return $asset !== null
            && (int) $asset->assigned_to === (int) $source->id
            && $asset->assigned_type === User::class;
    }

    private function accessoryCheckoutBelongsToSource(?AccessoryCheckout $checkout, User $source): bool
    {
        return $checkout !== null
            && (int) $checkout->assigned_to === (int) $source->id
            && $checkout->assigned_type === User::class;
    }

    private function licenseSeatBelongsToSource(?LicenseSeat $seat, User $source): bool
    {
        return $seat !== null
            && (int) $seat->assigned_to === (int) $source->id
            && $seat->asset_id === null;
    }

    private function checkInAsset(Asset $asset, User $source, ?string $note): void
    {
        $originalValues = $asset->getRawOriginal();
        $checkinAt = date('Y-m-d H:i:s');

        $asset->expected_checkin = null;
        $asset->last_checkin = $checkinAt;
        $asset->accepted = null;
        $asset->assignedTo()->dissociate();

        $asset->licenseseats->each(function (LicenseSeat $seat) {
            $seat->update(['assigned_to' => null]);
        });

        CheckoutAcceptance::pending()
            ->where('checkoutable_type', Asset::class)
            ->where('checkoutable_id', $asset->id)
            ->get()
            ->each(fn ($a) => $a->delete());

        $asset->save();

        event(new CheckoutableCheckedIn($asset, $source, auth()->user(), $note, $checkinAt, $originalValues));
    }

    private function checkInAccessory(AccessoryCheckout $checkout, Accessory $accessory, ?string $note): void
    {
        $source = $checkout->assignedTo;

        CheckoutAcceptance::pending()
            ->where('checkoutable_type', Accessory::class)
            ->where('checkoutable_id', $accessory->id)
            ->where('assigned_to_id', $checkout->assigned_to)
            ->get()
            ->each(fn ($a) => $a->delete());

        $checkout->delete();

        event(new CheckoutableCheckedIn($accessory, $source, auth()->user(), $note, date('Y-m-d H:i:s')));
    }

    private function checkOutAccessory(Accessory $accessory, User $target, ?string $note): void
    {
        $newCheckout = new AccessoryCheckout([
            'accessory_id' => $accessory->id,
            'assigned_to' => $target->id,
            'assigned_type' => User::class,
            'note' => $note,
        ]);
        $newCheckout->created_by = auth()->id();
        $newCheckout->save();

        event(new CheckoutableCheckedOut($accessory, $target, auth()->user(), $note, [], 1, false));
    }

    private function transferLicenseSeat(LicenseSeat $seat, User $source, User $target, ?string $note): void
    {
        CheckoutAcceptance::pending()
            ->where('checkoutable_type', License::class)
            ->where('checkoutable_id', $seat->license_id)
            ->where('assigned_to_id', $source->id)
            ->get()
            ->each(fn ($a) => $a->delete());

        $seat->assigned_to = null;
        $seat->save();
        event(new CheckoutableCheckedIn($seat, $source, auth()->user(), $note));

        $seat->assigned_to = $target->id;
        $seat->save();
        event(new CheckoutableCheckedOut($seat, $target, auth()->user(), $note, [], 1, false));
    }
}
