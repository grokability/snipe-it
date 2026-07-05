<?php

namespace App\Http\Controllers\Api;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Transformers\ActionlogsTransformer;
use App\Http\Transformers\LicenseSeatsTransformer;
use App\Http\Transformers\LicensesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Asset;
use App\Models\Company;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LicensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function index(FilterRequest $request): JsonResponse|array
    {
        $this->authorize('view', License::class);

        $licenses = License::with('company', 'manufacturer', 'supplier', 'category', 'adminuser', 'licenseSeatsRelation', 'assignedCount')
            ->withCount([
                'freeSeats as free_seats_count',
                'licenseseats as unreassignable_seats_count' => fn ($q) => $q->where('unreassignable_seat', true),
            ]);
        $settings = Setting::getSettings();

        if ($request->input('status') == 'inactive') {
            $licenses->ExpiredLicenses();
        } elseif ($request->input('status') == 'expiring') {
            $licenses->ExpiringLicenses($settings->alert_interval);
        } elseif ($request->input('status') == 'active') {
            $licenses->ActiveLicenses();
        }

        if ($request->filled('company_id')) {
            // expand_company_hierarchy=1 opts the company show-page tabs into the
            // parent/child rollup so a child shows items inherited from its parent.
            if ($request->boolean('expand_company_hierarchy')) {
                $licenses->whereIn('licenses.company_id', Company::reachableCompanyIds($request->input('company_id')));
            } else {
                $licenses->where('licenses.company_id', '=', $request->input('company_id'));
            }
        }

        if ($request->filled('name')) {
            $licenses->where('licenses.name', '=', $request->input('name'));
        }

        if ($request->filled('product_key')) {
            $licenses->where('licenses.serial', '=', $request->input('product_key'));
        }

        if ($request->filled('order_number')) {
            $licenses->where('order_number', '=', $request->input('order_number'));
        }

        if ($request->filled('purchase_order')) {
            $licenses->where('purchase_order', '=', $request->input('purchase_order'));
        }

        if ($request->filled('license_name')) {
            $licenses->where('license_name', '=', $request->input('license_name'));
        }

        if ($request->filled('license_email')) {
            $licenses->where('license_email', '=', $request->input('license_email'));
        }

        if ($request->filled('manufacturer_id')) {
            $licenses->where('manufacturer_id', '=', $request->input('manufacturer_id'));
        }

        if ($request->filled('supplier_id')) {
            $licenses->where('supplier_id', '=', $request->input('supplier_id'));
        }

        if ($request->filled('category_id')) {
            $licenses->where('category_id', '=', $request->input('category_id'));
        }

        if ($request->filled('depreciation_id')) {
            $licenses->where('depreciation_id', '=', $request->input('depreciation_id'));
        }

        if ($request->filled('created_by')) {
            $licenses->where('created_by', '=', $request->input('created_by'));
        }

        if (($request->filled('maintained')) && ($request->input('maintained') == 'true')) {
            $licenses->where('maintained', '=', 1);
        } elseif (($request->filled('maintained')) && ($request->input('maintained') == 'false')) {
            $licenses->where('maintained', '=', 0);
        }

        if (($request->filled('expires')) && ($request->input('expires') == 'true')) {
            $licenses->whereNotNull('expiration_date');
        } elseif (($request->filled('expires')) && ($request->input('expires') == 'false')) {
            $licenses->whereNull('expiration_date');
        }

        // This invokes the Searchable model trait and will handle input by search or by advanced search filter
        if ($request->filled('filter') || $request->filled('search')) {
            $licenses->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
        }

        if ($request->input('deleted') == 'true') {
            $licenses->onlyTrashed();
        }

        $total = $licenses->count();

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';

        switch ($request->input('sort')) {
            case 'manufacturer':
                $licenses = $licenses->leftJoin('manufacturers', 'licenses.manufacturer_id', '=', 'manufacturers.id')->orderBy('manufacturers.name', $order);
                break;
            case 'supplier':
                $licenses = $licenses->leftJoin('suppliers', 'licenses.supplier_id', '=', 'suppliers.id')->orderBy('suppliers.name', $order);
                break;
            case 'category':
                $licenses = $licenses->leftJoin('categories', 'licenses.category_id', '=', 'categories.id')->orderBy('categories.name', $order);
                break;
            case 'depreciation':
                $licenses = $licenses->leftJoin('depreciations', 'licenses.depreciation_id', '=', 'depreciations.id')->orderBy('depreciations.name', $order);
                break;
            case 'company':
                $licenses = $licenses->leftJoin('companies', 'licenses.company_id', '=', 'companies.id')->orderBy('companies.name', $order);
                break;
            case 'created_by':
                $licenses = $licenses->OrderByCreatedBy($order);
                break;
            case 'product_key':
                $licenses = $licenses->orderBy('licenses.serial', $order);
                break;
            default:
                $allowed_columns =
                    [
                        'category',
                        'company',
                        'created_at',
                        'depreciation_id',
                        'expiration_date',
                        'free_seats_count',
                        'id',
                        'license_email',
                        'license_name',
                        'maintained',
                        'min_amt',
                        'name',
                        'notes',
                        'order_number',
                        'purchase_cost',
                        'purchase_date',
                        'purchase_order',
                        'reassignable',
                        'seats',
                        'serial',
                        'termination_date',
                        'updated_at',
                    ];
                $sort = in_array($request->input('sort'), $allowed_columns) ? e($request->input('sort')) : 'created_at';
                $licenses = $licenses->orderBy($sort, $order);
                break;
        }

        $licenses = $licenses->skip($offset)->take($limit)->get();

        return (new LicensesTransformer)->transformLicenses($licenses, $total);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', License::class);
        $license = new License;
        $license->fill($request->all());
        $license->created_by = auth()->id();
        $license->company_id = Company::getIdForCurrentUser($request->input('company_id'));

        if ($license->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $license, trans('admin/licenses/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $license->getErrors()));
    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @param  int  $id
     */
    public function show($id): JsonResponse|array
    {
        $this->authorize('view', License::class);
        $license = License::withCount([
            'freeSeats as free_seats_count',
            'licenseseats as unreassignable_seats_count' => fn ($q) => $q->where('unreassignable_seat', true),
        ])->findOrFail($id);
        $license = $license->load('assignedusers', 'licenseSeats.user', 'licenseSeats.asset');

        return (new LicensesTransformer)->transformLicense($license);
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  int  $id
     */
    public function update(Request $request, $id): JsonResponse|array
    {
        //
        $this->authorize('update', License::class);

        $license = License::findOrFail($id);
        $license->fill($request->all());
        $license->company_id = Company::getIdForCurrentUser($request->input('company_id'));

        if ($license->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $license, trans('admin/licenses/message.update.success')));
        }

        return Helper::formatStandardApiResponse('error', null, $license->getErrors());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  int  $id
     */
    public function destroy($id): JsonResponse
    {
        $license = License::findOrFail($id);
        $this->authorize('delete', $license);

        if ($license->assigned_seats_count == 0) {
            // Delete the license and the associated license seats
            DB::table('license_seats')
                ->where('license_id', $license->id)
                ->update(['assigned_to' => null, 'asset_id' => null]);

            $licenseSeats = $license->licenseseats();
            $licenseSeats->delete();
            $license->delete();

            // Redirect to the licenses management page
            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/licenses/message.delete.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.assoc_users')));
    }

    /**
     * Checkout a license seat to a user or asset.
     *
     * Accepts an optional `seat_id`; if omitted the next available free seat is used.
     * `target_type` must be "user" or "asset". Supply `assigned_to` for users or
     * `asset_id` for assets.
     *
     * This will eventually use the same form request the UI uses, but we need to update the field names first.
     *
     * @param  int  $licenseId
     */
    public function checkout(Request $request, $licenseId): JsonResponse
    {
        $license = License::findOrFail($licenseId);
        $this->authorize('checkout', $license);

        $validated = $this->validate($request, [
            'seat_id' => 'sometimes|integer|nullable',
            'target_type' => 'required|in:user,asset',
            'assigned_to' => 'required_if:target_type,user|integer|nullable',
            'asset_id' => 'required_if:target_type,asset|integer|nullable',
            'notes' => 'sometimes|string|nullable',
        ]);

        if ($license->isInactive()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.checkout.license_is_inactive')));
        }

        $errorResponse = null;
        $updatedSeat = null;
        $target = null;

        DB::transaction(function () use ($license, $validated, &$errorResponse, &$updatedSeat, &$target): void {
            $seatId = $validated['seat_id'] ?? null;

            $licenseSeat = $seatId
                ? LicenseSeat::where('id', $seatId)->where('license_id', $license->id)->lockForUpdate()->first()
                : $license->freeSeat(lock: true);

            if (! $licenseSeat) {
                $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.checkout.not_enough_seats')));

                return;
            }

            if ($licenseSeat->unreassignable_seat) {
                $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.checkout.unavailable')));

                return;
            }

            if ($validated['target_type'] === 'user') {
                $target = User::withoutGlobalScopes()->whereNull('deleted_at')->find($validated['assigned_to'] ?? null);
                if (! $target) {
                    $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.user_does_not_exist')));

                    return;
                }

                if (Company::isFullMultipleCompanySupportEnabled() && ! $target->companies()->where('companies.id', $license->company_id)->exists()) {
                    $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, trans('general.error_user_company')));

                    return;
                }

                $licenseSeat->assigned_to = $target->id;
                $licenseSeat->asset_id = null;
            } else {
                $target = Asset::withoutGlobalScopes()->whereNull('deleted_at')->find($validated['asset_id'] ?? null);
                if (! $target) {
                    $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.asset_does_not_exist')));

                    return;
                }

                if (Company::isFullMultipleCompanySupportEnabled() && $license->company_id && $license->company_id !== $target->company_id) {
                    $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, trans('general.error_user_company')));

                    return;
                }

                $licenseSeat->asset_id = $target->id;
                $licenseSeat->assigned_to = null;

                if ($target->checkedOutToUser()) {
                    $licenseSeat->assigned_to = $target->assigned_to;
                }
            }

            $licenseSeat->notes = $validated['notes'] ?? null;
            $licenseSeat->created_by = auth()->id();

            if (! $licenseSeat->save()) {
                $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, $licenseSeat->getErrors()));

                return;
            }

            event(new CheckoutableCheckedOut($licenseSeat, $target, auth()->user(), $validated['notes'] ?? null));
            $updatedSeat = $licenseSeat->load('license', 'user', 'asset');
        });

        if ($errorResponse) {
            return $errorResponse;
        }

        if ($updatedSeat) {
            return response()->json(Helper::formatStandardApiResponse('success', (new LicenseSeatsTransformer)->transformLicenseSeat($updatedSeat), trans('admin/licenses/message.checkout.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, 'An unexpected error occurred'), 500);
    }

    /**
     * Checkin a license seat.
     *
     * `seat_id` is required to identify which seat to check back in.
     *
     * @param  int  $licenseId
     */
    public function checkin(Request $request, $licenseId): JsonResponse
    {
        $license = License::findOrFail($licenseId);
        $this->authorize('checkin', $license);

        $validated = $this->validate($request, [
            'seat_id' => 'required|integer',
            'notes' => 'sometimes|string|nullable',
        ]);

        $licenseSeat = LicenseSeat::where('id', $validated['seat_id'])
            ->where('license_id', $license->id)
            ->first();

        if (! $licenseSeat) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.not_found')));
        }

        if (is_null($licenseSeat->assigned_to) && is_null($licenseSeat->asset_id)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.checkin.error')));
        }

        $target = $licenseSeat->user ?? $licenseSeat->asset;

        $licenseSeat->assigned_to = null;
        $licenseSeat->asset_id = null;
        $licenseSeat->notes = $validated['notes'] ?? null;

        if (! $license->reassignable) {
            $licenseSeat->unreassignable_seat = true;
        }

        if (! $licenseSeat->save()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $licenseSeat->getErrors()));
        }

        event(new CheckoutableCheckedIn($licenseSeat, $target, auth()->user(), $licenseSeat->notes));

        return response()->json(Helper::formatStandardApiResponse('success', (new LicenseSeatsTransformer)->transformLicenseSeat($licenseSeat->load('license', 'user', 'asset')), trans('admin/licenses/message.checkin.success')));
    }

    /**
     * Gets a paginated collection for the select2 menus
     *
     * @see SelectlistTransformer
     */
    public function selectlist(Request $request): array
    {
        $this->authorize('view.selectlists');

        $licenses = License::select([
            'licenses.id',
            'licenses.name',
        ]);

        if ($request->filled('search')) {
            $licenses = $licenses->where('licenses.name', 'LIKE', '%'.$request->input('search').'%');
        }

        $licenses = $licenses->orderBy('name', 'ASC')->paginate(50);

        return (new SelectlistTransformer)->transformSelectlist($licenses);
    }

    public function history(Request $request, License $license): JsonResponse|array
    {
        $this->authorize('history', $license);
        $historyQuery = $license->getHistory($request);
        $total = (clone $historyQuery)->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');
        $history = (clone $historyQuery)->skip($offset)->take($limit)->get();

        return response()->json((new ActionlogsTransformer)->transformActionlogs($history, $total), 200, ['Content-Type' => 'application/json;charset=utf8'], JSON_UNESCAPED_UNICODE);
    }
}
