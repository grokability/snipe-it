<?php

namespace App\Mcp\Tools;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_categories')]
#[Title('List Categories')]
#[Description('Search and list Snipe-IT categories with optional filtering by type and pagination')]
class ListCategoriesTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('view', Category::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'category_type' => 'nullable|string|in:asset,accessory,consumable,component,license',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $categories = Category::withCount(
            'showableAssets as assets_count',
            'accessories as accessories_count',
            'consumables as consumables_count',
            'components as components_count',
            'licenses as licenses_count'
        );

        if ($request->filled('search')) {
            $categories->TextSearch($request->get('search'));
        }

        if ($request->filled('category_type')) {
            $categories->where('category_type', $request->get('category_type'));
        }

        $categories->orderBy('created_at', 'desc');

        $total = $categories->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $categories->skip($offset)->take($limit)->get();

        $categoriesData = $results->map(fn (Category $category) => [
            'id' => $category->id,
            'name' => $category->name,
            'category_type' => $category->category_type,
            'assets_count' => $category->assets_count,
            'accessories_count' => $category->accessories_count,
            'consumables_count' => $category->consumables_count,
            'components_count' => $category->components_count,
            'licenses_count' => $category->licenses_count,
        ])->values()->all();

        return Response::make(
            Response::text("Found {$total} categories, returning ".count($categoriesData))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'categories' => $categoriesData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword to search across category name, type, notes'),
            'category_type' => $schema->string()->description('Filter by type: asset, accessory, consumable, component, or license'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching categories')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'categories' => $schema->array()->description('List of categories')->required(),
        ];
    }
}
