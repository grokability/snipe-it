<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Transformers\CompaniesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Company;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompaniesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     */
    public function index(FilterRequest $request): JsonResponse|array
    {
        $this->authorize('view', Company::class);

        $allowed_columns = [
            'id',
            'name',
            'parent',
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
            'children_count',
            'tag_color',
            'notes',
        ];

        $companies = Company::withCount(['assets as assets_count' => function ($query) {
            $query->AssetsForShow();
        }])
            ->with('adminuser', 'parent')
            ->withCount('licenses as licenses_count')
            ->withCount('accessories as accessories_count')
            ->withCount('consumables as consumables_count')
            ->withCount('components as components_count')
            ->withCount('users as users_count')
            // children is a self-relation. Laravel aliases the related table to
            // `laravel_reserved_0` for the count subquery, but CompanyableScope
            // hardcodes `companies.id` in its where clause — that clashes with
            // the alias and produces "Unknown column 'laravel_reserved_0.parent_id'".
            // The closure form lets us strip the global scope on the subquery.
            ->withCount(['children as children_count' => function ($query) {
                $query->withoutGlobalScopes();
            }]);

        // This invokes the Searchable model trait scopeTextSearch and will handle input by search or by advanced search filter
        if ($request->filled('filter') || $request->filled('search')) {
            $companies->TextSearch($request->input('filter') ? $request->input('filter') : $request->input('search'));
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

        if ($request->filled('tag_color')) {
            $companies->where('tag_color', '=', $request->input('tag_color'));
        }

        if ($request->filled('parent_id')) {
            // Allow filtering for top-level companies by passing parent_id=0 or "null".
            $parentId = $request->input('parent_id');
            if (strtolower((string) $parentId) === 'null') {
                $companies->whereNull('parent_id');
            } else {
                $companies->where('parent_id', '=', (int) $parentId);
            }
        }

        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $companies->count()) ? $companies->count() : app('api_offset_value');
        $limit = app('api_limit_value');
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override = $request->input('sort');
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'created_at';

        switch ($sort_override) {
            case 'created_by':
                $companies = $companies->OrderByCreatedBy($order);
                break;
            case 'parent':
                $companies = $companies->OrderParent($order);
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
     *
     * @since [v4.0]
     */
    public function store(ImageUploadRequest $request): JsonResponse
    {
        $this->authorize('create', Company::class);
        $company = new Company;
        $company->fill($request->all());
        $company->created_by = auth()->id();
        $company = $request->handleImages($company);

        if ($company->save()) {
            // parent is worth loading so the response reflects the hierarchy the caller just set.
            $company->loadMissing('parent');

            return response()->json(Helper::formatStandardApiResponse('success', (new CompaniesTransformer)->transformCompany($company), trans('admin/companies/message.create.success')));
        }

        return response()
            ->json(Helper::formatStandardApiResponse('error', null, $company->getErrors()));
    }

    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  int  $id
     */
    public function show($id): array
    {
        $this->authorize('view', Company::class);
        $company = Company::with('parent')
            // Same seven counts as index(). isDeletable() reads *_count and
            // falls back to per-relation ->count() queries if these aren't
            // preloaded, which would be six extra hits per show call.
            ->withCount('assets as assets_count', 'licenses as licenses_count', 'accessories as accessories_count', 'consumables as consumables_count', 'components as components_count', 'users as users_count')
            ->withCount(['children as children_count' => fn ($q) => $q->withoutGlobalScopes()])
            ->findOrFail($id);
        $this->authorize('view', $company);

        return (new CompaniesTransformer)->transformCompany($company);
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.0]
     *
     * @param  int  $id
     */
    public function update(ImageUploadRequest $request, $id): JsonResponse
    {
        $this->authorize('update', Company::class);
        $company = Company::findOrFail($id);
        $this->authorize('update', $company);
        $company->fill($request->all());
        $company = $request->handleImages($company);

        if ($company->save()) {
            $company->loadMissing('parent');
            // Match the index()/show() eager loads so isDeletable() in the
            // transformer doesn't fall through to per-relation count queries.
            $company->loadCount(['assets as assets_count', 'licenses as licenses_count', 'accessories as accessories_count', 'consumables as consumables_count', 'components as components_count', 'users as users_count']);
            $company->loadCount(['children as children_count' => fn ($q) => $q->withoutGlobalScopes()]);

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
     *
     * @since [v4.0]
     *
     * @param  int  $id
     */
    public function destroy($id): JsonResponse
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
     *
     * @since [v4.0.16]
     * @see SelectlistTransformer
     */
    public function selectlist(Request $request): array
    {
        $this->authorize('view.selectlists');
        $companies = Company::select([
            'companies.id',
            'companies.name',
            'companies.parent_id',
            'companies.email',
            'companies.image',
            'companies.tag_color',
        ]);

        // When FMCS is enabled and the user is not a superuser, restrict the list to
        // companies they belong to (primary company_id + pivot companies). This lets
        // non-superusers select a company from their own set when creating assets, etc.
        if (Setting::getSettings()->full_multiple_companies_support == '1' && ! auth()->user()->isSuperUser()) {
            $userCompanyIds = auth()->user()->allCompanies()->pluck('id');
            if ($userCompanyIds->isNotEmpty()) {
                $companies->whereIn('companies.id', $userCompanyIds);
            }
        }

        if ($request->filled('search')) {
            $companies = $companies->where('companies.name', 'LIKE', '%'.$request->input('search').'%');
        }

        // excludeId: drop a specific company from the list. Used by the parent-
        // company picker on the edit form so a company can't be made its own parent.
        if ($request->filled('excludeId')) {
            $companies->where('companies.id', '!=', (int) $request->input('excludeId'));
        }

        // Mirror Locations: fetch the full set so we can sort parent → child and
        // emit indented use_text. Manual pagination after the hierarchical sort
        // keeps select2 responsive on large lists while preserving grouping.
        $companies = $companies->orderBy('name', 'ASC')->get();

        // onlyTopLevel: child companies (parent_id != null) still appear, but are
        // marked disabled so select2 renders them un-clickable. Communicates the
        // hierarchy constraint visually instead of as a post-submit error.
        $onlyTopLevel = $request->boolean('onlyTopLevel');
        if ($onlyTopLevel) {
            foreach ($companies as $company) {
                if ($company->parent_id) {
                    $company->use_disabled = true;
                }
            }
        }

        if ($request->filled('search')) {
            // Searching breaks hierarchy display anyway — render flat results.
            foreach ($companies as $company) {
                $company->use_image = ($company->image)
                    ? Storage::disk('public')->url('companies/'.$company->image)
                    : null;
            }
            $sorted = $companies;
        } else {
            // Group by parent_id, using 0 for "no parent" — PHP 8.4 deprecates
            // null array offsets and Company::indenter expects ints.
            $companies_by_parent = [];
            foreach ($companies as $company) {
                $companies_by_parent[(int) $company->parent_id][] = $company;
            }

            // If a user is scoped to a child but not its parent, the child's
            // parent_id won't have a top-level key — flatten those into the
            // top-level bucket so they still appear in the list.
            $visibleIds = $companies->pluck('id')->all();
            foreach ($companies_by_parent as $pid => $rows) {
                if ($pid !== 0 && ! in_array($pid, $visibleIds, true)) {
                    $companies_by_parent[0] = array_merge($companies_by_parent[0] ?? [], $rows);
                    unset($companies_by_parent[$pid]);
                }
            }

            $sorted = new Collection(Company::indenter($companies_by_parent));
        }

        return (new SelectlistTransformer)->transformSelectlist(Helper::paginateCollection($sorted));
    }
}
