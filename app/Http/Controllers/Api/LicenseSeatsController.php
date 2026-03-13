<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\LicenseSeatsTransformer;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseSeatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $licenseId
     */
    public function index(Request $request, $licenseId) : JsonResponse | array
    {

        if ($license = License::find($licenseId)) {
            $this->authorize('view', $license);

            $seats = LicenseSeat::with('license', 'user', 'asset', 'user.department',  'user.company', 'asset.company')
                ->where('license_seats.license_id', $licenseId);

            if ($request->input('status') == 'available') {
                $seats->whereNull('license_seats.assigned_to')->whereNull('license_seats.asset_id');
            }

            if ($request->input('status') == 'assigned') {
                $seats->ByAssigned();
            }


            $order = $request->input('order') === 'asc' ? 'asc' : 'desc';

            if ($request->input('sort') == 'assigned_user.department') {
                $seats->OrderDepartments($order);
            } elseif ($request->input('sort') == 'assigned_user.company') {
                    $seats->OrderCompany($order);
            } else {
                $seats->orderBy('updated_at', $order);
            }

            $total = $seats->count();

            // Make sure the offset and limit are actually integers and do not exceed system limits
            $offset = ($request->input('offset') > $seats->count()) ? $seats->count() : app('api_offset_value');

            if ($offset >= $total ){
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
    public function show($licenseId, $seatId) : JsonResponse | array
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $licenseId
     * @param  int  $seatId
     */
    public function update(Request $request, $licenseId, $seatId) : JsonResponse | array
    {
        $validated = $this->validate($request, [
            'assigned_to' => [
                'sometimes',
                'int',
                'nullable',
                'prohibits:asset_id',
                // must be a valid user or null to unassign
                function ($attribute, $value, $fail) {
                    if (!is_null($value) && !User::where('id', $value)->whereNull('deleted_at')->exists()) {
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
                    if (!is_null($value) && !Asset::where('id', $value)->whereNull('deleted_at')->exists()) {
                        $fail('The selected asset_id is invalid.');
                    }
                },
            ],
            'notes' => 'sometimes|string|nullable',
        ]);

        $this->authorize('checkout', License::class);

        $licenseSeat = LicenseSeat::with(['license', 'asset', 'user'])->find($seatId);

        if (!$licenseSeat) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Seat not found'));
        }

        $license = $licenseSeat->license;
        if (!$license || $license->id != intval($licenseId)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Seat does not belong to the specified license'));
        }

        $oldUser = $licenseSeat->user;
        $oldAsset = $licenseSeat->asset;

        // attempt to update the license seat
        $licenseSeat->fill($validated);

        // check if this update is a checkin operation
        // 1. are relevant fields touched at all?
        $assignmentTouched = $licenseSeat->isDirty('assigned_to') || $licenseSeat->isDirty('asset_id');
        $anythingTouched = $licenseSeat->isDirty();

        if (! $anythingTouched) {
            return response()->json(
                Helper::formatStandardApiResponse('success', $licenseSeat, trans('admin/licenses/message.update.success'))
            );
        }
        if( $assignmentTouched && $licenseSeat->unreassignable_seat) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.checkout.unavailable')));
        }

        // 2. are they cleared? if yes then this is a checkin operation
        $is_checkin = ($assignmentTouched && $licenseSeat->assigned_to === null && $licenseSeat->asset_id === null);
        $target = null;

        // the logging functions expect only one "target". if both asset and user are present in the request,
        // we simply let assets take precedence over users...
        if ($licenseSeat->isDirty('assigned_to')) {
            $target = $is_checkin ? $oldUser : User::find($licenseSeat->assigned_to);
        }

        if ($licenseSeat->isDirty('asset_id')) {
            $target = $is_checkin ? $oldAsset : Asset::find($licenseSeat->asset_id);
        }

        if ($assignmentTouched && is_null($target)){
            // if both asset_id and assigned_to are null then we are "checking-in"
            // a related model that does not exist (possible purged or bad data).
            if (!is_null($request->input('asset_id')) || !is_null($request->input('assigned_to'))) {
                return response()->json(Helper::formatStandardApiResponse('error', null, 'Target not found'));
            }
        }

        if ($licenseSeat->save()) {
            if($assignmentTouched) {
                if ($is_checkin) {
                    if (!$licenseSeat->license->reassignable) {
                        $licenseSeat->unreassignable_seat = true;
                        $licenseSeat->save();
                    }
                    // todo: skip if target is null?
                    $licenseSeat->logCheckin($target, $licenseSeat->notes);
                } else {
                    // in this case, relevant fields are touched but it's not a checkin operation. so it must be a checkout operation.
                    $licenseSeat->logCheckout($request->input('notes'), $target);
                }
            }
            return response()->json(Helper::formatStandardApiResponse('success', $licenseSeat, trans('admin/licenses/message.update.success')));
        }

        return Helper::formatStandardApiResponse('error', null, $licenseSeat->getErrors());
    }
}
