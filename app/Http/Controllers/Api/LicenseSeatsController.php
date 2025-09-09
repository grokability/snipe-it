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

            $seats = LicenseSeat::with('license', 'user', 'asset', 'user.department')
                ->where('license_seats.license_id', $licenseId);

            if ($request->input('status') == 'available') {
                $seats->whereNull('license_seats.assigned_to');
            }

            if ($request->input('status') == 'assigned') {
                $seats->ByAssigned();
            }


            $order = $request->input('order') === 'asc' ? 'asc' : 'desc';

            if ($request->input('sort') == 'department') {
                $seats->OrderDepartments($order);
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
        // sanity checks:
        // 1. does the license seat exist?
        if (! $licenseSeat = LicenseSeat::find($seatId)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Seat not found'));
        }
        // 2. does the seat belong to the specified license?
        if (! $license = $licenseSeat->license()->first() || $license->id != intval($licenseId)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Seat does not belong to the specified license'));
        }

        return (new LicenseSeatsTransformer)->transformLicenseSeat($licenseSeat);
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
        $this->authorize('checkout', License::class);


        if (! $licenseSeat = LicenseSeat::find($seatId)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Seat not found'));
        }

        $license = $licenseSeat->license()->first();
        if (!$license || $license->id != intval($licenseId)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Seat does not belong to the specified license'));
        }

        $oldUser = $licenseSeat->user()->first();
        $oldAsset = $licenseSeat->asset()->first();

        // attempt to update the license seat
        $licenseSeat->fill($request->all());
        $licenseSeat->created_by = auth()->id();

        // check if this update is a checkin operation
        // 1. are relevant fields touched at all?
        $touched = $licenseSeat->isDirty('assigned_to') || $licenseSeat->isDirty('asset_id');
        // 2. are they cleared? if yes then this is a checkin operation
        $is_checkin = ($touched && $licenseSeat->assigned_to === null && $licenseSeat->asset_id === null);

        if (! $touched) {
            // nothing to update
            return response()->json(Helper::formatStandardApiResponse('success', $licenseSeat, trans('admin/licenses/message.update.success')));
        }
        if( $touched && $licenseSeat->unreassignable_seat) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/licenses/message.checkout.unavailable')));
        }
        // the logging functions expect only one "target". if both asset and user are present in the request,
        // we simply let assets take precedence over users...
        if ($licenseSeat->isDirty('assigned_to')) {
            $target = $is_checkin ? $oldUser : User::find($licenseSeat->assigned_to);
        }
        if ($licenseSeat->isDirty('asset_id')) {
            $target = $is_checkin ? $oldAsset : Asset::find($licenseSeat->asset_id);
        }

        if (is_null($target)){
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Target not found'));
        }

        if ($licenseSeat->save()) {

            if ($is_checkin) {
                if(!$licenseSeat->license->reassignable){
                    $licenseSeat->unreassignable_seat = true;
                    $licenseSeat->save();
                }
                $licenseSeat->logCheckin($target, $licenseSeat->notes);

                return response()->json(Helper::formatStandardApiResponse('success', $licenseSeat, trans('admin/licenses/message.update.success')));
            }

            // in this case, relevant fields are touched but it's not a checkin operation. so it must be a checkout operation.
            $licenseSeat->logCheckout($request->input('notes'), $target);

            return response()->json(Helper::formatStandardApiResponse('success', $licenseSeat, trans('admin/licenses/message.update.success')));
        }

        return Helper::formatStandardApiResponse('error', null, $licenseSeat->getErrors());
    }
}
