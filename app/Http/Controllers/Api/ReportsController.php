<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Transformers\ActionlogsTransformer;
use App\Models\Actionlog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ReportsController extends Controller
{
    /**
     * Returns Activity Report JSON.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function index(FilterRequest $request): JsonResponse|array
    {

        // If the user doesn't have permission to view the item or the target,
        // then they shouldn't be able to see the activity log for that item or target,
        // but if they have the general activity view permission,
        // then they can see all activity logs regardless of the item or target.
        if ((! Gate::allows('activity.view')) && (($request->filled('target_type')) && ($request->filled('target_id'))) || (($request->filled('item_type')) && ($request->filled('item_id')))) {

            if (($request->filled('target_type')) && ($request->filled('target_id'))) {
                $target = Helper::normalizeFullModelName(request()->input('target_type'));
                $target::find(request()->input('target_id'))?->withTrashed();
                $this->authorize('view', $target);
            }

            if (($request->filled('item_type')) && ($request->filled('item_id'))) {
                $item = Helper::normalizeFullModelName(request()->input('item_type'));
                $item::find(request()->input('item_id'))?->withTrashed();
                $this->authorize('view', $item);
            }

        } else {
            $this->authorize('activity.view');
        }

        $actionlogs = Actionlog::with('item', 'user', 'adminuser', 'target', 'location');

        if (($request->filled('target_type')) && ($request->filled('target_id'))) {
            $actionlogs = $actionlogs->where('target_id', '=', $request->input('target_id'))
                ->where('target_type', '=', Helper::normalizeFullModelName($request->input('target_type')));
        }

        if (($request->filled('item_type')) && ($request->filled('item_id'))) {
            $actionlogs = $actionlogs->where(function ($query) use ($request) {
                $query->where('item_id', '=', $request->input('item_id'))
                    ->where('item_type', '=', Helper::normalizeFullModelName($request->input('item_type')))
                    ->orWhere(function ($query) use ($request) {
                        $query->where('target_id', '=', $request->input('item_id'))
                            ->where('target_type', '=', Helper::normalizeFullModelName($request->input('item_type')));
                    });
            });
        }

        // This invokes the Searchable model trait scopeTextSearch and will handle input by search or by advanced search filter
        if ($request->filled('filter') || $request->filled('search')) {
            $actionlogs->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
        }

        if ($request->filled('action_type')) {
            $actionlogs = $actionlogs->where('action_type', '=', $request->input('action_type'));
        }

        if ($request->filled('created_by')) {
            $actionlogs = $actionlogs->where('created_by', '=', $request->input('created_by'));
        }

        if ($request->filled('action_source')) {
            $actionlogs = $actionlogs->where('action_source', '=', $request->input('action_source'));
        }

        if ($request->filled('remote_ip')) {
            $actionlogs = $actionlogs->where('remote_ip', '=', $request->input('remote_ip'));
        }

        if ($request->filled('uploads')) {
            $actionlogs = $actionlogs->whereNotNull('filename');
        }

        $allowed_columns = [
            'id',
            'created_at',
            'target_id',
            'created_by',
            'accept_signature',
            'action_type',
            'note',
            'remote_ip',
            'user_agent',
            'target_type',
            'item_type',
            'action_source',
            'action_date',
        ];

        $total = $actionlogs->count();
        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = ($request->input('order') == 'asc') ? 'asc' : 'desc';

        switch ($request->input('sort')) {
            case 'created_by':
                $actionlogs->OrderByCreatedBy($order);
                break;
            default:
                $sort = in_array($request->input('sort'), $allowed_columns) ? e($request->input('sort')) : 'action_logs.created_at';
                $actionlogs = $actionlogs->orderBy($sort, $order);
                break;
        }

        $actionlogs = $actionlogs->skip($offset)->take($limit)->get();

        return response()->json((new ActionlogsTransformer)->transformActionlogs($actionlogs, $total), 200, ['Content-Type' => 'application/json;charset=utf8'], JSON_UNESCAPED_UNICODE);

    }
}
