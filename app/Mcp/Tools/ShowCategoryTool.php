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

#[Name('show_category')]
#[Title('Show Category Details')]
#[Description('Look up a single Snipe-IT category by numeric ID or name and return its full details')]
class ShowCategoryTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $withCounts = [
            'showableAssets as assets_count',
            'accessories as accessories_count',
            'consumables as consumables_count',
            'components as components_count',
            'licenses as licenses_count',
        ];

        $category = null;

        if ($request->filled('id')) {
            $category = Category::withCount($withCounts)->find($request->get('id'));
        } elseif ($request->filled('name')) {
            $category = Category::withCount($withCounts)->where('name', $request->get('name'))->first();
        } else {
            return Response::make(Response::error(trans('mcp.id_or_name_required')));
        }

        if (! $category) {
            return Response::make(Response::error(trans('mcp.category_not_found')));
        }

        if (! Gate::allows('view', $category)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        return Response::make(
            Response::text(trans('mcp.category_found', ['name' => $category->name]))
        )->withStructuredContent([
            'id' => $category->id,
            'name' => $category->name,
            'category_type' => $category->category_type,
            'assets_count' => $category->assets_count,
            'accessories_count' => $category->accessories_count,
            'consumables_count' => $category->consumables_count,
            'components_count' => $category->components_count,
            'licenses_count' => $category->licenses_count,
            'notes' => $category->notes,
            'created_at' => $category->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $category->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the category to look up'),
            'name' => $schema->string()->description('Name of the category to look up'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric category ID'),
            'name' => $schema->string()->description('Category name'),
            'category_type' => $schema->string()->description('Category type: asset, accessory, consumable, component, or license'),
            'assets_count' => $schema->number()->description('Number of assets in this category'),
            'accessories_count' => $schema->number()->description('Number of accessories in this category'),
            'consumables_count' => $schema->number()->description('Number of consumables in this category'),
            'components_count' => $schema->number()->description('Number of components in this category'),
            'licenses_count' => $schema->number()->description('Number of licenses in this category'),
            'notes' => $schema->string()->description('Notes'),
            'created_at' => $schema->string()->description('Creation timestamp'),
            'updated_at' => $schema->string()->description('Last update timestamp'),
        ];
    }
}
