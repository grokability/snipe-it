<?php

namespace App\Mcp\Tools;

use App\Models\Consumable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
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
            return Response::make(Response::error('Unauthorized'));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'company_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'manufacturer_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $consumables = Consumable::with('company', 'category', 'manufacturer', 'supplier', 'location')
            ->withCount('users as users_count');

        if ($request->filled('search')) {
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

        $total = $consumables->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $consumables->orderBy('consumables.created_at', 'desc')->skip($offset)->take($limit)->get();

        $consumablesData = $results->map(fn (Consumable $consumable) => [
            'id' => $consumable->id,
            'name' => $consumable->name,
            'qty' => $consumable->qty,
            'users_count' => $consumable->users_count,
            'category' => $consumable->category?->name,
            'manufacturer' => $consumable->manufacturer?->name,
            'company' => $consumable->company?->name,
            'location' => $consumable->location?->name,
            'purchase_cost' => $consumable->purchase_cost,
            'purchase_date' => $consumable->purchase_date?->format('Y-m-d'),
        ])->values()->all();

        return Response::make(
            Response::text("Found {$total} consumables, returning ".count($consumablesData))
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
            'search' => $schema->string()->description('Keyword to search across consumable name and other fields'),
            'company_id' => $schema->number()->description('Filter by company ID'),
            'category_id' => $schema->number()->description('Filter by category ID'),
            'manufacturer_id' => $schema->number()->description('Filter by manufacturer ID'),
            'location_id' => $schema->number()->description('Filter by location ID'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching consumables')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
        ];
    }
}
