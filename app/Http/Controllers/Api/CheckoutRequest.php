<?php

namespace App\Http\Controllers\Api;

use App\Actions\CheckoutRequests\CancelCheckoutRequestAction;
use App\Actions\CheckoutRequests\CreateCheckoutRequestAction;
use App\Exceptions\DuplicateCheckoutRequest;
use App\Exceptions\ItemNotRequestable;
use App\Exceptions\NoActiveCheckoutRequest;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\CheckoutRequestsTransformer;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CheckoutRequest as CheckoutRequestModel;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\RequestAssetCancelation;
use App\Notifications\RequestAssetNotification;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutRequest extends Controller
{
    /**
     * Admin-scoped list of open (not-yet-canceled) checkout requests
     * across all users. Mirrors the /requests Blade view so
     * integrators can build their own approval / fulfilment UI (kiosk,
     * mobile app, chat bot) against the same data set.
     *
     * FMCS scoping matches the Blade side exactly: rows are loaded, then
     * a post-hoc Company::isCurrentUserHasAccess filter runs for
     * non-superusers. That is O(rows) work rather than a whereHasMorph
     * at the DB layer, but the request table stays small in practice
     * and this behavior is what admins already see on the web page. If
     * request volume ever justifies it, pushing the filter into a
     * whereHasMorph across Asset/Accessory/Consumable/Component would
     * move the cost back to the database.
     */
    public function index(Request $request): array
    {
        // Endpoint-level gate: allow through when the caller can
        // checkout at least one of the five checkoutable types.
        // The canCheckoutAtLeastOneItemType gate (see AuthServiceProvider)
        // wraps that OR - reused here + on the nav link in
        // layouts/default.blade.php so both surfaces answer the
        // "should this user see /requests at all?" question
        // identically.
        $this->authorize('canCheckoutAtLeastOneItemType');

        $user = auth()->user();

        $query = CheckoutRequestModel::with('user', 'requestedItem')
            ->pending();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('requestable_type')) {
            $query->where('requestable_type', $request->input('requestable_type'));
        }

        // ?search= hits notes + requester (Searchable trait's
        // standard shape) AND the polymorphic requestable's name /
        // location / category / company (via the model's
        // advancedTextSearch override which whereHasMorphs across
        // every requestable type). All at the DB layer, so search
        // narrows the row set before the sort / FMCS filter below.
        if ($request->filled('search')) {
            $query->TextSearch($request->input('search'));
        }

        // Pull the (search-narrowed) set. The sort happens in PHP after
        // the FMCS filter below since the controller loads all-then-
        // slice anyway. This also lets `remaining` sort work
        // uniformly across the polymorphic requestable types (each
        // computes numRemaining() differently, so a single DB
        // orderBy wouldn't cover them). Default sort: most recent
        // first, which matches the previous behavior when no client-side
        // header is chosen.
        $requests = $query->get();

        if (Company::isFullMultipleCompanySupportEnabled() && ! auth()->user()->isSuperUser()) {
            $requests = $requests->filter(function (CheckoutRequestModel $r): bool {
                if (! $r->requestable || ! Company::isCurrentUserHasAccess($r->requestable)) {
                    return false;
                }

                // AssetModel is a global object with no
                // company_id column, so Company::isCurrentUserHasAccess
                // short-circuits to true for every actor. Fall back to
                // the requesting user's tenant visibility, which
                // routes through User's CompanyableScope and correctly
                // rejects cross-company users.
                if ($r->requestable_type === AssetModel::class) {
                    return $r->user !== null && Company::isCurrentUserHasAccess($r->user);
                }

                return true;
            })->values();
        }

        // Per-row checkout-permission filter. A caller who can
        // checkout accessories but not assets sees accessory rows
        // and no asset rows; the endpoint-level gate above only
        // checks "any checkoutable type at all", so this second
        // pass narrows to what they can actually fulfill.
        // AssetModel rows map back to Asset for the check because
        // fulfillment is via asset checkout. Skipped for superusers
        // since Gate short-circuits true for them anyway.
        if (! $user->isSuperUser()) {
            $requests = $requests->filter(function (CheckoutRequestModel $r) use ($user): bool {
                if (! $r->requestable_type) {
                    return false;
                }
                $permissionType = $r->requestable_type === AssetModel::class
                    ? Asset::class
                    : $r->requestable_type;

                return $user->can('checkout', $permissionType);
            })->values();
        }

        // Whitelist of sort keys mapped to the value extractor used
        // for the in-PHP sort. Any other value the client sends is
        // ignored - defends against a bs-tables regression that would
        // otherwise let a caller sort by an unexpected attribute
        // (raw property access on a polymorphic morphTo would throw
        // on the first row where the attribute doesn't exist).
        $sortKey = $request->input('sort', 'requested_at');
        $direction = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sorters = [
            'requested_at' => fn (CheckoutRequestModel $r) => $r->created_at,
            'start_date' => fn (CheckoutRequestModel $r) => $r->start_date,
            'end_date' => fn (CheckoutRequestModel $r) => $r->end_date,
            'quantity' => fn (CheckoutRequestModel $r) => $r->quantity,
            'requestable.remaining' => fn (CheckoutRequestModel $r) => method_exists($r->requestable, 'numRemaining')
                ? $r->requestable->numRemaining()
                : null,
        ];
        $sorter = $sorters[$sortKey] ?? $sorters['requested_at'];
        $requests = $direction === 'asc'
            ? $requests->sortBy($sorter)->values()
            : $requests->sortByDesc($sorter)->values();

        $total = $requests->count();
        $offset = (int) $request->input('offset', 0);
        $limit = min((int) $request->input('limit', 50), 500);
        $page = $requests->slice($offset, $limit)->values();

        return (new CheckoutRequestsTransformer)->transformCheckoutRequests($page, $total);
    }

    public function store(Asset $asset): JsonResponse
    {
        try {
            CreateCheckoutRequestAction::run($asset, auth()->user());

            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/hardware/message.requests.success')));
        } catch (ItemNotRequestable $e) {
            return response()->json(Helper::formatStandardApiResponse('error', 'Asset is not requestable'));
        } catch (DuplicateCheckoutRequest $e) {
            return response()->json(
                Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.requests.duplicate')),
                409,
            );
        } catch (AuthorizationException $e) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.insufficient_permissions')));
        } catch (Exception $e) {
            report($e);

            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.something_went_wrong')));
        }
    }

    public function destroy(Asset $asset): JsonResponse
    {
        try {
            CancelCheckoutRequestAction::run($asset, auth()->user());

            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/hardware/message.requests.canceled')));
        } catch (NoActiveCheckoutRequest $e) {
            return response()->json(
                Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.requests.no_active')),
                404,
            );
        } catch (AuthorizationException $e) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.insufficient_permissions')));
        } catch (Exception $e) {
            report($e);

            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.something_went_wrong')));
        }
    }

    public function storeConsumable(Consumable $consumable): JsonResponse
    {
        return $this->createRequestFor($consumable);
    }

    public function destroyConsumable(Consumable $consumable): JsonResponse
    {
        return $this->cancelRequestFor($consumable);
    }

    public function storeComponent(Component $component): JsonResponse
    {
        return $this->createRequestFor($component);
    }

    public function destroyComponent(Component $component): JsonResponse
    {
        return $this->cancelRequestFor($component);
    }

    public function storeLicense(License $license): JsonResponse
    {
        return $this->createRequestFor($license);
    }

    public function destroyLicense(License $license): JsonResponse
    {
        return $this->cancelRequestFor($license);
    }

    /**
     * Shared create-request flow for Consumable + Component + License.
     * Delegates to CreateCheckoutRequestAction, which owns the uniform
     * actionlog + notification path across every requestable type
     * (and gates the Asset-specific requests_counter increment).
     */
    private function createRequestFor($requestable): JsonResponse
    {
        try {
            CreateCheckoutRequestAction::run($requestable, auth()->user());
        } catch (ItemNotRequestable $e) {
            return response()->json(
                Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.requests.error')),
                403,
            );
        } catch (AuthorizationException $e) {
            return response()->json(
                Helper::formatStandardApiResponse('error', null, trans('general.insufficient_permissions')),
                403,
            );
        } catch (DuplicateCheckoutRequest $e) {
            return response()->json(
                Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.requests.duplicate')),
                409,
            );
        } catch (Exception $e) {
            report($e);

            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.something_went_wrong')));
        }

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/hardware/message.requests.success')));
    }

    /**
     * Shared cancel flow for Consumable + Component (self-cancel by
     * the currently authenticated requester). FMCS scoping falls out
     * of the model's global CompanyableScope on the reachable-check
     * inside the notification firing.
     */
    private function cancelRequestFor($requestable): JsonResponse
    {
        try {
            if (! Company::isCurrentUserHasAccess($requestable)) {
                return response()->json(
                    Helper::formatStandardApiResponse('error', null, trans('general.insufficient_permissions')),
                    403,
                );
            }

            $user = auth()->user();
            $affected = $requestable->cancelRequest($user->id);

            if ($affected === 0) {
                return response()->json(
                    Helper::formatStandardApiResponse('error', null, trans('admin/hardware/message.requests.no_active')),
                    404,
                );
            }

            $this->fireRequestNotification($requestable, $user, 'canceled');

            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/hardware/message.requests.canceled')));
        } catch (Exception $e) {
            report($e);

            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.something_went_wrong')));
        }
    }

    /**
     * Fire the admin-alert notification for a request event. Failures
     * are logged but don't fail the request, matching the tolerance
     * the web-side ViewAssetsController::getRequestItem already has
     * for a broken mail transport.
     */
    private function fireRequestNotification($requestable, User $user, string $action): void
    {
        $settings = Setting::getSettings();
        if ($settings->alert_email == '' || $settings->alerts_enabled != '1' || config('app.lock_passwords')) {
            return;
        }

        $data = [
            'item' => $requestable,
            'item_type' => Helper::shortTypeName($requestable),
            'item_quantity' => 1,
            'target' => $user,
            'requested_by' => $user->display_name,
            'requested_date' => date('Y-m-d H:i:s'),
        ];

        try {
            $notification = $action === 'canceled'
                ? new RequestAssetCancelation($data)
                : new RequestAssetNotification($data);
            $settings->notify($notification->locale($settings->locale));
        } catch (Exception $e) {
            Log::warning('Could not send checkout-request notification: '.$e->getMessage());
        }
    }
}
