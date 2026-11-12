<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Http\Requests\MaintenanceIndexRequest;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Asset;
use App\Models\Maintenance;
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

#[Name('list_maintenances')]
#[Title('List Maintenances')]
#[Description('List asset maintenances with optional filtering by asset and pagination')]
class ListMaintenancesTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Read)) {
            return $denied;
        }

        if (! Gate::allows('view', Asset::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Shared filter rules live on MaintenanceIndexRequest::rulesArray()
        // so any future rule change stays in sync for both the MCP tool
        // and the HTTP API endpoint once it adopts the FormRequest.
        // MCP-specific pagination (limit, offset) and sort enum stay
        // inline because they are presentation concerns scoped to this
        // tool, not shared with the HTTP response shape.
        try {
            $request->validate(array_merge(MaintenanceIndexRequest::rulesArray(), [
                'sort' => 'nullable|string|in:recent,oldest,start_date,completion_date',
                'limit' => 'nullable|integer|min:1|max:'.config('app.max_results'),
                'offset' => 'nullable|integer|min:0',
            ]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        // Resolve supplier_name to supplier_id and merge back onto the
        // request so the existing filled() read below keeps working
        // unchanged. asset_id / asset_tag already have a name path and
        // are handled in their own block below.
        $fkMap = [
            ['supplier_id', 'supplier_name', Supplier::class],
        ];

        $resolvedFks = [];
        foreach ($fkMap as [$idField, $nameField, $modelClass]) {
            $resolved = $this->resolveFkByName($request, $idField, $nameField, $modelClass);
            if ($this->fkResolutionFailed($resolved)) {
                return Response::make(Response::error($this->fkErrorMessage($resolved)));
            }
            if ($resolved !== null) {
                $resolvedFks[$idField] = $resolved;
            }
        }
        $request->merge($resolvedFks);

        // The maintenances table went polymorphic after this tool was
        // first written: `asset_id` + `completion_date` were renamed to
        // `item_id` + `item_type` + `completed_at` (and
        // `expected_completion_date` for the planned-done date). Model-
        // level accessors keep `asset_id` / `completion_date` readable,
        // but WHERE clauses bypass accessors so queries here go against
        // the real column names.
        $maintenances = Maintenance::with('item', 'supplier');

        if ($request->filled('asset_id')) {
            $maintenances->where('item_id', $request->get('asset_id'))
                ->where('item_type', Asset::class);
        }

        if ($request->filled('asset_tag')) {
            $asset = Asset::where('asset_tag', $request->get('asset_tag'))->first();
            if (! $asset) {
                return Response::make(Response::error(trans('mcp.asset_not_found')));
            }
            $maintenances->where('item_id', $asset->id)->where('item_type', Asset::class);
        }

        if ($request->filled('supplier_id')) {
            $maintenances->where('supplier_id', $request->get('supplier_id'));
        }

        match ($request->get('status', 'all')) {
            'open' => $maintenances->whereNull('completed_at'),
            'completed' => $maintenances->whereNotNull('completed_at'),
            default => null,
        };

        if ($request->has('is_warranty')) {
            $maintenances->where('is_warranty', $request->boolean('is_warranty'));
        }

        if ($request->filled('start_date_from')) {
            $maintenances->where('start_date', '>=', $request->get('start_date_from'));
        }

        if ($request->filled('start_date_to')) {
            $maintenances->where('start_date', '<=', $request->get('start_date_to'));
        }

        if ($request->filled('completion_date_from')) {
            $maintenances->where('completed_at', '>=', $request->get('completion_date_from'));
        }

        if ($request->filled('completion_date_to')) {
            $maintenances->where('completed_at', '<=', $request->get('completion_date_to'));
        }

        match ($request->get('sort', 'recent')) {
            'oldest' => $maintenances->orderBy('created_at', 'asc'),
            'start_date' => $maintenances->orderBy('start_date', 'desc'),
            'completion_date' => $maintenances->orderBy('completed_at', 'desc'),
            default => $maintenances->orderBy('created_at', 'desc'),
        };

        $total = $maintenances->count();
        $limit = $request->filled('limit') ? max(1, min(config('app.max_results'), (int) $request->get('limit'))) : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $maintenances->skip($offset)->take($limit)->get();

        $maintenancesData = $results->map(fn (Maintenance $maintenance) => [
            'id' => $maintenance->id,
            'name' => $maintenance->name,
            'item_type' => $maintenance->item_type ? class_basename($maintenance->item_type) : null,
            'item_id' => $maintenance->item_id,
            'asset_tag' => $maintenance->item instanceof Asset ? $maintenance->item->asset_tag : null,
            'is_warranty' => (bool) $maintenance->is_warranty,
            'cost' => $maintenance->cost,
            'start_date' => $maintenance->start_date,
            'expected_completion_date' => optional($maintenance->expected_completion_date)->toDateString(),
            'completed_at' => optional($maintenance->completed_at)->toDateTimeString(),
            'supplier' => $maintenance->supplier?->name,
            'supplier_id' => $maintenance->supplier_id,
            'maintenance_type_id' => $maintenance->maintenance_type_id,
            'notes' => $maintenance->notes,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_maintenances', ['total' => $total, 'count' => count($maintenancesData)]))
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
            'asset_id' => $schema->number()->description('Filter by numeric asset ID'),
            'asset_tag' => $schema->string()->description('Filter by asset tag. Resolved to the matching asset_id server-side.'),
            'supplier_id' => $schema->number()->description('Filter by the supplier that performed the maintenance. Alternative: supplier_name.'),
            'supplier_name' => $schema->string()->description('Filter by the supplier name. Resolved to an ID server-side.'),
            'status' => $schema->string()->description('Filter by completion status: "open" (completed_at is null), "completed" (completed_at is set), or "all" (default)'),
            'is_warranty' => $schema->boolean()->description('Filter to warranty vs non-warranty maintenances'),
            'start_date_from' => $schema->string()->description('Return maintenances whose start_date is on or after this date (YYYY-MM-DD)'),
            'start_date_to' => $schema->string()->description('Return maintenances whose start_date is on or before this date (YYYY-MM-DD)'),
            'completion_date_from' => $schema->string()->description('Return maintenances whose actual completed_at timestamp is on or after this date (YYYY-MM-DD)'),
            'completion_date_to' => $schema->string()->description('Return maintenances whose actual completed_at timestamp is on or before this date (YYYY-MM-DD)'),
            'sort' => $schema->string()->description('Sort order: "recent" (default, most recently created), "oldest", "start_date" (newest start_date first), or "completion_date"'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: '.config('app.max_results').')'),
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
