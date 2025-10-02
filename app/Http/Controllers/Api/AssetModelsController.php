<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetModelRequest;
use App\Http\Transformers\AssetModelsTransformer;
use App\Http\Transformers\AssetsTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Asset;
use App\Models\AssetModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

/**
 * This class controls all actions related to asset models for
 * the Snipe-IT Asset Management application.
 *
 * @version    v4.0
 * @author [A. Gianotto] [<snipe@snipe.net>]
 */
class AssetModelsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function index(Request $request) : JsonResponse | array
    {
        $this->authorize('view', AssetModel::class);
        $allowed_columns =
            [
                'id',
                'image',
                'name',
                'model_number',
                'min_amt',
                'eol',
                'notes',
                'created_at',
                'manufacturer',
                'requestable',
                'assets_count',
                'assets_assigned_count',
                'assets_archived_count',
                'remaining',
                'category',
                'fieldset',
                'deleted_at',
                'updated_at',
                'require_serial',
            ];

        $assetmodels = AssetModel::select([
            'models.id',
            'models.image',
            'models.name',
            'models.model_number',
            'models.min_amt',
            'models.eol',
            'models.created_by',
            'models.requestable',
            'models.notes',
            'models.created_at',
            'models.category_id',
            'models.manufacturer_id',
            'models.depreciation_id',
            'models.fieldset_id',
            'models.deleted_at',
            'models.updated_at',
            'models.require_serial'
         ])
            ->with('category', 'depreciation', 'manufacturer', 'fieldset.fields.defaultValues', 'adminuser')
            ->withCount('assets as assets_count')
            ->withCount('availableAssets as remaining')
            ->withCount('assignedAssets as assets_assigned_count')
            ->withCount('archivedAssets as assets_archived_count');

        if ($request->input('status')=='deleted') {
            $assetmodels->onlyTrashed();
        }

        if ($request->filled('name')) {
            $assetmodels = $assetmodels->where('models.name', '=', $request->input('name'));
        }

        if ($request->filled('model_number')) {
            $assetmodels = $assetmodels->where('models.model_number', '=', $request->input('model_number'));
        }

        if ($request->input('requestable') == 'true') {
            $assetmodels = $assetmodels->where('models.requestable', '=', '1');
        } elseif ($request->input('requestable') == 'false') {
            $assetmodels = $assetmodels->where('models.requestable', '=', '0');
        }        

        if ($request->filled('notes')) {
            $assetmodels = $assetmodels->where('models.notes', '=', $request->input('notes'));
        }

        if ($request->filled('category_id')) {
            $assetmodels = $assetmodels->where('models.category_id', '=', $request->input('category_id'));
        }

        if ($request->filled('depreciation_id')) {
            $assetmodels = $assetmodels->where('models.depreciation_id', '=', $request->input('depreciation_id'));
        }

        if ($request->filled('search')) {
            $assetmodels->TextSearch($request->input('search'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $assetmodels->count()) ? $assetmodels->count() : abs($request->input('offset'));
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'models.created_at';

        switch ($request->input('sort')) {
            case 'manufacturer':
                $assetmodels->OrderManufacturer($order);
                break;
            case 'category':
                $assetmodels->OrderCategory($order);
                break;
            case 'fieldset':
                $assetmodels->OrderFieldset($order);
                break;
            case 'created_by':
                $assetmodels->OrderByCreatedByName($order);
                break;
            default:
                $assetmodels->orderBy($sort, $order);
                break;
        }

        $total = $assetmodels->count();
        $assetmodels = $assetmodels->skip($offset)->take($limit)->get();

        return (new AssetModelsTransformer)->transformAssetModels($assetmodels, $total);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\StoreAssetModelRequest  $request
     */
    public function store(StoreAssetModelRequest $request) : JsonResponse
    {
        $this->authorize('create', AssetModel::class);
        $assetmodel = new AssetModel;
        $assetmodel->fill($request->all());
        $assetmodel = $request->handleImages($assetmodel);

        if ($assetmodel->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', (new AssetModelsTransformer)->transformAssetModel($assetmodel), trans('admin/models/message.create.success')));
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, $assetmodel->getErrors()));


    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function show($id) :  array
    {
        $this->authorize('view', AssetModel::class);
        $assetmodel = AssetModel::withCount('assets as assets_count')->findOrFail($id);

        return (new AssetModelsTransformer)->transformAssetModel($assetmodel);
    }

    /**
     * Display the specified resource's assets
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function assets($id) : array
    {
        $this->authorize('view', AssetModel::class);
        $assets = Asset::where('model_id', '=', $id)->get();

        return (new AssetsTransformer)->transformAssets($assets, $assets->count());
    }


    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreAssetModelRequest $request, $id) : JsonResponse
    {
        $this->authorize('update', AssetModel::class);
        $assetmodel = AssetModel::findOrFail($id);
        $assetmodel->fill($request->all());
        $assetmodel = $request->handleImages($assetmodel);

        /**
         * Allow custom_fieldset_id to override and populate fieldset_id.
         * This is stupid, but required for legacy API support.
         *
         * I have no idea why we manually overrode that field name
         * in previous versions. I assume there was a good reason for
         * it, but I'll be damned if I can think of one. - snipe
         */
        if ($request->filled('custom_fieldset_id')) {
            $assetmodel->fieldset_id = $request->get('custom_fieldset_id');
        }


        if ($assetmodel->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', (new AssetModelsTransformer)->transformAssetModel($assetmodel), trans('admin/models/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $assetmodel->getErrors()));
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
        $this->authorize('delete', AssetModel::class);
        $assetmodel = AssetModel::findOrFail($id);
        $this->authorize('delete', $assetmodel);

        if ($assetmodel->assets()->count() > 0) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/models/message.assoc_users')));
        }

        if ($assetmodel->image) {
            try {
                Storage::disk('public')->delete('assetmodels/'.$assetmodel->image);
            } catch (\Exception $e) {
                Log::info($e);
            }
        }

        $assetmodel->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/models/message.delete.success')));
    }

    /**
     * Gets a paginated collection for the select2 menus
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0.16]
     * @see \App\Http\Transformers\SelectlistTransformer
     */
    public function selectlist(Request $request) : array
    {

        $this->authorize('view.selectlists');
        $assetmodels = AssetModel::select([
            'models.id',
            'models.name',
            'models.image',
            'models.model_number',
            'models.manufacturer_id',
            'models.category_id',
        ])->with('manufacturer', 'category');

        $settings = \App\Models\Setting::getSettings();

        if ($request->filled('search')) {
            $assetmodels = $assetmodels->SearchByManufacturerOrCat($request->input('search'));
        }

        $assetmodels = $assetmodels->OrderCategory('ASC')->OrderManufacturer('ASC')->orderby('models.name', 'asc')->orderby('models.model_number', 'asc')->paginate(50);

        foreach ($assetmodels as $assetmodel) {
            $assetmodel->use_text = '';

            if ($settings->modellistCheckedValue('category')) {
                $assetmodel->use_text .= (($assetmodel->category) ? $assetmodel->category->name.' - ' : '');
            }

            if ($settings->modellistCheckedValue('manufacturer')) {
                $assetmodel->use_text .= (($assetmodel->manufacturer) ? $assetmodel->manufacturer->name.' ' : '');
            }

            $assetmodel->use_text .= $assetmodel->name;

            if (($settings->modellistCheckedValue('model_number')) && ($assetmodel->model_number != '')) {
                $assetmodel->use_text .= ' (#'.$assetmodel->model_number.')';
            }

            $assetmodel->use_image = ($settings->modellistCheckedValue('image') && ($assetmodel->image)) ? Storage::disk('public')->url('models/'.e($assetmodel->image)) : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($assetmodels);
    }
}
