<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\AssetsTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Http\Transformers\StatuslabelsTransformer;
use App\Models\Asset;
use App\Models\Setting;
use App\Models\Statuslabel;
use Illuminate\Http\Request;
use App\Http\Transformers\PieChartTransformer;
use Illuminate\Http\JsonResponse;

class StatuslabelsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function index(Request $request) : array
    {
        $this->authorize('view', Statuslabel::class);
        $allowed_columns = [
            'id',
            'name',
            'created_at',
            'assets_count',
            'color',
            'notes',
            'default_label'
        ];

        $statuslabels = Statuslabel::with('adminuser')->withCount('assets as assets_count');

        if ($request->filled('search')) {
            $statuslabels = $statuslabels->TextSearch($request->input('search'));
        }

        if ($request->filled('name')) {
            $statuslabels->where('name', '=', $request->input('name'));
        }


        // if a status_type is passed, filter by that
        if ($request->filled('status_type')) {
            if (strtolower($request->input('status_type')) == 'pending') {
                $statuslabels = $statuslabels->Pending();
            } elseif (strtolower($request->input('status_type')) == 'archived') {
                $statuslabels = $statuslabels->Archived();
            } elseif (strtolower($request->input('status_type')) == 'deployable') {
                $statuslabels = $statuslabels->Deployable();
            } elseif (strtolower($request->input('status_type')) == 'undeployable') {
                $statuslabels = $statuslabels->Undeployable();
            }
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $statuslabels->count()) ? $statuslabels->count() : app('api_offset_value');
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override =  $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'created_at';

        switch ($sort_override) {
            case 'created_by':
                $statuslabels = $statuslabels->OrderByCreatedBy($order);
                break;
            default:
                $statuslabels = $statuslabels->orderBy($column_sort, $order);
                break;
        }

        $total = $statuslabels->count();
        $statuslabels = $statuslabels->skip($offset)->take($limit)->get();

        return (new StatuslabelsTransformer)->transformStatuslabels($statuslabels, $total);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request) : JsonResponse
    {
        $this->authorize('create', Statuslabel::class);
        $request->except('deployable', 'pending', 'archived');

        if (! $request->filled('type')) {

            return response()->json(Helper::formatStandardApiResponse('error', null, ['type' => ['Status label type is required.']]));
        }

        $statuslabel = new Statuslabel;
        $statuslabel->fill($request->all());

        $statusType = Statuslabel::getStatuslabelTypesForDB($request->input('type'));
        $statuslabel->deployable = $statusType['deployable'];
        $statuslabel->pending = $statusType['pending'];
        $statuslabel->archived = $statusType['archived'];
        $statuslabel->color             =  $request->input('color');
        $statuslabel->show_in_nav       =  $request->input('show_in_nav', 0);
        $statuslabel->default_label     =  $request->input('default_label', 0);


        if ($statuslabel->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $statuslabel, trans('admin/statuslabels/message.create.success')));
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, $statuslabel->getErrors()));

    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function show($id) : array
    {
        $this->authorize('view', Statuslabel::class);
        $statuslabel = Statuslabel::findOrFail($id);

        return (new StatuslabelsTransformer)->transformStatuslabel($statuslabel);
    }


    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id) : JsonResponse
    {
        $this->authorize('update', Statuslabel::class);
        $statuslabel = Statuslabel::findOrFail($id);
        
        $request->except('deployable', 'pending', 'archived');


        if (! $request->filled('type')) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Status label type is required.'));
        }

        $statuslabel->fill($request->all());

        $statusType = Statuslabel::getStatuslabelTypesForDB($request->input('type'));
        $statuslabel->deployable = $statusType['deployable'];
        $statuslabel->pending = $statusType['pending'];
        $statuslabel->archived = $statusType['archived'];
        $statuslabel->color             =  $request->input('color');
        $statuslabel->show_in_nav       =  $request->input('show_in_nav', 0);
        $statuslabel->default_label     =  $request->input('default_label', 0);

        if ($statuslabel->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $statuslabel, trans('admin/statuslabels/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $statuslabel->getErrors()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function destroy($id) : JsonResponse
    {
        $this->authorize('delete', Statuslabel::class);
        $statuslabel = Statuslabel::findOrFail($id);
        $this->authorize('delete', $statuslabel);

        // Check that there are no assets associated
        if ($statuslabel->assets()->count() == 0) {
            $statuslabel->delete();

            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/statuslabels/message.delete.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/statuslabels/message.assoc_assets')));
    }



     /**
     * Show a count of assets by status label for pie chart
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     */
    public function getAssetCountByStatuslabel() : array
    {
        $this->authorize('view', Statuslabel::class);

        if (Setting::getSettings()->show_archived_in_list == 0 ) {
            $statuslabels = Statuslabel::withCount('assets')->where('archived','0')->get();
        } else {
            $statuslabels = Statuslabel::withCount('assets')->get();
        }

        $total = [];

        foreach ($statuslabels as $statuslabel) {

            $total[$statuslabel->name]['label'] = $statuslabel->name;
            $total[$statuslabel->name]['count'] = $statuslabel->assets_count;

            if ($statuslabel->color != '') {
                $total[$statuslabel->name]['color'] = $statuslabel->color;
            }
        }

        return (new PieChartTransformer())->transformPieChartDate($total);

    }

    /**
     * Show a count of assets by meta status type for pie chart
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.0.11]
     */
    public function getAssetCountByMetaStatus() : array
    {
        $this->authorize('view', Statuslabel::class);

        $total['rtd']['label'] = trans('general.ready_to_deploy');
        $total['rtd']['count'] = Asset::RTD()->count();

        $total['deployed']['label'] = trans('general.deployed');
        $total['deployed']['count'] = Asset::Deployed()->count();

        $total['archived']['label'] = trans('general.archived');
        $total['archived']['count'] = Asset::Archived()->count();

        $total['pending']['label'] = trans('general.pending');
        $total['pending']['count'] = Asset::Pending()->count();

        $total['undeployable']['label'] = trans('general.undeployable');
        $total['undeployable']['count'] = Asset::Undeployable()->count();

        return (new PieChartTransformer())->transformPieChartDate($total);
    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function assets(Request $request, $id) : array
    {
        $this->authorize('view', Statuslabel::class);
        $this->authorize('index', Asset::class);
        $assets = Asset::where('status_id', '=', $id)->with('assignedTo');

        $allowed_columns = [
            'id',
            'name',
        ];

        $offset = request('offset', 0);
        $limit = $request->input('limit', 50);
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';
        $assets->orderBy($sort, $order);

        $total = $assets->count();
        $assets = $assets->skip($offset)->take($limit)->get();


        return (new AssetsTransformer)->transformAssets($assets, $total);
    }


    /**
     * Returns a boolean response based on whether the status label
     * is one that is deployable or pending.
     *
     * This is used by the hardware create/edit view to determine whether
     * we should provide a dropdown of users for them to check the asset out to,
     * and whether we show a warning that the asset will be checked in if it's already
     * assigned but the status is changed to one that isn't pending or deployable
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function checkIfDeployable($id) : string
    {
        $statuslabel = Statuslabel::findOrFail($id);
        if (($statuslabel->getStatuslabelType() == 'pending') || ($statuslabel->getStatuslabelType() == 'deployable')) {
            return '1';
        }

        return '0';
    }

    /**
     * Gets a paginated collection for the select2 menus
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.1.1]
     * @see \App\Http\Transformers\SelectlistTransformer
     */
    public function selectlist(Request $request) : array
    {

        $this->authorize('view.selectlists');
        $statuslabels = Statuslabel::orderBy('default_label', 'desc')->orderBy('name', 'asc')->orderBy('deployable', 'desc');

        if ($request->filled('search')) {
            $statuslabels = $statuslabels->where('name', 'LIKE', '%'.$request->get('search').'%');
        }

        if ($request->filled('deployable')) {
            $statuslabels = $statuslabels->where('deployable', '=', '1');
        }

        if ($request->filled('pending')) {
            $statuslabels = $statuslabels->where('pending', '=', '1');
        }

        if ($request->filled('archived')) {
            $statuslabels = $statuslabels->where('archived', '=', '1');
        }

        $statuslabels = $statuslabels->orderBy('name', 'ASC')->paginate(50);

        // Loop through and set some custom properties for the transformer to use.
        // This lets us have more flexibility in special cases like assets, where
        // they may not have a ->name value but we want to display something anyway
        foreach ($statuslabels as $statuslabel) {
            $statuslabels->use_text = $statuslabel->name;
        }

        return (new SelectlistTransformer)->transformSelectlist($statuslabels);
    }
}
