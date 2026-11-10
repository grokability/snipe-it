<?php

namespace App\Http\Controllers;

use App\Actions\CheckoutRequests\CancelCheckoutRequestAction;
use App\Actions\CheckoutRequests\CreateCheckoutRequestAction;
use App\Enums\ActionType;
use App\Exceptions\ItemNotRequestable;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\RequestAssetCancelation;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * This controller handles all actions related to the ability for users
 * to view their own assets in the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class ViewAssetsController extends Controller
{
    /**
     * Extract custom fields that should be displayed in user view.
     */
    private function extractCustomFields(User $user): array
    {
        $fieldArray = [];
        foreach ($user->assets as $asset) {
            if ($asset->model && $asset->model->fieldset) {
                foreach ($asset->custom_fieldset->fields->where('display_in_user_view', 1) as $field) {
                    $fieldArray[$field->db_column] = $field->name;
                }
            }
        }

        return array_unique($fieldArray);
    }

    /**
     * Get list of users viewable by the current user.
     */
    private function getViewableUsers(User $authUser): Collection
    {
        // SuperAdmin sees all users
        if ($authUser->isSuperUser()) {
            return User::select('id', 'first_name', 'last_name', 'username')
                ->where('activated', 1)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        // Regular manager sees only their subordinates + self
        $managedUsers = $authUser->getAllSubordinates();

        // If user has subordinates, show them with self at beginning
        if ($managedUsers->count() > 0) {
            return collect([$authUser])->merge($managedUsers)
                ->sortBy('last_name')
                ->sortBy('first_name');
        }

        // User has no subordinates, only sees themselves
        return collect([$authUser]);
    }

    /**
     * Get the selected user ID from request or default to current user.
     */
    private function getSelectedUserId(Request $request, Collection $subordinates, int $defaultUserId): int
    {
        // If no subordinates or no user_id in request, return default
        if ($subordinates->count() <= 1 || ! $request->filled('user_id')) {
            return $defaultUserId;
        }

        $requestedUserId = (int) $request->input('user_id');

        // Validate if the requested user is allowed
        if ($subordinates->contains('id', $requestedUserId)) {
            return $requestedUserId;
        }

        // If invalid ID or not authorized, return default
        return $defaultUserId;
    }

    /**
     * Show user's assigned assets with optional manager view functionality.
     */
    public function getIndex(Request $request): View|RedirectResponse
    {
        $authUser = auth()->user();
        $settings = Setting::getSettings();
        $subordinates = collect();
        $selectedUserId = $authUser->id;

        // Process manager view if enabled
        if ($settings->manager_view_enabled) {
            $subordinates = $this->getViewableUsers($authUser);
            $selectedUserId = $this->getSelectedUserId($request, $subordinates, $authUser->id);
        }

        // Load the data for the user to be viewed (either auth user or selected subordinate)
        $userToView = User::with([
            'assets',
            'assets.model',
            'assets.model.fieldset.fields',
            'consumables',
            'accessories',
            'licenses',
            'companies',
        ])->find($selectedUserId);

        // If the user to view couldn't be found (shouldn't happen with proper logic), redirect with error
        if (! $userToView) {
            return redirect()->route('view-assets')->with('error', trans('admin/users/message.user_not_found'));
        }

        // Process custom fields for the user being viewed
        $fieldArray = $this->extractCustomFields($userToView);

        // Pass the necessary data to the view
        return view('account/view-assets', [
            'user' => $userToView, // Use 'user' for compatibility with the existing view
            'field_array' => $fieldArray,
            'settings' => $settings,
            'subordinates' => $subordinates,
            'selectedUserId' => $selectedUserId,
        ]);
    }

    /**
     * Returns view of requestable items for a user.
     */
    public function getRequestableIndex(): View
    {
        // Every tab on /account/requestable is API-backed now, so the
        // server-rendered page is a pure shell. All the controller
        // needs is the reachable-count per type - drives the tab
        // badge + "is this tab visible at all" gate. The API endpoint
        // that hydrates each tab re-applies the same
        // Requestable<Foo>() scope + CompanyableTrait global scope
        // filters, so the counts here stay consistent with what the
        // AJAX call will render.
        $counts = [
            'assets' => Asset::Hardware()->Requestable()->count(),
            'models' => AssetModel::Requestable()->count(),
            'accessories' => Accessory::Requestable()->count(),
            'consumables' => Consumable::Requestable()->count(),
            'components' => Component::Requestable()->count(),
            'licenses' => License::Requestable()->count(),
        ];

        return view('account/requestable-assets', compact('counts'));
    }

    public function getRequestItem(Request $request, $itemType, $itemId = null, $requestingUser = null): RedirectResponse
    {
        // Cancel-by-admin is inferred from the presence of a
        // requestingUser segment that doesn't match the caller.
        // The old URL shape carried a trusted `cancel_by_admin`
        // flag, which was safe (server-side is_admin re-check
        // blocked forgery) but redundant now that the same
        // information falls out of comparing $requestingUser to
        // the auth id.
        $cancel_by_admin = $requestingUser !== null
            && (int) $requestingUser !== (int) auth()->id();
        $fullItemType = 'App\\Models\\'.studly_case($itemType);

        if ($itemType == 'asset_model') {
            $itemType = 'model';
        }
        $item = call_user_func([$fullItemType, 'find'], $itemId);

        // Null $item covers two cases: a straight bad id, and a
        // cross-company id that the CompanyableTrait's global scope
        // hid from the current caller. Either way, return the same
        // "not requestable" error so we don't leak the existence of
        // items in other companies AND don't 500 on the null-deref
        // that would otherwise land two lines below.
        if ($item === null) {
            return redirect()->back()->with('error', trans('admin/hardware/message.requests.error'));
        }

        $user = auth()->user();
        $is_admin = $user->isSuperUser() || $user->isAdmin();

        if ($cancel_by_admin && ! $is_admin) {
            return redirect()->back()->with('error', trans('general.insufficient_permissions'));
        }

        $item_request = $item->isRequestedBy($user);

        if ($item_request || ($is_admin && $cancel_by_admin)) {
            return $this->handleCancelRequest(
                $request,
                $item,
                $fullItemType,
                $itemType,
                $item_request ?: null,
                $is_admin && $cancel_by_admin ? $requestingUser : null,
            );
        }

        return $this->handleSubmitRequest($request, $item);
    }

    /**
     * Assemble the shared notification/actionlog payload used by both
     * the submit and cancel paths. Kept as a helper so the request
     * dispatcher can stay focused on routing.
     *
     * @return array{0: Actionlog, 1: array<string, mixed>}
     */
    private function buildRequestContext(Request $request, $item, string $fullItemType, string $itemType): array
    {
        $user = auth()->user();

        $logaction = new Actionlog;
        $logaction->item_id = $item->id;
        $logaction->item_type = $fullItemType;
        $logaction->created_at = date('Y-m-d H:i:s');
        $logaction->created_by = auth()->id();
        if ($user->location_id) {
            $logaction->location_id = $user->location_id;
        }
        $logaction->target_id = auth()->id();
        $logaction->target_type = User::class;

        $data = [
            'asset_id' => $item->id,
            'requested_date' => $logaction->created_at,
            'user_id' => auth()->id(),
            'item_quantity' => $request->has('request-quantity') ? e($request->input('request-quantity')) : 1,
            'requested_by' => $user->display_name,
            'item' => $item,
            'item_type' => $itemType,
            'target' => $user,
            'item_url' => $this->resolveItemUrl($fullItemType, $item, $itemType),
        ];

        return [$logaction, $data];
    }

    private function resolveItemUrl(string $fullItemType, $item, string $itemType): string
    {
        return match ($fullItemType) {
            Asset::class => route('hardware.show', $item->id),
            AssetModel::class => route('view/model', $item->id),
            Accessory::class => route('accessories.show', $item->id),
            Consumable::class => route('consumables.show', $item->id),
            Component::class => route('components.show', $item->id),
            License::class => route('licenses.show', $item->id),
            default => route("view/{$itemType}", $item->id),
        };
    }

    /**
     * Prefer an explicit active_tab hint over a plain back() so the
     * requester lands on the tab they submitted from. Falls back to
     * back() for callers that don't carry the hint (e.g. admin
     * cancel from /requests).
     */
    private function redirectAfterRequestAction(Request $request, string $successKey): RedirectResponse
    {
        if ($tab = $this->safeActiveTab($request->input('active_tab'))) {
            return redirect()->route('account.requestable')
                ->withFragment($tab)
                ->with('success', trans($successKey));
        }

        return redirect()->back()->with('success', trans($successKey));
    }

    private function handleCancelRequest(Request $request, $item, string $fullItemType, string $itemType, $item_request, $requestingUser): RedirectResponse
    {
        [$logaction, $data] = $this->buildRequestContext($request, $item, $fullItemType, $itemType);

        $item->cancelRequest($requestingUser);
        $data['item_quantity'] = $item_request ? $item_request->quantity : 1;

        if ($item_request) {
            $data['start_date'] = $item_request->start_date;
            $data['end_date'] = $item_request->end_date;
            $data['note'] = $item_request->notes;
        }

        $logaction->logaction(ActionType::RequestCanceled);

        $settings = Setting::getSettings();
        if (($settings->alert_email != '') && ($settings->alerts_enabled == '1') && (! config('app.lock_passwords'))) {
            try {
                $settings->notify((new RequestAssetCancelation($data))->locale($settings->locale));
            } catch (Exception $e) {
                Log::warning('Could not send request cancellation notification: '.$e->getMessage());
            }
        }

        return $this->redirectAfterRequestAction($request, 'admin/hardware/message.requests.canceled');
    }

    private function handleSubmitRequest(Request $request, $item): RedirectResponse
    {
        // Optional reservation window. Empty strings from the
        // request-modal date pickers coerce to null so an "I need
        // this whenever" request doesn't accidentally persist today's
        // date as the start. Uses Laravel's built-in validation
        // strings so a bad range gets the framework's localized
        // "after or equal to" message rather than a hand-rolled key
        // we'd have to translate ourselves.
        $reservationValidator = validator($request->only(['start_date', 'end_date']), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        if ($reservationValidator->fails()) {
            return redirect()->back()->withInput()->withErrors($reservationValidator);
        }

        $startDate = $request->filled('start_date') ? $request->input('start_date') : null;
        $endDate = $request->filled('end_date') ? $request->input('end_date') : null;
        // Optional free-text notes the requester attaches to explain
        // what they need. Empty string coerces to null so an untouched
        // textarea doesn't persist a blank row and then leak an empty
        // "Additional Notes" block into the admin's mail.
        $notes = $request->filled('notes') ? $request->input('notes') : null;
        $qty = $request->has('request-quantity')
            ? max(1, (int) $request->input('request-quantity'))
            : 1;

        try {
            CreateCheckoutRequestAction::run(
                $item,
                auth()->user(),
                $qty,
                $startDate,
                $endDate,
                $notes,
            );
        } catch (\App\Exceptions\ItemNotRequestable $e) {
            return redirect()->back()->with('error', trans('admin/hardware/message.requests.error'));
        } catch (AuthorizationException $e) {
            return redirect()->back()->with('error', trans('general.insufficient_permissions'));
        } catch (\App\Exceptions\DuplicateCheckoutRequest $e) {
            return redirect()->back()->with('error', trans('admin/hardware/message.requests.duplicate'));
        }

        return $this->redirectAfterRequestAction($request, 'admin/hardware/message.requests.success');
    }

    /**
     * Process a specific requested asset
     */
    public function store(Request $request, Asset $asset): RedirectResponse
    {
        try {
            CreateCheckoutRequestAction::run($asset, auth()->user());

            $redirect = redirect()->route('account.requestable')
                ->with('success', trans('admin/hardware/message.requests.success'));
            if ($tab = $this->safeActiveTab($request->input('active_tab'))) {
                $redirect->withFragment($tab);
            }

            return $redirect;
        } catch (ItemNotRequestable $e) {
            return redirect()->back()->with('error', 'Asset is not requestable');
        } catch (\App\Exceptions\DuplicateCheckoutRequest $e) {
            return redirect()->back()->with('error', trans('admin/hardware/message.requests.duplicate'));
        } catch (AuthorizationException $e) {
            return redirect()->back()->with('error', trans('admin/hardware/message.requests.error'));
        } catch (Exception $e) {
            report($e);

            return redirect()->back()->with('error', trans('general.something_went_wrong'));
        }
    }

    public function destroy(Request $request, Asset $asset): RedirectResponse
    {
        try {
            CancelCheckoutRequestAction::run($asset, auth()->user());

            $redirect = redirect()->route('account.requestable')
                ->with('success', trans('admin/hardware/message.requests.canceled'));
            if ($tab = $this->safeActiveTab($request->input('active_tab'))) {
                $redirect->withFragment($tab);
            }

            return $redirect;
        } catch (\App\Exceptions\NoActiveCheckoutRequest $e) {
            return redirect()->back()->with('error', trans('admin/hardware/message.requests.no_active'));
        } catch (Exception $e) {
            report($e);

            return redirect()->back()->with('error', trans('general.something_went_wrong'));
        }
    }

    public function getRequestedAssets(): View
    {
        return view('account/requested');
    }

    /**
     * Validate the client-supplied `active_tab` hint against the
     * known tab ids on /account/requestable. Returns the tab
     * id when it's on the allowlist, null otherwise. Bootstrap 3
     * doesn't restore tab state on a full-page navigation, so the
     * caller passes this into RedirectResponse::withFragment() plus
     * a small hash->tab activator on the destination view keeps
     * the requester on the tab they submitted from. Only known tab
     * ids are honored so an arbitrary `active_tab` value can't
     * inject something unexpected into the redirect fragment.
     */
    private function safeActiveTab(?string $tab): ?string
    {
        $allowed = ['assets', 'models', 'accessories', 'consumables', 'components', 'licenses'];

        return in_array($tab, $allowed, true) ? $tab : null;
    }
}
