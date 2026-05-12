<?php

namespace App\Mcp\Tools;

use App\Models\Depreciation;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_depreciations')]
#[Title('List Depreciations')]
#[Description('Search and list Snipe-IT depreciation schedules with optional filtering and pagination')]
class ListDepreciationsTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('view', Depreciation::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $depreciations = Depreciation::withCount('models as models_count');

        if ($request->filled('search')) {
            $depreciations->TextSearch($request->get('search'));
        }

        $depreciations->orderBy('created_at', 'desc');

        $total = $depreciations->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $depreciations->skip($offset)->take($limit)->get();

        $depreciationsData = $results->map(fn (Depreciation $dep) => [
            'id' => $dep->id,
            'name' => $dep->name,
            'months' => $dep->months,
            'models_count' => $dep->models_count,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_depreciations', ['total' => $total, 'count' => count($depreciationsData)]))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'depreciations' => $depreciationsData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword to search across depreciation name'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching depreciations')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'depreciations' => $schema->array()->description('List of depreciation schedules')->required(),
        ];
    }
}
