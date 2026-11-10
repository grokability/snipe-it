<?php

namespace App\Mcp\Tools;

use App\Models\Asset;
use App\Models\Maintenance;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_maintenances')]
#[Title('List Maintenances')]
#[Description('List asset maintenances with optional filtering by asset and pagination')]
class ListMaintenancesTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('view', Asset::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $request->validate([
            'asset_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $maintenances = Maintenance::with('asset', 'supplier');

        if ($request->filled('asset_id')) {
            $maintenances->where('asset_id', $request->get('asset_id'));
        }

        $maintenances->orderBy('created_at', 'desc');

        $total = $maintenances->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $maintenances->skip($offset)->take($limit)->get();

        $maintenancesData = $results->map(fn (Maintenance $maintenance) => [
            'id' => $maintenance->id,
            'title' => $maintenance->name,
            'asset_id' => $maintenance->asset_id,
            'asset_tag' => $maintenance->asset?->asset_tag,
            'is_warranty' => (bool) $maintenance->is_warranty,
            'cost' => $maintenance->cost,
            'start_date' => $maintenance->start_date,
            'completion_date' => $maintenance->completion_date,
            'supplier' => $maintenance->supplier?->name,
            'notes' => $maintenance->notes,
        ])->values()->all();

        return Response::make(
            Response::text("Found {$total} maintenances, returning ".count($maintenancesData))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'maintenances' => $maintenancesData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'asset_id' => $schema->number()->description('Filter by asset ID'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching maintenances')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'maintenances' => $schema->array()->description('List of maintenances')->required(),
        ];
    }
}
