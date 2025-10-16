<?php

namespace App\Http\Controllers\Api;

use App\Actions\Manufacturers\DeleteManufacturerAction;
use App\Exceptions\ItemStillHasAccessories;
use App\Exceptions\ItemStillHasAssets;
use App\Exceptions\ItemStillHasChildren;
use App\Exceptions\ItemStillHasComponents;
use App\Exceptions\ItemStillHasConsumables;
use App\Exceptions\ItemStillHasLicenses;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\ManufacturersTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Actionlog;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use App\Http\Requests\ImageUploadRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class ManufacturersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) : JsonResponse | array
    {
        $this->authorize('view', Manufacturer::class);
        $allowed_columns =  [
            'id',
            'name',
            'url',
            'support_url',
            'support_email',
            'warranty_lookup_url',
            'support_phone',
            'created_at',
            'updated_at',
            'image',
            'assets_count',
            'consumables_count',
            'components_count',
            'licenses_count',
            'notes',
        ];

        $manufacturers = Manufacturer::select([
                'id',
                'name',
                'url',
                'support_url',
                'warranty_lookup_url',
                'support_email',
                'support_phone',
                'created_by',
                'created_at',
                'updated_at',
                'image',
                'deleted_at',
                'notes',
            ])
            ->with('adminuser')
            ->withCount('assets as assets_count')
            ->withCount('licenses as licenses_count')
            ->withCount('consumables as consumables_count')
            ->withCount('accessories as accessories_count')
            ->withCount('components as components_count');

        if ($request->input('deleted') == 'true') {
            $manufacturers->onlyTrashed();
        }

        if ($request->filled('search')) {
            $manufacturers = $manufacturers->TextSearch($request->input('search'));
        }

        if ($request->filled('name')) {
            $manufacturers->where('name', '=', $request->input('name'));
        }

        if ($request->filled('url')) {
            $manufacturers->where('url', '=', $request->input('url'));
        }

        if ($request->filled('support_url')) {
            $manufacturers->where('support_url', '=', $request->input('support_url'));
        }

        if ($request->filled('warranty_lookup_url')) {
            $manufacturers->where('warranty_lookup_url', '=', $request->input('warranty_lookup_url'));
        }

        if ($request->filled('support_phone')) {
            $manufacturers->where('support_phone', '=', $request->input('support_phone'));
        }

        if ($request->filled('support_email')) {
            $manufacturers->where('support_email', '=', $request->input('support_email'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $manufacturers->count()) ? $manufacturers->count() : app('api_offset_value');
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override =  $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'created_at';

        switch ($sort_override) {
            case 'created_by':
                $manufacturers = $manufacturers->OrderByCreatedBy($order);
                break;
            default:
                $manufacturers = $manufacturers->orderBy($column_sort, $order);
                break;
        }

        $total = $manufacturers->count();
        $manufacturers = $manufacturers->skip($offset)->take($limit)->get();

        return (new ManufacturersTransformer)->transformManufacturers($manufacturers, $total);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest $request
     */
    public function store(ImageUploadRequest $request) : JsonResponse
    {
        $this->authorize('create', Manufacturer::class);
        $manufacturer = new Manufacturer;
        $manufacturer->fill($request->all());
        $manufacturer = $request->handleImages($manufacturer);

        if ($manufacturer->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $manufacturer, trans('admin/manufacturers/message.create.success')));
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, $manufacturer->getErrors()));

    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function show($id) : JsonResponse | array
    {
        $this->authorize('view', Manufacturer::class);
        $manufacturer = Manufacturer::withCount('assets as assets_count')->withCount('licenses as licenses_count')->withCount('consumables as consumables_count')->withCount('accessories as accessories_count')->findOrFail($id);

        return (new ManufacturersTransformer)->transformManufacturer($manufacturer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest  $request
     * @param  int  $id
     */
    public function update(ImageUploadRequest $request, $id) : JsonResponse
    {
        $this->authorize('update', Manufacturer::class);
        $manufacturer = Manufacturer::findOrFail($id);
        $manufacturer->fill($request->all());
        $manufacturer = $request->handleImages($manufacturer);

        if ($manufacturer->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $manufacturer, trans('admin/manufacturers/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $manufacturer->getErrors()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     */
    public function destroy(Manufacturer $manufacturer): JsonResponse
    {
        $this->authorize('delete', $manufacturer);
        try {
            DeleteManufacturerAction::run($manufacturer);
        } catch (ItemStillHasChildren $e) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.bulk_delete_associations.general_assoc_warning', ['item' => trans('general.manufacturer')])));
        } catch (\Exception $e) {
            report($e);
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('general.something_went_wrong')));
        }

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/manufacturers/message.delete.success')));
    }

    /**
     * Restore a given Manufacturer (mark as un-deleted)
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.3.4]
     * @param int $id
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function restore($id) : JsonResponse
    {
        $this->authorize('delete', Manufacturer::class);

        if ($manufacturer = Manufacturer::withTrashed()->find($id)) {

            if ($manufacturer->deleted_at == '') {
                return response()->json(Helper::formatStandardApiResponse('error', trans('general.not_deleted', ['item_type' => trans('general.manufacturer')])), 200);
            }

            if ($manufacturer->restore()) {

                $logaction = new Actionlog();
                $logaction->item_type = Manufacturer::class;
                $logaction->item_id = $manufacturer->id;
                $logaction->created_at = date('Y-m-d H:i:s');
                $logaction->created_by = auth()->id();
                $logaction->logaction('restore');

                return response()->json(Helper::formatStandardApiResponse('success', trans('admin/manufacturers/message.restore.success')), 200);
            }

            // Check validation to make sure we're not restoring an item with the same unique attributes as a non-deleted one
            return response()->json(Helper::formatStandardApiResponse('error', trans('general.could_not_restore', ['item_type' => trans('general.manufacturer'), 'error' => $manufacturer->getErrors()->first()])), 200);
        }

        return response()->json(Helper::formatStandardApiResponse('error', null,  trans('admin/manufacturers/message.does_not_exist')));
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
        $manufacturers = Manufacturer::select([
            'id',
            'name',
            'image',
        ]);

        if ($request->filled('search')) {
            $manufacturers = $manufacturers->where('name', 'LIKE', '%'.$request->get('search').'%');
        }

        $manufacturers = $manufacturers->orderBy('name', 'ASC')->paginate(50);

        // Loop through and set some custom properties for the transformer to use.
        // This lets us have more flexibility in special cases like assets, where
        // they may not have a ->name value but we want to display something anyway
        foreach ($manufacturers as $manufacturer) {
            $manufacturer->use_text = $manufacturer->name;
            $manufacturer->use_image = ($manufacturer->image) ? Storage::disk('public')->url('manufacturers/'.$manufacturer->image, $manufacturer->image) : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($manufacturers);
    }
}
