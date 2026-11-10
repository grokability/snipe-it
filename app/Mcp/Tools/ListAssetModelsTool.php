<?php

namespace App\Mcp\Tools;

use App\Models\AssetModel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_asset_models')]
#[Title('List Asset Models')]
#[Description('Search and list Snipe-IT asset models with optional filtering and pagination')]
class ListAssetModelsTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('view', AssetModel::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
            'manufacturer_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $models = AssetModel::with('category', 'manufacturer', 'depreciation')
            ->withCount('assets as assets_count');

        if ($request->filled('search')) {
            $models->TextSearch($request->get('search'));
        }

        if ($request->filled('category_id')) {
            $models->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $models->where('manufacturer_id', $request->get('manufacturer_id'));
        }

        $models->orderBy('models.created_at', 'desc');

        $total = $models->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $models->skip($offset)->take($limit)->get();

        $modelsData = $results->map(fn (AssetModel $model) => [
            'id' => $model->id,
            'name' => $model->name,
            'model_number' => $model->model_number,
            'category_id' => $model->category_id,
            'category' => $model->category?->name,
            'manufacturer_id' => $model->manufacturer_id,
            'manufacturer' => $model->manufacturer?->name,
            'assets_count' => $model->assets_count,
            'eol' => $model->eol,
            'min_amt' => $model->min_amt,
        ])->values()->all();

        return Response::make(
            Response::text("Found {$total} asset models, returning ".count($modelsData))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'models' => $modelsData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword to search across model name, model number'),
            'category_id' => $schema->number()->description('Filter by category ID'),
            'manufacturer_id' => $schema->number()->description('Filter by manufacturer ID'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching asset models')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'models' => $schema->array()->description('List of asset models')->required(),
        ];
    }
}
