<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Http\Requests\UserIndexRequest;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_users')]
#[Title('List Users')]
#[Description('Search and list Snipe-IT users with optional filtering by keyword, company, department, and location')]
class ListUsersTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Read)) {
            return $denied;
        }

        if (! Gate::allows('index', User::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Shared filter rules live on UserIndexRequest::rulesArray()
        // so any future rule change stays in sync for both the MCP tool
        // and the HTTP API endpoint once it adopts the FormRequest.
        // MCP-specific pagination stays inline.
        try {
            $request->validate(array_merge(UserIndexRequest::rulesArray(), [
                'limit' => 'nullable|integer|min:1|max:'.config('app.max_results'),
                'offset' => 'nullable|integer|min:0',
            ]));
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        // Resolve *_name filters to IDs and merge back onto the request
        // so the existing filled() reads below keep working unchanged.
        // company_id still uses the company_user pivot path below.
        $fkMap = [
            ['company_id', 'company_name', Company::class],
            ['department_id', 'department_name', Department::class],
            ['location_id', 'location_name', Location::class],
        ];

        $resolvedFks = [];
        foreach ($fkMap as [$idField, $nameField, $modelClass]) {
            $resolved = $this->resolveFkByName($request, $idField, $nameField, $modelClass);
            if ($this->fkResolutionFailed($resolved)) {
                return Response::make(Response::error($this->fkErrorMessage($resolved)));
            }
            if ($resolved !== null) {
                $resolvedFks[$idField] = $resolved;
            }
        }
        $request->merge($resolvedFks);

        $users = User::with('companies', 'department', 'userloc', 'manager')
            ->withCount([
                'assets as assets_count',
                'licenses as licenses_count',
                'accessories as accessories_count',
                'consumables as consumables_count',
            ]);

        if ($request->boolean('only_deleted')) {
            $users->onlyTrashed();
        } elseif ($request->boolean('include_deleted')) {
            $users->withTrashed();
        }

        // filter wins when both are present, matching the HTTP API contract.
        if ($request->filled('filter')) {
            $users->TextSearch($request->get('filter'));
        } elseif ($request->filled('search')) {
            $users->TextSearch($request->get('search'));
        }

        if ($request->filled('company_id')) {
            // Company membership for users is pivot-based (company_user)
            // after the parent-company feature landed. users.company_id
            // was renamed to legacy_company_id and is no longer the
            // authoritative source. Filter via the pivot relationship.
            $companyId = $request->get('company_id');
            $users->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId));
        }

        if ($request->filled('department_id')) {
            $users->where('users.department_id', '=', $request->get('department_id'));
        }

        if ($request->filled('location_id')) {
            $users->where('users.location_id', '=', $request->get('location_id'));
        }

        if ($request->has('activated')) {
            $users->where('users.activated', '=', $request->get('activated'));
        }

        $users->orderBy('users.last_name', 'asc')->orderBy('users.first_name', 'asc');

        $total = $users->count();
        $limit = $request->filled('limit') ? max(1, min(config('app.max_results'), (int) $request->get('limit'))) : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $users->skip($offset)->take($limit)->get();

        $usersData = $results->map(fn (User $user) => [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'email' => $user->email,
            'employee_num' => $user->employee_num,
            'jobtitle' => $user->jobtitle,
            // Company membership is pivot-based (company_user) after the
            // parent-company feature. Prefer the pivot. Fall back to the
            // legacy relationship so sites mid-migration still see
            // something.
            'company' => ($user->companies->first() ?? $user->company)?->name,
            'company_ids' => $user->companies->pluck('id')->all(),
            'department' => $user->department?->name,
            'department_id' => $user->department_id,
            'location' => $user->userloc?->name,
            'location_id' => $user->location_id,
            'manager' => $user->manager?->full_name,
            'manager_id' => $user->manager_id,
            'activated' => (bool) $user->activated,
            'deleted_at' => optional($user->deleted_at)->toDateTimeString(),
            'last_login' => optional($user->last_login)->toDateTimeString(),
            'start_date' => $user->start_date,
            'end_date' => $user->end_date,
            'assets_count' => $user->assets_count,
            'licenses_count' => $user->licenses_count,
            'accessories_count' => $user->accessories_count,
            'consumables_count' => $user->consumables_count,
            'notes' => $user->notes,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_users', ['total' => $total, 'count' => count($usersData)]))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'users' => $usersData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Free-text keyword to search across name, username, email, and employee number. Mutually exclusive with filter: when both are supplied, filter wins.'),
            'filter' => $schema->object()->description('Structured advanced-search filter payload. Same shape the UI Advanced Search modal posts. Supports date ranges ("start - end"), enum lists, and any column listed in User searchable fields.'),
            'company_id' => $schema->number()->description('Filter by company ID. Alternative: company_name.'),
            'company_name' => $schema->string()->description('Filter by company name. Resolved to an ID server-side.'),
            'department_id' => $schema->number()->description('Filter by department ID. Alternative: department_name.'),
            'department_name' => $schema->string()->description('Filter by department name. Resolved to an ID server-side.'),
            'location_id' => $schema->number()->description('Filter by location ID. Alternative: location_name.'),
            'location_name' => $schema->string()->description('Filter by location name. Resolved to an ID server-side.'),
            'activated' => $schema->boolean()->description('Filter by the `activated` login-access flag. Note: many orgs never set this on real users (asset tracking only), so this is not a reliable "still with the company" signal. Use include_deleted / only_deleted for that.'),
            'include_deleted' => $schema->boolean()->description('When true, include soft-deleted users in the results (default: false)'),
            'only_deleted' => $schema->boolean()->description('When true, return ONLY soft-deleted users. Takes precedence over include_deleted.'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: '.config('app.max_results').')'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching users')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'users' => $schema->array()->description('Matched user rows including activated, deleted_at, last_login, and child-item counts (assets, licenses, accessories, consumables).'),
        ];
    }
}
