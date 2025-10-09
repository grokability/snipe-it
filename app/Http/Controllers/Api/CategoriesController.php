<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\CategoriesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ImageUploadRequest;
use Illuminate\Support\Facades\Storage;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) : array
    {
        $this->authorize('view', Category::class);
        $allowed_columns = [
            'id',
            'name',
            'category_type',
            'category_type',
            'use_default_eula',
            'eula_text',
            'require_acceptance',
            'checkin_email',
            'assets_count',
            'accessories_count',
            'consumables_count',
            'components_count',
            'licenses_count',
            'created_at',
            'updated_at',
            'image',
            'notes',
        ];

        $categories = Category::select([
            'id',
            'created_by',
            'created_at',
            'updated_at',
            'name', 'category_type',
            'use_default_eula',
            'eula_text',
            'require_acceptance',
            'checkin_email',
            'image',
            'notes',
            ])
            ->with('adminuser')
            ->withCount('accessories as accessories_count', 'consumables as consumables_count', 'components as components_count', 'licenses as licenses_count', 'models as models_count');


        $filter = [];

        if ($request->filled('filter')) {
            $filter = json_decode($request->input('filter'), true);

            $filter = array_filter($filter, function ($key) use ($allowed_columns) {
                return in_array($key, $allowed_columns);
            }, ARRAY_FILTER_USE_KEY);

        }

        if ((! is_null($filter)) && (count($filter)) > 0) {
            $categories->ByFilter($filter);
        } elseif ($request->filled('search')) {
            $categories->TextSearch($request->input('search'));
        }

        /*
         * This checks to see if we should override the Admin Setting to show archived assets in list.
         * We don't currently use it within the Snipe-IT GUI, but will be useful for API integrations where they
         * may actually need to fetch assets that are archived.
         *
         * @see \App\Models\Category::showableAssets()
         */
        if ($request->input('archived')=='true') {
            $categories = $categories->withCount('assets as assets_count');
        } else {
            $categories = $categories->withCount('showableAssets as assets_count');
        }

        if ($request->filled('name')) {
            $categories->where('name', '=', $request->input('name'));
        }

        if ($request->filled('category_type')) {
            $categories->where('category_type', '=', $request->input('category_type'));
        }

        if ($request->filled('use_default_eula')) {
            $categories->where('use_default_eula', '=', $request->input('use_default_eula'));
        }

        if ($request->filled('require_acceptance')) {
            $categories->where('require_acceptance', '=', $request->input('require_acceptance'));
        }

        if ($request->filled('checkin_email')) {
            $categories->where('checkin_email', '=', $request->input('checkin_email'));
        }

        if ($request->filled('created_by')) {
            $categories->where('created_by', '=', $request->input('created_by'));
        }

        if ($request->filled('created_at')) {
            $categories->where('created_at', '=', $request->input('created_at'));
        }

        if ($request->filled('updated_at')) {
            $categories->where('updated_at', '=', $request->input('updated_at'));
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $categories->count()) ? $categories->count() : app('api_offset_value');
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override =  $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'assets_count';

        switch ($sort_override) {
            case 'created_by':
                $categories = $categories->OrderByCreatedBy($order);
                break;
            default:
                $categories = $categories->orderBy($column_sort, $order);
                break;
        }

        $total = $categories->count();
        $categories = $categories->skip($offset)->take($limit)->get();

        return (new CategoriesTransformer)->transformCategories($categories, $total);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \App\Http\Requests\ImageUploadRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(ImageUploadRequest $request) : JsonResponse
    {
        $this->authorize('create', Category::class);
        $category = new Category;
        $category->fill($request->all());
        $category->category_type = strtolower($request->input('category_type'));
        $category = $request->handleImages($category);

        if ($category->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $category, trans('admin/categories/message.create.success')));
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, $category->getErrors()));

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
        $this->authorize('view', Category::class);
        $category = Category::withCount('assets as assets_count', 'accessories as accessories_count', 'consumables as consumables_count', 'components as components_count', 'licenses as licenses_count')->findOrFail($id);
        return (new CategoriesTransformer)->transformCategory($category);

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
    public function update(ImageUploadRequest $request, $id) : JsonResponse
    {
        $this->authorize('update', Category::class);
        $category = Category::findOrFail($id);

        // Don't allow the user to change the category_type once it's been created
        if (($request->filled('category_type')) && ($category->category_type != $request->input('category_type'))) {
            return response()->json(
                Helper::formatStandardApiResponse('error', null,  ['category_type' => trans('admin/categories/message.update.cannot_change_category_type')], 422)
            );
        }
        $category->fill($request->all());
        $category = $request->handleImages($category);

        if ($category->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $category, trans('admin/categories/message.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $category->getErrors()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) : JsonResponse
    {
        $this->authorize('delete', Category::class);
        $category = Category::withCount('assets as assets_count', 'accessories as accessories_count', 'consumables as consumables_count', 'components as components_count', 'licenses as licenses_count', 'models as models_count')->findOrFail($id);

        if (! $category->isDeletable()) {
            return response()->json(
                Helper::formatStandardApiResponse('error', null, trans('admin/categories/message.assoc_items', ['asset_type'=>$category->category_type]))
            );
        }
        $category->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/categories/message.delete.success')));
    }


    /**
     * Gets a paginated collection for the select2 menus
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0.16]
     * @see \App\Http\Transformers\SelectlistTransformer
     */
    public function selectlist(Request $request, $category_type = 'asset') : array
    {
        $this->authorize('view.selectlists');
        $categories = Category::select([
            'id',
            'name',
            'image',
        ]);

        if ($request->filled('search')) {
            $categories = $categories->where('name', 'LIKE', '%'.$request->get('search').'%');
        }

        $categories = $categories->where('category_type', $category_type)->orderBy('name', 'ASC')->paginate(50);

        // Loop through and set some custom properties for the transformer to use.
        // This lets us have more flexibility in special cases like assets, where
        // they may not have a ->name value but we want to display something anyway
        foreach ($categories as $category) {
            $category->use_image = ($category->image) ? Storage::disk('public')->url('categories/'.$category->image, $category->image) : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($categories);
    }
}
