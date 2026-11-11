<?php

namespace App\Mcp\Tools;

use App\Http\Requests\ConsumableIndexRequest;
use App\Models\Consumable;
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

#[Name('list_consumables')]
#[Title('List Consumables')]
#[Description('Search and list Snipe-IT consumables with optional filtering by keyword, company, category, manufacturer, location, and pagination')]
class ListConsumablesTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('index', Consumable::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Shared filter rules live on ConsumableIndexRequest::rulesArray().
        // MCP-specific pagination stays inline. Max limit tracks the API's
        // config('app.max_results') so both surfaces honor the same env.
        try {
            $request->validate(array_merge(ConsumableIndexRequest::rulesArray(), [
                'limit' => 'nullable|integer|min:1|max:'.config('app.max_results'),
                'offset' => 'nullable|integer|min:0',
            ]));
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $consumables = Consumable::with('company', 'category', 'manufacturer', 'defaultSupplier', 'location')
            ->withCount('users as users_count');

        // filter wins when both are present, matching the HTTP API contract.
        if ($request->filled('filter')) {
            $consumables->TextSearch($request->get('filter'));
        } elseif ($request->filled('search')) {
            $consumables->TextSearch($request->get('search'));
        }

        if ($request->filled('company_id')) {
            $consumables->where('consumables.company_id', $request->get('company_id'));
        }

        if ($request->filled('category_id')) {
            $consumables->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $consumables->where('manufacturer_id', $request->get('manufacturer_id'));
        }

        if ($request->filled('location_id')) {
            $consumables->where('location_id', $request->get('location_id'));
        }

        if ($request->boolean('low_stock_only')) {
            // Current remaining = qty minus checkouts (consumables_users rows
            // where consumed_at is null). Items where remaining <= min_amt
            // are "low stock" per Snipe-IT's reporting conventions. Done as
            // a subquery so the SQL side filters the set before pagination.
            $consumables->whereRaw(
                '(consumables.qty - (SELECT COUNT(*) FROM consumables_users WHERE consumables_users.consumable_id = consumables.id AND consumables_users.consumed_at IS NULL)) <= COALESCE(consumables.min_amt, 0)'
            );
        }

        $total = $consumables->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $consumables->orderBy('consumables.created_at', 'desc')->skip($offset)->take($limit)->get();

        $consumablesData = $results->map(fn (Consumable $consumable) => [
            'id' => $consumable->id,
            'name' => $consumable->name,
            'model_number' => $consumable->model_number,
            'item_no' => $consumable->item_no,
            'qty' => $consumable->qty,
            'min_amt' => $consumable->min_amt,
            'remaining' => $consumable->numRemaining(),
            'users_count' => $consumable->users_count,
            'category' => $consumable->category?->name,
            'category_id' => $consumable->category_id,
            'manufacturer' => $consumable->manufacturer?->name,
            'manufacturer_id' => $consumable->manufacturer_id,
            'supplier' => $consumable->defaultSupplier?->name,
            'default_supplier_id' => $consumable->default_supplier_id,
            'company' => $consumable->company?->name,
            'company_id' => $consumable->company_id,
            'location' => $consumable->location?->name,
            'location_id' => $consumable->location_id,
            'purchase_cost' => $consumable->purchase_cost,
            'purchase_date' => $consumable->purchase_date?->format('Y-m-d'),
            'notes' => $consumable->notes,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_consumables', ['total' => $total, 'count' => count($consumablesData)]))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'consumables' => $consumablesData,
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
            'location_id' => $schema->number()->description('Filter by location ID'),
            'low_stock_only' => $schema->boolean()->description('When true, return only consumables whose remaining quantity is at or below their configured min_amt threshold. Use for reorder reports.'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: '.config('app.max_results').')'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching consumables')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'consumables' => $schema->array()->description('Matched consumable rows including qty, min_amt, remaining (computed), supplier, and notes.'),
        ];
    }
}
