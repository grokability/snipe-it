<?php

namespace App\Http\Controllers\Api;

use App\Actions\CheckoutRequests\CancelCheckoutRequestAction;
use App\Actions\CheckoutRequests\CreateCheckoutRequestAction;
use App\Exceptions\AssetNotRequestable;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Exception;

class CheckoutRequest extends Controller
{
    public function store(Asset $asset): JsonResponse
    {
        try {
            CreateCheckoutRequestAction::run($asset, auth()->user());
            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/hardware/message.requests.success')));
        } catch (AssetNotRequestable $e) {
            return response()->json(Helper::formatStandardApiResponse('error', 'Asset is not requestable'));
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
        } catch (AuthorizationException $e) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.insufficient_permissions')));
        } catch (Exception $e) {
            report($e);
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.something_went_wrong')));
        }
    }
}
