<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\CompaniesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Requests\ImageUploadRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class CompaniesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function index(Request $request) : JsonResponse | array
    {
        $this->authorize('view', Company::class);

        $allowed_columns = [
            'id',
            'name',
            'phone',
            'fax',
            'email',
            'created_at',
            'updated_at',
            'users_count',
            'assets_count',
            'licenses_count',
            'accessories_count',
            'consumables_count',
            'components_count',
            'notes',
        ];

        $companies = Company::withCount(['assets as assets_count'  => function ($query) {
            $query->AssetsForShow();
        }])
            ->with('adminuser')
            ->withCount('licenses as licenses_count', 'accessories as accessories_count', 'consumables as consumables_count', 'components as components_count', 'users as users_count');


        if ($request->filled('search')) {
            $companies->TextSearch($request->input('search'));
        }

        if ($request->filled('name')) {
            $companies->where('name', '=', $request->input('name'));
        }

		if ($request->filled('email')) {
            $companies->where('email', '=', $request->input('email'));
        }

        if ($request->filled('created_by')) {
            $companies->where('created_by', '=', $request->input('created_by'));
        }


        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $companies->count()) ? $companies->count() : app('api_offset_value');
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override =  $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'created_at';

        switch ($sort_override) {
            case 'created_by':
                $companies = $companies->OrderByCreatedBy($order);
                break;
            default:
                $companies = $companies->orderBy($column_sort, $order);
                break;
        }

        $total = $companies->count();

        $companies = $companies->skip($offset)->take($limit)->get();
        return (new CompaniesTransformer)->transformCompanies($companies, $total);

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
        $this->authorize('create', Company::class);
        $company = new Company;
        $company->fill($request->all());
        $company = $request->handleImages($company);
        
        if ($company->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', (new CompaniesTransformer)->transformCompany($company), trans('admin/companies/message.create.success')));
        }

        return response()
            ->json(Helper::formatStandardApiResponse('error', null, $company->getErrors()));
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
        $this->authorize('view', Company::class);
        $company = Company::findOrFail($id);
        $this->authorize('view', $company);
        return (new CompaniesTransformer)->transformCompany($company);

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
        $this->authorize('update', Company::class);
        $company = Company::findOrFail($id);
        $this->authorize('update', $company);
        $company->fill($request->all());
        $company = $request->handleImages($company);

        if ($company->save()) {
            return response()
                ->json(Helper::formatStandardApiResponse('success', (new CompaniesTransformer)->transformCompany($company), trans('admin/companies/message.update.success')));
        }

        return response()
            ->json(Helper::formatStandardApiResponse('error', null, $company->getErrors()));
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
        $this->authorize('delete', Company::class);
        $company = Company::findOrFail($id);
        $this->authorize('delete', $company);

        if (! $company->isDeletable()) {
            return response()
                    ->json(Helper::formatStandardApiResponse('error', null, trans('admin/companies/message.assoc_users')));
        }
        $company->delete();

        return response()
            ->json(Helper::formatStandardApiResponse('success', null, trans('admin/companies/message.delete.success')));
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
        $companies = Company::select([
            'companies.id',
            'companies.name',
            'companies.email',
            'companies.image',
        ]);


        if ($request->filled('search')) {
            $companies = $companies->where('companies.name', 'LIKE', '%'.$request->get('search').'%');
        }

        $companies = $companies->orderBy('name', 'ASC')->paginate(50);

        // Loop through and set some custom properties for the transformer to use.
        // This lets us have more flexibility in special cases like assets, where
        // they may not have a ->name value but we want to display something anyway
        foreach ($companies as $company) {
            $company->use_image = ($company->image) ? Storage::disk('public')->url('companies/'.$company->image, $company->image) : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($companies);
    }
}
