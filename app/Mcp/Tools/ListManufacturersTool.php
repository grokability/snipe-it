<?php

namespace App\Mcp\Tools;

use App\Models\Manufacturer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_manufacturers')]
#[Title('List Manufacturers')]
#[Description('Search and list Snipe-IT manufacturers with optional filtering and pagination')]
class ListManufacturersTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('view', Manufacturer::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $manufacturers = Manufacturer::withCount(
            'assets as assets_count',
            'licenses as licenses_count',
            'accessories as accessories_count',
            'components as components_count'
        );

        if ($request->filled('search')) {
            $manufacturers->TextSearch($request->get('search'));
        }

        $manufacturers->orderBy('created_at', 'desc');

        $total = $manufacturers->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $manufacturers->skip($offset)->take($limit)->get();

        $manufacturersData = $results->map(fn (Manufacturer $manufacturer) => [
            'id' => $manufacturer->id,
            'name' => $manufacturer->name,
            'url' => $manufacturer->url,
            'support_url' => $manufacturer->support_url,
            'support_email' => $manufacturer->support_email,
            'support_phone' => $manufacturer->support_phone,
            'assets_count' => $manufacturer->assets_count,
            'licenses_count' => $manufacturer->licenses_count,
            'accessories_count' => $manufacturer->accessories_count,
            'components_count' => $manufacturer->components_count,
        ])->values()->all();

        return Response::make(
            Response::text("Found {$total} manufacturers, returning ".count($manufacturersData))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'manufacturers' => $manufacturersData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword to search across manufacturer name and notes'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching manufacturers')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'manufacturers' => $schema->array()->description('List of manufacturers')->required(),
        ];
    }
}
