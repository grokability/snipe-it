<?php

namespace App\Mcp\Tools;

use App\Models\Asset;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_assets')]
#[Title('List Assets')]
#[Description('Search and list Snipe-IT assets with optional filtering by keyword, status, and pagination')]
class ListAssetsTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'status_type' => 'nullable|string|in:RTD,Deployed,Archived,Pending,Undeployable',
            'company_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'model_id' => 'nullable|integer',
            'manufacturer_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $assets = Asset::select('assets.*')
            ->with('status', 'assignedTo', 'model.category', 'model.manufacturer', 'location', 'company');

        match ($request->filled('status_type') ? $request->get('status_type') : null) {
            'RTD' => $assets->rtd(),
            'Deployed' => $assets->deployed(),
            'Archived' => $assets->archived(),
            'Pending' => $assets->pending(),
            'Undeployable' => $assets->undeployable(),
            default => $assets->notArchived(),
        };

        if ($request->filled('search')) {
            $assets->TextSearch($request->get('search'));
        }

        if ($request->filled('company_id')) {
            $assets->where('assets.company_id', '=', $request->get('company_id'));
        }

        if ($request->filled('location_id')) {
            $assets->where('assets.location_id', '=', $request->get('location_id'));
        }

        if ($request->filled('category_id')) {
            $assets->inCategory($request->get('category_id'));
        }

        if ($request->filled('model_id')) {
            $assets->inModels([$request->get('model_id')]);
        }

        if ($request->filled('manufacturer_id')) {
            $assets->byManufacturer($request->get('manufacturer_id'));
        }

        $assets->orderBy('assets.created_at', 'desc');

        $total = $assets->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $assets->skip($offset)->take($limit)->get();

        $assetsData = $results->map(fn (Asset $asset) => [
            'id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
            'name' => $asset->name,
            'serial' => $asset->serial,
            'status' => $asset->status?->name,
            'status_type' => $asset->status?->getStatuslabelType(),
            'model' => $asset->model?->name,
            'category' => $asset->model?->category?->name,
            'manufacturer' => $asset->model?->manufacturer?->name,
            'company' => $asset->company?->name,
            'location' => $asset->location?->name,
            'assigned_to_id' => $asset->assigned_to,
            'assigned_to_type' => $asset->assigned_type ? class_basename($asset->assigned_type) : null,
        ])->values()->all();

        return Response::make(
            Response::text("Found {$total} assets, returning ".count($assetsData))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'assets' => $assetsData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()
                ->description('Keyword to search across asset tag, serial, name, and model'),
            'status_type' => $schema->string()
                ->description('Filter by status type: RTD (ready to deploy), Deployed, Archived, Pending, or Undeployable'),
            'company_id' => $schema->number()
                ->description('Filter by company ID'),
            'location_id' => $schema->number()
                ->description('Filter by location ID'),
            'category_id' => $schema->number()
                ->description('Filter by category ID'),
            'model_id' => $schema->number()
                ->description('Filter by model ID'),
            'manufacturer_id' => $schema->number()
                ->description('Filter by manufacturer ID'),
            'limit' => $schema->number()
                ->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()
                ->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching assets')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
        ];
    }
}
