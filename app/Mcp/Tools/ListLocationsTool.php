<?php

namespace App\Mcp\Tools;

use App\Models\Location;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_locations')]
#[Title('List Locations')]
#[Description('Search and list Snipe-IT locations with optional filtering and pagination')]
class ListLocationsTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('view', Location::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $locations = Location::with('parent')->withCount(
            'assets as assets_count',
            'users as users_count',
            'children as children_count'
        );

        if ($request->filled('search')) {
            $locations->TextSearch($request->get('search'));
        }

        if ($request->filled('parent_id')) {
            $locations->where('parent_id', $request->get('parent_id'));
        }

        $locations->orderBy('created_at', 'desc');

        $total = $locations->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $locations->skip($offset)->take($limit)->get();

        $locationsData = $results->map(fn (Location $location) => [
            'id' => $location->id,
            'name' => $location->name,
            'address' => $location->address,
            'city' => $location->city,
            'state' => $location->state,
            'country' => $location->country,
            'zip' => $location->zip,
            'phone' => $location->phone,
            'parent_id' => $location->parent_id,
            'parent' => $location->parent?->name,
            'assets_count' => $location->assets_count,
            'users_count' => $location->users_count,
            'children_count' => $location->children_count,
        ])->values()->all();

        return Response::make(
            Response::text("Found {$total} locations, returning ".count($locationsData))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'locations' => $locationsData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword to search across location name, city, state, country'),
            'parent_id' => $schema->number()->description('Filter by parent location ID'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching locations')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'locations' => $schema->array()->description('List of locations')->required(),
        ];
    }
}
