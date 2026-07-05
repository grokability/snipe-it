<?php

namespace App\Http\Controllers\Api;

use App\Enums\ActionType;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Transformers\ActionlogsTransformer;
use App\Http\Transformers\MaintenancesTransformer;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     *
     * @version v1.0
     *
     * @since [v1.8]
     */
    public function index(FilterRequest $request): JsonResponse|array
    {
        $this->authorize('view', Asset::class);

        $maintenances = Maintenance::select('maintenances.*')
            ->whereHas('asset')
            ->with('asset', 'asset.model', 'asset.location', 'asset.defaultLoc', 'supplier', 'asset.company', 'asset.status', 'adminuser', 'asset.assignedTo', 'maintenanceType', 'responsibleParty', 'completedByUser');

        // This invokes the Searchable model trait scopeTextSearch and will handle input by search or by advanced search filter
        if ($request->filled('filter') || $request->filled('search')) {
            $maintenances->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
        }

        if ($request->filled('asset_id')) {
            $maintenances->where('asset_id', '=', $request->input('asset_id'));
        }

        // Polymorphic filter — used by the user detail Maintenances tab to
        // pull every maintenance where the underlying asset was checked out
        // to a specific user. Defaults to type=User when only the id is
        // supplied; pass `checked_out_to_type=<fqcn>` to target locations
        // or assets if/when those tabs land.
        if ($request->filled('checked_out_to_id')) {
            $type = $request->input('checked_out_to_type', User::class);
            $maintenances
                ->where('maintenances.checked_out_to_id', $request->input('checked_out_to_id'))
                ->where('maintenances.checked_out_to_type', $type);
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

        if ($request->filled('maintenance_type')) {
            $maintenances->where('maintenance_type', '=', $request->input('maintenance_type'));
        }

        if ($request->filled('maintenance_type_id')) {
            $maintenances->where('maintenance_type_id', '=', $request->input('maintenance_type_id'));
        }

        if ($request->filled('responsible_party_id')) {
            $maintenances->where('responsible_party_id', '=', $request->input('responsible_party_id'));
        }

        if ($request->filled('completed')) {
            if ($request->input('completed') === 'true') {
                $maintenances->completed();
            } else {
                $maintenances->active();
            }
        }

        if ($request->filled('upcoming_status')) {
            $settings = Setting::getSettings();
            switch ($request->input('upcoming_status')) {
                case 'due':
                    $maintenances->dueForCompletion($settings);
                    break;
                case 'overdue':
                    $maintenances->overdueForCompletion();
                    break;
                case 'due-or-overdue':
                    $maintenances->dueOrOverdueForCompletion($settings);
                    break;
            }
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $maintenances->count()) ? $maintenances->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $allowed_columns = [
            'id',
            'name',
            'asset_maintenance_time',
            'cost',
            'start_date',
            'completion_date',
            'completed_at',
            'notes',
            'asset_tag',
            'asset_name',
            'serial',
            'created_by',
            'supplier',
            'location',
            'is_warranty',
            'status_label',
            'model',
            'model_number',
            'maintenance_type',
        ];

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? e($request->input('sort')) : 'created_at';

        switch ($sort) {
            case 'created_by':
                $maintenances = $maintenances->orderByCreatedBy($order);
                break;
            case 'supplier':
                $maintenances = $maintenances->orderBySupplier($order);
                break;
            case 'asset_tag':
                $maintenances = $maintenances->orderByTag($order);
                break;
            case 'asset_name':
                $maintenances = $maintenances->orderByAssetName($order);
                break;
            case 'model':
                $maintenances = $maintenances->orderByAssetModelName($order);
                break;
            case 'model_number':
                $maintenances = $maintenances->orderByAssetModelNumber($order);
                break;
            case 'serial':
                $maintenances = $maintenances->orderByAssetSerial($order);
                break;
            case 'location':
                $maintenances = $maintenances->orderLocationName($order);
                break;
            case 'status_label':
                $maintenances = $maintenances->orderStatusName($order);
                break;
            case 'maintenance_type':
                $maintenances = $maintenances->orderByMaintenanceType($order);
                break;
            case 'completed_at':
                $maintenances = $maintenances->orderByCompletedAt($order);
                break;
            default:
                $maintenances = $maintenances->orderBy($sort, $order);
                break;
        }

        $total = $maintenances->count();
        $maintenances = $maintenances->skip($offset)->take($limit)->get();

        if (request()->input('format') == 'flat') {
            return (new MaintenancesTransformer)->transformMaintenancesFlat($maintenances, $total);
        }

        return (new MaintenancesTransformer)->transformMaintenances($maintenances, $total);

    }

    /**
     *  Validates and stores the new asset maintenance
     *
     * @see MaintenancesController::getCreate() method for the form
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     *
     * @version v1.0
     *
     * @since [v1.8]
     */
    public function store(ImageUploadRequest $request): JsonResponse|array
    {
        $this->authorize('update', Asset::class);

        $isBulk = $request->has('asset_ids');
        $assetIds = $isBulk
            ? array_values(array_filter((array) $request->input('asset_ids')))
            : [$request->input('asset_id')];

        $created = new EloquentCollection;
        $errors = [];

        foreach ($assetIds as $assetId) {
            $asset = Asset::find($assetId);

            if (! $asset) {
                $errors[] = trans('general.item_not_found', ['item_type' => trans('general.asset'), 'id' => $assetId]);

                continue;
            }

            if (! Company::isCurrentUserHasAccess($asset)) {
                $errors[] = trans('general.action_permission_denied', ['item_type' => trans('general.asset'), 'id' => $assetId, 'action' => trans('general.create')]);

                continue;
            }

            $maintenance = new Maintenance;
            $maintenance->fill($request->except(['asset_id', 'asset_ids']));
            $maintenance->asset_id = $assetId;
            $maintenance->created_by = auth()->id();
            $request->handleImages($maintenance);

            if ($maintenance->save()) {
                $created->push($maintenance->fresh());
            } else {
                $errors[] = $maintenance->getErrors();
            }
        }

        if ($isBulk) {
            if ($created->isEmpty()) {
                return response()->json(Helper::formatStandardApiResponse('error', null, count($errors) === 1 ? $errors[0] : $errors));
            }

            return response()->json(Helper::formatStandardApiResponse(
                'success',
                (new MaintenancesTransformer)->transformMaintenances($created, $created->count()),
                trans('admin/maintenances/message.create.success')
            ));
        }

        // Single asset_id path — backward compatible response shape
        if ($created->isNotEmpty()) {
            return response()->json(Helper::formatStandardApiResponse('success', $created->first(), trans('admin/maintenances/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, ! empty($errors) ? $errors[0] : null));
    }

    /**
     *  Validates and stores an update to an asset maintenance
     *
     * @author  A. Gianotto <snipe@snipe.net>
     *
     * @param  int  $id
     * @param  int  $request
     *
     * @version v1.0
     *
     * @since [v4.0]
     */
    public function update(Request $request, $id): JsonResponse|array
    {
        $this->authorize('update', Asset::class);

        if ($maintenance = Maintenance::with('asset')->find($id)) {

            // The asset this maintenance is attached to is not valid or has been deleted
            if (! $maintenance->asset) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.item_not_found', ['item_type' => trans('general.asset'), 'id' => $id])));
            }

            // Can this user manage the existing asset?
            if (! Company::isCurrentUserHasAccess($maintenance->asset)) {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.action_permission_denied', ['item_type' => trans('admin/maintenances/general.maintenance'), 'id' => $id, 'action' => trans('general.edit')])));
            }

            // If the request changes asset_id, verify the new asset is accessible
            if ($request->filled('asset_id') && (int) $request->input('asset_id') !== $maintenance->asset_id) {
                $newAsset = Asset::find($request->input('asset_id'));

                if (! $newAsset) {
                    return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.item_not_found', ['item_type' => trans('general.asset'), 'id' => $request->input('asset_id')])));
                }

                if (! Company::isCurrentUserHasAccess($newAsset)) {
                    return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.action_permission_denied', ['item_type' => trans('general.asset'), 'id' => $request->input('asset_id'), 'action' => trans('general.edit')])), 403);
                }

                $maintenance->fill($request->except('asset_id'));
                $maintenance->asset_id = $newAsset->id;
            } else {
                $maintenance->fill($request->except('asset_id'));
            }

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
     *
     * @param  int  $maintenanceId
     *
     * @version v1.0
     *
     * @since [v4.0]
     */
    public function destroy($maintenanceId): JsonResponse|array
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
     *
     * @param  int  $maintenanceId
     *
     * @version v1.0
     *
     * @since [v4.0]
     */
    public function show($maintenanceId): JsonResponse|array
    {
        $this->authorize('view', Asset::class);
        $maintenance = Maintenance::findOrFail($maintenanceId);
        if (! Company::isCurrentUserHasAccess($maintenance->asset)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'You cannot view a maintenance for that asset'));
        }

        return (new MaintenancesTransformer)->transformMaintenance($maintenance);

    }

    public function complete(Request $request, Maintenance $maintenance): JsonResponse
    {
        $this->authorize('update', Asset::class);

        if (! Company::isCurrentUserHasAccess($maintenance->asset)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.action_permission_denied', ['item_type' => trans('admin/maintenances/general.maintenance'), 'id' => $maintenance->id, 'action' => trans('admin/maintenances/form.mark_complete')])));
        }

        if ($maintenance->completed_at) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/maintenances/form.already_complete')));
        }

        $maintenance->completed_at = now();
        $maintenance->completed_by = auth()->id();
        $maintenance->asset_maintenance_time = (int) $maintenance->created_at->diffInDays(now(), true);
        $maintenance->saveQuietly();

        $logAction = new Actionlog;
        $logAction->item_type = Maintenance::class;
        $logAction->item_id = $maintenance->id;
        $logAction->target_type = Asset::class;
        $logAction->target_id = $maintenance->asset_id;
        $logAction->created_by = auth()->id();
        $logAction->note = $request->input('note');
        $logAction->logaction(ActionType::MaintenanceComplete);

        return response()->json(Helper::formatStandardApiResponse('success', (new MaintenancesTransformer)->transformMaintenance($maintenance->fresh()), trans('admin/maintenances/message.complete.success')));
    }

    public function history(Request $request, Maintenance $maintenance): JsonResponse|array
    {
        $this->authorize('history', $maintenance);
        $historyQuery = $maintenance->getHistory($request);
        $total = (clone $historyQuery)->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');
        $history = (clone $historyQuery)->skip($offset)->take($limit)->get();

        return response()->json((new ActionlogsTransformer)->transformActionlogs($history, $total), 200, ['Content-Type' => 'application/json;charset=utf8'], JSON_UNESCAPED_UNICODE);
    }

    public function notesIndex(Maintenance $maintenance): JsonResponse
    {
        $this->authorize('journal', $maintenance);

        $notes = Actionlog::with('user:id,username')
            ->where('item_type', Maintenance::class)
            ->where('item_id', $maintenance->id)
            ->where('action_type', 'note added')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'created_at', 'note', 'created_by', 'item_id', 'item_type', 'action_type']);

        $notesArray = $notes->map(fn ($note) => [
            'id' => $note->id,
            'created_at' => $note->created_at,
            'note' => $note->note,
            'created_by' => $note->created_by,
            'username' => $note->user?->username,
            'item_id' => $note->item_id,
            'item_type' => $note->item_type,
            'action_type' => $note->action_type,
        ]);

        return response()->json(Helper::formatStandardApiResponse('success', ['notes' => $notesArray, 'maintenance_id' => $maintenance->id]));
    }

    public function notesStore(Request $request, Maintenance $maintenance): JsonResponse
    {
        $this->authorize('update', $maintenance);

        if (! $request->filled('note')) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('validation.required', ['attribute' => 'note'])), 422);
        }

        $logaction = new Actionlog;
        $logaction->item_type = Maintenance::class;
        $logaction->created_by = auth()->id();
        $logaction->item_id = $maintenance->id;
        $logaction->note = $request->input('note');

        if ($logaction->logaction('note added')) {
            return response()->json(Helper::formatStandardApiResponse('success', ['note' => $logaction->note, 'item_id' => $maintenance->id], trans('general.note_added')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, 'Something went wrong'), 500);
    }
}
