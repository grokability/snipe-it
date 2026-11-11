<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Asset;
use App\Models\MaintenanceType;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_maintenance_types')]
#[Title('List Maintenance Types')]
#[Description('List configured maintenance types (the taxonomy that create_maintenance requires via maintenance_type_id). Use this to look up the right id before creating a maintenance so you do not create duplicate type rows.')]
class ListMaintenanceTypesTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Read)) {
            return $denied;
        }

        // Maintenance types are viewed in the context of asset
        // maintenances, so this inherits the assets.view gate rather
        // than looking up a maintenances.view permission that does not
        // exist in Snipe-IT. Matches how MaintenancePolicy::index gates.
        if (! Gate::allows('view', Asset::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:'.config('app.max_results'),
            'offset' => 'nullable|integer|min:0',
        ]);

        $types = MaintenanceType::query();

        if ($request->filled('search')) {
            $types->TextSearch($request->get('search'));
        }

        $types->orderBy('name', 'asc');

        $total = $types->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $types->skip($offset)->take($limit)->get();

        $typesData = $results->map(fn (MaintenanceType $type) => [
            'id' => $type->id,
            'name' => $type->name,
            'tag_color' => $type->tag_color,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_maintenance_types', ['total' => $total, 'count' => count($typesData)]))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'maintenance_types' => $typesData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword search across maintenance type name and tag_color. Use this to probe for close matches ("repair", "upgrade", etc) before deciding to create a new type.'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: '.config('app.max_results').')'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching maintenance types')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'maintenance_types' => $schema->array()->description('List of maintenance types with id, name, and tag_color.')->required(),
        ];
    }
}
