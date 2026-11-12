<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Http\Requests\AccessoryIndexRequest;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Accessory;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
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

#[Name('list_accessories')]
#[Title('List Accessories')]
#[Description('Search and list Snipe-IT accessories with optional filtering by keyword, company, category, manufacturer, location, and low-stock status')]
class ListAccessoriesTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Read)) {
            return $denied;
        }

        if (! Gate::allows('index', Accessory::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Shared filter rules live on AccessoryIndexRequest::rulesArray().
        // MCP-specific pagination stays inline. Max limit tracks the API's
        // config('app.max_results') so both surfaces honor the same env.
        try {
            $request->validate(array_merge(AccessoryIndexRequest::rulesArray(), [
                'limit' => 'nullable|integer|min:1|max:'.config('app.max_results'),
                'offset' => 'nullable|integer|min:0',
            ]));
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        // Resolve *_name filters to IDs before applying where clauses.
        // Merged back onto the request so the existing filled() reads
        // below keep working without touching each filter site.
        $fkMap = [
            ['company_id', 'company_name', Company::class],
            ['category_id', 'category_name', Category::class],
            ['manufacturer_id', 'manufacturer_name', Manufacturer::class],
            ['supplier_id', 'supplier_name', Supplier::class],
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

        $accessories = Accessory::with('company', 'category', 'manufacturer', 'defaultSupplier', 'location');

        // filter wins when both are present, matching the HTTP API contract.
        if ($request->filled('filter')) {
            $accessories->TextSearch($request->get('filter'));
        } elseif ($request->filled('search')) {
            $accessories->TextSearch($request->get('search'));
        }

        if ($request->filled('company_id')) {
            $accessories->where('accessories.company_id', $request->get('company_id'));
        }

        if ($request->filled('category_id')) {
            $accessories->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $accessories->where('manufacturer_id', $request->get('manufacturer_id'));
        }

        if ($request->filled('supplier_id')) {
            $accessories->where('supplier_id', $request->get('supplier_id'));
        }

        if ($request->filled('location_id')) {
            $accessories->where('location_id', $request->get('location_id'));
        }

        // Archived scoping uses the same builder scopes the UI does.
        //   archived_only=true  -> archived() (archived rows only)
        //   include_archived=true -> no filter (active + archived)
        //   default -> accessoriesForShow() (hides archived unless the
        //              show_archived_in_list admin setting is on, so
        //              MCP list output tracks the UI's visible set).
        if ($request->boolean('archived_only')) {
            $accessories->archived();
        } elseif (! $request->boolean('include_archived')) {
            $accessories->accessoriesForShow();
        }

        if ($request->boolean('low_stock_only')) {
            // Current remaining = qty minus active checkouts (checkouts
            // are deleted on checkin, so a row present means "still
            // checked out"). Items where remaining <= min_amt are "low
            // stock" per Snipe-IT's reporting conventions. Done as a
            // subquery so SQL filters before pagination.
            $accessories->notArchived()
                ->whereRaw(
                    '(accessories.qty - (SELECT COUNT(*) FROM accessories_checkout WHERE accessories_checkout.accessory_id = accessories.id)) <= COALESCE(accessories.min_amt, 0)'
                );
        }

        $accessories->orderBy('accessories.created_at', 'desc');

        $total = $accessories->count();
        $limit = $request->filled('limit') ? max(1, min(config('app.max_results'), (int) $request->get('limit'))) : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $accessories->skip($offset)->take($limit)->get();

        $accessoriesData = $results->map(fn (Accessory $accessory) => [
            'id' => $accessory->id,
            'name' => $accessory->name,
            'model_number' => $accessory->model_number,
            'qty' => $accessory->qty,
            'min_amt' => $accessory->min_amt,
            'remaining' => $accessory->numRemaining(),
            'checked_out_count' => $accessory->qty - $accessory->numRemaining(),
            'category' => $accessory->category?->name,
            'category_id' => $accessory->category_id,
            'manufacturer' => $accessory->manufacturer?->name,
            'manufacturer_id' => $accessory->manufacturer_id,
            'supplier' => $accessory->defaultSupplier?->name,
            'default_supplier_id' => $accessory->default_supplier_id,
            'company' => $accessory->company?->name,
            'company_id' => $accessory->company_id,
            'location' => $accessory->location?->name,
            'location_id' => $accessory->location_id,
            // Per-acquisition purchase data (date, cost, order number)
            // lives on the Orders table now. default_purchase_cost is
            // the template value on the parent accessory itself.
            'default_purchase_cost' => $accessory->default_purchase_cost,
            'archived' => (bool) $accessory->archived,
            'notes' => $accessory->notes,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_accessories', ['total' => $total, 'count' => count($accessoriesData)]))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'accessories' => $accessoriesData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Free-text keyword to search across accessory name, model number, and related fields. Mutually exclusive with filter: when both are supplied, filter wins.'),
            'filter' => $schema->object()->description('Structured advanced-search filter payload. Same shape the UI Advanced Search modal posts.'),
            'company_id' => $schema->number()->description('Filter by company ID. Alternative: company_name.'),
            'company_name' => $schema->string()->description('Filter by company name. Resolved to an ID server-side.'),
            'category_id' => $schema->number()->description('Filter by category ID. Alternative: category_name.'),
            'category_name' => $schema->string()->description('Filter by category name. Resolved to an ID server-side.'),
            'manufacturer_id' => $schema->number()->description('Filter by manufacturer ID. Alternative: manufacturer_name.'),
            'manufacturer_name' => $schema->string()->description('Filter by manufacturer name. Resolved to an ID server-side.'),
            'supplier_id' => $schema->number()->description('Filter by supplier ID. Alternative: supplier_name.'),
            'supplier_name' => $schema->string()->description('Filter by supplier name. Resolved to an ID server-side.'),
            'location_id' => $schema->number()->description('Filter by location ID. Alternative: location_name.'),
            'location_name' => $schema->string()->description('Filter by location name. Resolved to an ID server-side.'),
            'low_stock_only' => $schema->boolean()->description('When true, return only accessories whose remaining quantity is at or below their configured min_amt threshold. Use for reorder reports. Archived accessories are always excluded from this filter.'),
            'include_archived' => $schema->boolean()->description('When true, include archived accessories in the results. Default matches the UI\'s accessoriesForShow() scope: archived rows are hidden unless the admin has flipped the show_archived_in_list setting on.'),
            'archived_only' => $schema->boolean()->description('When true, return ONLY archived accessories. Overrides include_archived.'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: '.config('app.max_results').')'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching accessories')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'accessories' => $schema->array()->description('Matched accessory rows including qty, min_amt, remaining (computed), supplier, and notes.'),
        ];
    }
}
