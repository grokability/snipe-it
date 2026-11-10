<?php

namespace App\Mcp\Tools;

use App\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_suppliers')]
#[Title('List Suppliers')]
#[Description('Search and list Snipe-IT suppliers with optional filtering and pagination')]
class ListSuppliersTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('view', Supplier::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $suppliers = Supplier::withCount(
            'assets as assets_count',
            'licenses as licenses_count'
        );

        if ($request->filled('search')) {
            $suppliers->TextSearch($request->get('search'));
        }

        $suppliers->orderBy('created_at', 'desc');

        $total = $suppliers->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $suppliers->skip($offset)->take($limit)->get();

        $suppliersData = $results->map(fn (Supplier $supplier) => [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'address' => $supplier->address,
            'city' => $supplier->city,
            'state' => $supplier->state,
            'country' => $supplier->country,
            'phone' => $supplier->phone,
            'email' => $supplier->email,
            'url' => $supplier->url,
            'assets_count' => $supplier->assets_count,
            'licenses_count' => $supplier->licenses_count,
        ])->values()->all();

        return Response::make(
            Response::text("Found {$total} suppliers, returning ".count($suppliersData))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'suppliers' => $suppliersData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword to search across supplier fields'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching suppliers')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'suppliers' => $schema->array()->description('List of suppliers')->required(),
        ];
    }
}
