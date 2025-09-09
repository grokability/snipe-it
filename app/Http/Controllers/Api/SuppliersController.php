<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\SelectlistTransformer;
use App\Http\Transformers\SuppliersTransformer;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Requests\ImageUploadRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class SuppliersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): array
    {
        $this->authorize('view', Supplier::class);
        $allowed_columns = [
            'id',
            'name',
            'address',
            'address2',
            'city',
            'state',
            'country',
            'zip',
            'phone',
            'contact',
            'fax',
            'email',
            'image',
            'assets_count',
            'licenses_count',
            'accessories_count',
            'components_count',
            'consumables_count',
            'url',
            'notes',
        ];
        
        $suppliers = Supplier::select(
                ['id', 'name', 'address', 'address2', 'city', 'state', 'country', 'fax', 'phone', 'email', 'contact', 'created_at', 'created_by', 'updated_at', 'deleted_at', 'image', 'notes', 'url', 'zip'])
                    ->withCount('assets as assets_count')
                    ->withCount('licenses as licenses_count')
                    ->withCount('accessories as accessories_count')
                    ->withCount('components as components_count')
                    ->withCount('consumables as consumables_count')
                    ->with('adminuser');


        if ($request->filled('search')) {
            $suppliers->TextSearch($request->input('search'));
        }


        if ($request->filled('name')) {
            $suppliers->where('name', '=', $request->input('name'));
        }

        if ($request->filled('address')) {
            $suppliers->where('address', '=', $request->input('address'));
        }

        if ($request->filled('address2')) {
            $suppliers->where('address2', '=', $request->input('address2'));
        }

        if ($request->filled('city')) {
            $suppliers->where('city', '=', $request->input('city'));
        }

        if ($request->filled('zip')) {
            $suppliers->where('zip', '=', $request->input('zip'));
        }

        if ($request->filled('country')) {
            $suppliers->where('country', '=', $request->input('country'));
        }

        if ($request->filled('fax')) {
            $suppliers->where('fax', '=', $request->input('fax'));
        }

        if ($request->filled('email')) {
            $suppliers->where('email', '=', $request->input('email'));
        }

        if ($request->filled('url')) {
            $suppliers->where('url', '=', $request->input('url'));
        }

        if ($request->filled('notes')) {
            $suppliers->where('notes', '=', $request->input('notes'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $suppliers->count()) ? $suppliers->count() : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        switch ($request->input('sort')) {
            case 'created_by':
                $suppliers->OrderByCreatedByName($order);
                break;
            default:
                $suppliers->orderBy($sort, $order);
                break;
        }

        $total = $suppliers->count();
        $suppliers = $suppliers->skip($offset)->take($limit)->get();

        return (new SuppliersTransformer)->transformSuppliers($suppliers, $total);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest  $request
     */
    public function store(ImageUploadRequest $request) : JsonResponse
    {
        $this->authorize('create', Supplier::class);
        $supplier = new Supplier;
        $supplier->fill($request->all());
        $supplier = $request->handleImages($supplier);

        if ($supplier->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $supplier, trans('admin/suppliers/message.create.success')));
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, $supplier->getErrors()));

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
        $this->authorize('view', Supplier::class);
        $supplier = Supplier::findOrFail($id);

        return (new SuppliersTransformer)->transformSupplier($supplier);
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
        $this->authorize('update', Supplier::class);
        $supplier = Supplier::findOrFail($id);
        $supplier->fill($request->all());
        $supplier = $request->handleImages($supplier);

        if ($supplier->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $supplier, trans('admin/suppliers/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $supplier->getErrors()));
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
        $this->authorize('delete', Supplier::class);
        $supplier = Supplier::with('maintenances', 'assets', 'licenses')->withCount('maintenances as maintenances_count', 'assets as assets_count', 'licenses as licenses_count')->findOrFail($id);
        $this->authorize('delete', $supplier);


        if ($supplier->assets_count > 0) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/suppliers/message.delete.assoc_assets', ['asset_count' => (int) $supplier->assets_count])));
        }

        if ($supplier->maintenances_count > 0) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/suppliers/message.delete.assoc_maintenances', ['maintenances_count' => $supplier->maintenances_count])));
        }

        if ($supplier->licenses_count > 0) {
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/suppliers/message.delete.assoc_licenses', ['licenses_count' => (int) $supplier->licenses_count])));
        }

        $supplier->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/suppliers/message.delete.success')));
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

        $suppliers = Supplier::select([
            'id',
            'name',
            'image',
        ]);

        if ($request->filled('search')) {
            $suppliers = $suppliers->where('suppliers.name', 'LIKE', '%'.$request->get('search').'%');
        }

        $suppliers = $suppliers->orderBy('name', 'ASC')->paginate(50);

        // Loop through and set some custom properties for the transformer to use.
        // This lets us have more flexibility in special cases like assets, where
        // they may not have a ->name value but we want to display something anyway
        foreach ($suppliers as $supplier) {
            $supplier->use_text = $supplier->name;
            $supplier->use_image = ($supplier->image) ? Storage::disk('public')->url('suppliers/'.$supplier->image, $supplier->image) : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($suppliers);
    }
}
