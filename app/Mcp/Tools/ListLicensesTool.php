<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Http\Requests\LicenseIndexRequest;
use App\Mcp\Traits\HasMcpScope;
use App\Models\License;
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

#[Name('list_licenses')]
#[Title('List Licenses')]
#[Description('Search and list Snipe-IT licenses with optional filtering by keyword, company, category, and manufacturer')]
class ListLicensesTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Read)) {
            return $denied;
        }

        if (! Gate::allows('index', License::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Shared filter rules live on LicenseIndexRequest::rulesArray().
        // MCP-specific pagination stays inline. Max limit tracks the API's
        // config('app.max_results') so both surfaces honor the same env.
        try {
            $request->validate(array_merge(LicenseIndexRequest::rulesArray(), [
                'limit' => 'nullable|integer|min:1|max:'.config('app.max_results'),
                'offset' => 'nullable|integer|min:0',
            ]));
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $licenses = License::with('company', 'manufacturer', 'supplier', 'category', 'depreciation')
            ->withCount('freeSeats as free_seats_count');

        // filter wins when both are present, matching the HTTP API contract.
        if ($request->filled('filter')) {
            $licenses->TextSearch($request->get('filter'));
        } elseif ($request->filled('search')) {
            $licenses->TextSearch($request->get('search'));
        }

        if ($request->filled('company_id')) {
            $licenses->where('licenses.company_id', '=', $request->get('company_id'));
        }

        if ($request->filled('category_id')) {
            $licenses->where('category_id', '=', $request->get('category_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $licenses->where('manufacturer_id', '=', $request->get('manufacturer_id'));
        }

        if ($request->filled('supplier_id')) {
            $licenses->where('supplier_id', '=', $request->get('supplier_id'));
        }

        if ($request->filled('depreciation_id')) {
            $licenses->where('depreciation_id', '=', $request->get('depreciation_id'));
        }

        if ($request->filled('expiring_within_days')) {
            $days = (int) $request->get('expiring_within_days');
            $licenses->whereNotNull('expiration_date')
                ->whereBetween('expiration_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
        }

        if ($request->boolean('fully_consumed_only')) {
            // Licenses with zero free seats remaining. Uses a HAVING on the
            // free_seats_count that was already withCount'd above.
            $licenses->having('free_seats_count', '=', 0);
        }

        $licenses->orderBy('licenses.created_at', 'desc');

        $total = $licenses->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $licenses->skip($offset)->take($limit)->get();

        $licensesData = $results->map(fn (License $license) => [
            'id' => $license->id,
            'name' => $license->name,
            'serial' => $license->serial,
            'seats' => $license->seats,
            'free_seats' => $license->free_seats_count,
            'category' => $license->category?->name,
            'category_id' => $license->category_id,
            'manufacturer' => $license->manufacturer?->name,
            'manufacturer_id' => $license->manufacturer_id,
            'company' => $license->company?->name,
            'company_id' => $license->company_id,
            'supplier' => $license->supplier?->name,
            'supplier_id' => $license->supplier_id,
            'depreciation' => $license->depreciation?->name,
            'depreciation_id' => $license->depreciation_id,
            'order_number' => $license->order_number,
            'license_name' => $license->license_name,
            'license_email' => $license->license_email,
            'maintained' => (bool) $license->maintained,
            'reassignable' => (bool) $license->reassignable,
            'expiration_date' => $license->expiration_date?->format('Y-m-d'),
            'termination_date' => $license->termination_date?->format('Y-m-d'),
            'purchase_date' => $license->purchase_date?->format('Y-m-d'),
            'purchase_cost' => $license->purchase_cost,
            'min_amt' => $license->min_amt,
            'notes' => $license->notes,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_licenses', ['total' => $total, 'count' => count($licensesData)]))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'licenses' => $licensesData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Free-text keyword. Mutually exclusive with filter: when both are supplied, filter wins.'),
            'filter' => $schema->object()->description('Structured advanced-search filter payload. Same shape the UI Advanced Search modal posts.'),
            'company_id' => $schema->number()->description('Filter by company ID'),
            'category_id' => $schema->number()->description('Filter by category ID'),
            'manufacturer_id' => $schema->number()->description('Filter by manufacturer ID'),
            'supplier_id' => $schema->number()->description('Filter by supplier ID'),
            'depreciation_id' => $schema->number()->description('Filter by depreciation schedule ID'),
            'expiring_within_days' => $schema->number()->description('Return only licenses whose expiration_date is between today and (today + N days). Use for renewal-due reports.'),
            'fully_consumed_only' => $schema->boolean()->description('Return only licenses with zero free seats remaining.'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: '.config('app.max_results').')'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching licenses')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'licenses' => $schema->array()->description('Matched license rows including seat counts, expiration/termination dates, purchase_cost, maintained/reassignable flags, and notes.'),
        ];
    }
}
