<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Transformers\MaintenancesTransformer;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * This controller handles all actions related to Asset Maintenance for
 * the Snipe-IT Asset Management application.
 *
 * @version    v2.0
 */
class MaintenancesController extends Controller
{

    /**
     *  Generates the JSON response for asset maintenances listing view.
     *
     * @see MaintenancesController::getIndex() method that generates view
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     * @since [v1.8]
     */
    public function index(Request $request) : JsonResponse | array
    {
        $this->authorize('view', Asset::class);

        $maintenances = Maintenance::select('maintenances.*')
            ->with('asset', 'asset.model', 'asset.location', 'asset.defaultLoc', 'supplier', 'asset.company',  'asset.assetstatus', 'adminuser');

        if ($request->filled('search')) {
            $maintenances = $maintenances->TextSearch($request->input('search'));
        }

        if ($request->filled('asset_id')) {
            $maintenances->where('asset_id', '=', $request->input('asset_id'));
        }

        if ($request->filled('supplier_id')) {
            $maintenances->where('maintenances.supplier_id', '=', $request->input('supplier_id'));
        }

        if ($request->filled('created_by')) {
            $maintenances->where('maintenances.created_by', '=', $request->input('created_by'));
        }

        if ($request->filled('url')) {
            $maintenances->where('maintenances.url', '=', $request->input('url'));
        }

        if ($request->filled('asset_maintenance_type')) {
            $maintenances->where('asset_maintenance_type', '=', $request->input('asset_maintenance_type'));
        }


        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $maintenances->count()) ? $maintenances->count() : abs($request->input('offset'));
        $limit = app('api_limit_value');

        $allowed_columns = [
                                'id',
                                'name',
                                'asset_maintenance_time',
                                'asset_maintenance_type',
                                'cost',
                                'start_date',
                                'completion_date',
                                'notes',
                                'asset_tag',
                                'asset_name',
                                'serial',
                                'created_by',
                                'supplier',
                                'location',
                                'is_warranty',
                                'status_label',
                            ];

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? e($request->input('sort')) : 'created_at';

        switch ($sort) {
            case 'created_by':
                $maintenances = $maintenances->OrderByCreatedBy($order);
                break;
            case 'supplier':
                $maintenances = $maintenances->OrderBySupplier($order);
                break;
            case 'asset_tag':
                $maintenances = $maintenances->OrderByTag($order);
                break;
            case 'asset_name':
                $maintenances = $maintenances->OrderByAssetName($order);
                break;
            case 'serial':
                $maintenances = $maintenances->OrderByAssetSerial($order);
                break;
            case 'location':
                $maintenances = $maintenances->OrderLocationName($order);
                break;
            case 'status_label':
                $maintenances = $maintenances->OrderStatusName($order);
                break;
            default:
                $maintenances = $maintenances->orderBy($sort, $order);
                break;
        }

        $total = $maintenances->count();
        $maintenances = $maintenances->skip($offset)->take($limit)->get();
        return (new MaintenancesTransformer())->transformMaintenances($maintenances, $total);


    }


    /**
     *  Validates and stores the new asset maintenance
     *
     * @see MaintenancesController::getCreate() method for the form
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     * @since [v1.8]
     */
    public function store(ImageUploadRequest $request) : JsonResponse | array
    {
        $this->authorize('update', Asset::class);

        // create a new model instance
        $maintenance = new Maintenance();
        $maintenance->fill($request->all());
        $maintenance->created_by = auth()->id();
        $maintenance = $request->handleImages($maintenance);
        // Was the asset maintenance created?
        if ($maintenance->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $maintenance, trans('admin/maintenances/message.create.success')));

        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $maintenance->getErrors()));

    }

    /**
     *  Validates and stores an update to an asset maintenance
     *
     * @author  A. Gianotto <snipe@snipe.net>
     * @param int $id
     * @param int $request
     * @version v1.0
     * @since [v4.0]
     */
    public function update(Request $request, $id) : JsonResponse | array
    {
        $this->authorize('update', Asset::class);

        if ($maintenance = Maintenance::with('asset')->find($id)) {

            // Can this user manage this asset?
            if (! Company::isCurrentUserHasAccess($maintenance->asset)) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.action_permission_denied', ['item_type' => trans('admin/maintenances/general.maintenance'), 'id' => $id, 'action' => trans('general.edit')])));
            }

            // The asset this miantenance is attached to is not valid or has been deleted
            if (!$maintenance->asset) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.item_not_found', ['item_type' => trans('general.asset'), 'id' => $id])));
            }

            $maintenance->fill($request->all());

            if ($maintenance->save()) {
                return response()->json(Helper::formatStandardApiResponse('success', $maintenance, trans('admin/maintenances/message.edit.success')));
            }

            return response()->json(Helper::formatStandardApiResponse('error', null, $maintenance->getErrors()));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.item_not_found', ['item_type' => trans('admin/maintenances/general.maintenance'), 'id' => $id])));

    }

    /**
     *  Delete an asset maintenance
     *
     * @author  A. Gianotto <snipe@snipe.net>
     * @param int $maintenanceId
     * @version v1.0
     * @since [v4.0]
     */
    public function destroy($maintenanceId) : JsonResponse | array
    {
        $this->authorize('update', Asset::class);
        // Check if the asset maintenance exists

        $maintenance = Maintenance::findOrFail($maintenanceId);

        $maintenance->delete();

        return response()->json(Helper::formatStandardApiResponse('success', $maintenance, trans('admin/maintenances/message.delete.success')));


    }

    /**
     *  View an asset maintenance
     *
     * @author  A. Gianotto <snipe@snipe.net>
     * @param int $maintenanceId
     * @version v1.0
     * @since [v4.0]
     */
    public function show($maintenanceId) : JsonResponse | array
    {
        $this->authorize('view', Asset::class);
        $maintenance = Maintenance::findOrFail($maintenanceId);
        if (! Company::isCurrentUserHasAccess($maintenance->asset)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'You cannot view a maintenance for that asset'));
        }

        return (new MaintenancesTransformer())->transformMaintenance($maintenance);

    }
}
