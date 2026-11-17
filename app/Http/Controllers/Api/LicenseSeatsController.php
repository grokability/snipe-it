<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\MissingLogTarget;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\LicenseSeatsTransformer;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class LicenseSeatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  int  $licenseId
     */
    public function index(Request $request, $licenseId): JsonResponse|array
    {

        if ($license = License::find($licenseId)) {
            $this->authorize('view', $license);

            $seats = LicenseSeat::with('license', 'user', 'asset', 'user.department', 'user.companies', 'asset.company')
                ->where('license_seats.license_id', $licenseId);

            if ($request->input('status') == 'available') {
                $seats->whereNull('license_seats.assigned_to')->whereNull('license_seats.asset_id');
            }

            if ($request->input('status') == 'assigned') {
                $seats->ByAssigned();
            }

            if ($request->filled('search')) {
                $seats->TextSearch($request->input('search'));
            }

            $order = $request->input('order') === 'asc' ? 'asc' : 'desc';

            if ($request->input('sort') == 'assigned_user.department') {
                $seats->OrderDepartments($order);
            } elseif ($request->input('sort') == 'assigned_user.company') {
                $seats->OrderCompany($order);
            } else {
                $seats->orderBy('updated_at', $order);
            }

            // tie-breaker for stabling pagination with duplicate sort values
            $seats->orderBy('license_seats.id', $order);

            $total = $seats->count();

            // Make sure the offset and limit are actually integers and do not exceed system limits
            $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');

            if ($offset >= $total) {
                $offset = 0;
            }

            $limit = app('api_limit_value');

            $seats = $seats->skip($offset)->take($limit)->get();

            if ($seats) {
                return (new LicenseSeatsTransformer)->transformLicenseSeats($seats, $total);
            }
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.does_not_exist')), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $licenseId
     * @param  int  $seatId
     */
    public function show($licenseId, $seatId): JsonResponse|array
    {

        $this->authorize('view', License::class);

        if ($licenseSeat = LicenseSeat::where('license_id', $licenseId)->find($seatId)) {
            return (new LicenseSeatsTransformer)->transformLicenseSeat($licenseSeat);
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, 'Seat ID or license not found or the seat does not belong to this license'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $licenseId
     * @param  int  $seatId
     */
    public function update(Request $request, $licenseId, $seatId): JsonResponse|array
    {
        $validated = $this->validate($request, [
            'assigned_to' => [
                'sometimes',
                'int',
                'nullable',
                'prohibits:asset_id',
                // must be a valid user or null to unassign
                function ($attribute, $value, $fail) {
                    // Validate existence without company scopes; FMCS checks happen explicitly below.
                    if (! is_null($value) && ! User::withoutGlobalScopes()->where('id', $value)->whereNull('deleted_at')->exists()) {
                        $fail('The selected assigned_to is invalid.');
                    }
                },
            ],
            'asset_id' => [
                'sometimes',
                'int',
                'nullable',
                'prohibits:assigned_to',
                // must be a valid asset or null to unassign
                function ($attribute, $value, $fail) {
                    // Validate existence without company scopes; FMCS checks happen explicitly below.
                    if (! is_null($value) && ! Asset::withoutGlobalScopes()->where('id', $value)->whereNull('deleted_at')->exists()) {
                        $fail('The selected asset_id is invalid.');
                    }
                },
            ],
            'notes' => 'sometimes|string|nullable',
        ]);

        $this->authorize('checkout', License::class);

        $errorResponse = null;
        $updatedSeat = null;

        // Fetch the seat with a pessimistic lock inside a transaction so concurrent requests
        // on the same seat serialise rather than racing to overwrite each other's assignment.
        try {
            DB::transaction(function () use ($request, $licenseId, $seatId, $validated, &$errorResponse, &$updatedSeat): void {
                $licenseSeat = LicenseSeat::with(['license', 'asset', 'user'])
                    ->lockForUpdate()
                    ->find($seatId);

                if (! $licenseSeat) {
                    $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, 'Seat not found'));

                    return;
                }

                $license = $licenseSeat->license;
                if (! $license || $license->id != intval($licenseId)) {
                    $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, 'Seat does not belong to the specified license'));

                    return;
                }

                $targetUser = null;
                if (! is_null($request->input('assigned_to'))) {
                    // Resolve unscoped target so we can return a clean cross-company error instead of a hidden-not-found.
                    $targetUser = User::withoutGlobalScopes()->find($request->input('assigned_to'));

                    if (! $targetUser) {
                        $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, 'Target not found'));

                        return;
                    }

                    if ((Setting::getSettings()->full_multiple_companies_support == '1') && (! $targetUser->companies()->where('companies.id', $license->company_id)->exists())) {
                        $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, trans('general.error_user_company')));

                        return;
                    }
                }

                $targetAsset = null;
                if (! is_null($request->input('asset_id'))) {
                    // Resolve unscoped target so FMCS company mismatch can be enforced explicitly.
                    $targetAsset = Asset::withoutGlobalScopes()->find($request->input('asset_id'));

                    if (! $targetAsset) {
                        $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, 'Target not found'));

                        return;
                    }

                    if ((Setting::getSettings()->full_multiple_companies_support == '1') && ($license->company_id !== $targetAsset->company_id)) {
                        $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, trans('general.error_user_company')));

                        return;
                    }
                }

                $oldUser = $licenseSeat->user;
                $oldAsset = $licenseSeat->asset;

                $licenseSeat->fill($validated);

                $assignmentTouched = $licenseSeat->isDirty('assigned_to') || $licenseSeat->isDirty('asset_id');
                $anythingTouched = $licenseSeat->isDirty();

                if (! $anythingTouched) {
                    $updatedSeat = $licenseSeat;

                    return;
                }

                if ($assignmentTouched && $licenseSeat->unreassignable_seat) {
                    $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.checkout.unavailable')));

                    return;
                }

                // Are the assignment fields cleared? If yes, this is a checkin operation.
                $is_checkin = ($assignmentTouched && $licenseSeat->assigned_to === null && $licenseSeat->asset_id === null);

                // The method-level authorize() above requires licenses.checkout
                // because most calls to this endpoint reassign a seat. When the
                // update instead clears the assignment (a checkin), require the
                // dedicated licenses.checkin permission too. Gate on the
                // specific license so FMCS company scoping runs too (a caller
                // in a different company shouldn't be able to free a seat on
                // this one even with the permission bit).
                if ($is_checkin && ! Gate::allows('checkin', $license)) {
                    $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, trans('general.unauthorized')), 403);

                    return;
                }

                // The logging functions expect only one "target"; assets take precedence over users.
                $target = null;
                if ($licenseSeat->isDirty('assigned_to')) {
                    $target = $is_checkin ? $oldUser : $targetUser;
                }
                if ($licenseSeat->isDirty('asset_id')) {
                    $target = $is_checkin ? $oldAsset : $targetAsset;
                }

                if ($assignmentTouched && is_null($target)) {
                    // Both fields are null but one was provided — the related model is purged or bad data.
                    if (! is_null($request->input('asset_id')) || ! is_null($request->input('assigned_to'))) {
                        $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, 'Target not found'));

                        return;
                    }
                }

                if (! $licenseSeat->save()) {
                    $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, $licenseSeat->getErrors()));

                    return;
                }

                if ($assignmentTouched) {
                    if ($is_checkin) {
                        if (! $licenseSeat->license->reassignable) {
                            $licenseSeat->unreassignable_seat = true;

                            if (! $licenseSeat->save()) {
                                $errorResponse = response()->json(Helper::formatStandardApiResponse('error', null, $licenseSeat->getErrors()));

                                return;
                            }
                        }

                        $licenseSeat->logCheckin($target, $licenseSeat->notes);
                    } else {
                        $licenseSeat->logCheckout($request->input('notes'), $target);
                    }
                }

                $updatedSeat = $licenseSeat;
            });
        } catch (MissingLogTarget $e) {
            // Loggable trait fell through its target check inside the transaction.
            // The transaction has already rolled back (DB::transaction rethrows on
            // exception) so no seat assignment persisted. Downgrade the surfaced
            // 500 to a 4xx response body the client can act on, and warning-log
            // so we still see systemic regressions in Rollbar without alerting as
            // an unhandled exception.
            Log::warning('logCheckout target validation failed during license seat update.', [
                'license_id' => $licenseId,
                'seat_id' => $seatId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(Helper::formatStandardApiResponse('error', null, 'Target not found'), 422);
        }

        if ($errorResponse) {
            return $errorResponse;
        }

        if ($updatedSeat) {
            return response()->json(Helper::formatStandardApiResponse('success', $updatedSeat, trans('admin/licenses/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, 'An unexpected error occurred'), 500);
    }
}
