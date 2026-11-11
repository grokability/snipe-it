<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Http\Requests\AssetIndexRequest;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Asset;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
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
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Read)) {
            return $denied;
        }

        if (! Gate::allows('index', Asset::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Shared filter rules live on AssetIndexRequest::rulesArray()
        // so any future rule change stays in sync for both the MCP tool
        // and the HTTP API endpoint once it adopts the FormRequest.
        // MCP-specific pagination stays inline.
        try {
            $request->validate(array_merge(AssetIndexRequest::rulesArray(), [
                'limit' => 'nullable|integer|min:1|max:'.config('app.max_results'),
                'offset' => 'nullable|integer|min:0',
            ]));
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $assets = Asset::select('assets.*')
            ->with('status', 'assignedTo', 'model.category', 'model.manufacturer', 'location', 'defaultLoc', 'company', 'supplier')
            ->withCount([
                'licenses as licenses_count',
                'components as components_count',
                'maintenances as maintenances_count',
                'assignedAssets as assigned_assets_count' => fn ($query) => $query->withoutTrashed(),
            ]);

        match ($request->filled('status_type') ? $request->get('status_type') : null) {
            'RTD' => $assets->rtd(),
            'Deployed' => $assets->deployed(),
            'Archived' => $assets->archived(),
            'Pending' => $assets->pending(),
            'Undeployable' => $assets->undeployable(),
            default => $assets->notArchived(),
        };

        // filter wins when both are present, matching the HTTP API contract.
        // filter is the structured advanced-search payload (array shape:
        // ['field_name' => 'value'] or more complex comparators per
        // Searchable::applySearchFilters). search is free-text keyword.
        if ($request->filled('filter')) {
            $assets->TextSearch($request->get('filter'));
        } elseif ($request->filled('search')) {
            $assets->TextSearch($request->get('search'));
        }

        if ($request->filled('company_id')) {
            $assets->where('assets.company_id', '=', $request->get('company_id'));
        }

        if ($request->filled('location_id')) {
            $assets->where('assets.location_id', '=', $request->get('location_id'));
        }

        if ($request->filled('rtd_location_id')) {
            $assets->where('assets.rtd_location_id', '=', $request->get('rtd_location_id'));
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

        if ($request->filled('supplier_id')) {
            $assets->where('assets.supplier_id', '=', $request->get('supplier_id'));
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
            'model_id' => $asset->model_id,
            'category' => $asset->model?->category?->name,
            'manufacturer' => $asset->model?->manufacturer?->name,
            'supplier' => $asset->supplier?->name,
            'company' => $asset->company?->name,
            'location' => $asset->location?->name,
            'location_id' => $asset->location_id,
            'rtd_location' => $asset->defaultLoc?->name,
            'rtd_location_id' => $asset->rtd_location_id,
            'assigned_to_id' => $asset->assigned_to,
            'assigned_to_type' => $asset->assigned_type ? class_basename($asset->assigned_type) : null,

            // Date and lifecycle fields the UI advanced search already
            // exposes. Including them inline saves the AI an extra
            // show_asset call per row when building replace / EOL /
            // warranty reports.
            'purchase_date' => $asset->purchase_date?->toDateString(),
            'purchase_cost' => $asset->purchase_cost,
            'warranty_months' => $asset->warranty_months,
            'warranty_expires' => $asset->warranty_months > 0 ? optional($asset->warranty_expires)->toDateString() : null,
            'asset_eol_date' => optional($asset->asset_eol_date)->toDateString(),
            'last_checkout' => optional($asset->last_checkout)->toDateString(),
            'last_checkin' => optional($asset->last_checkin)->toDateString(),
            'last_audit_date' => optional($asset->last_audit_date)->toDateString(),
            'next_audit_date' => optional($asset->next_audit_date)->toDateString(),
            'notes' => $asset->notes,

            // Child counts the UI shows in bs-table columns.
            'licenses_count' => $asset->licenses_count,
            'components_count' => $asset->components_count,
            'maintenances_count' => $asset->maintenances_count,
            'assigned_assets_count' => $asset->assigned_assets_count,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_assets', ['total' => $total, 'count' => count($assetsData)]))
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
                ->description('Free-text keyword to search across asset tag, serial, name, and model. Mutually exclusive with filter: when both are supplied, filter wins.'),
            'filter' => $schema->object()
                ->description('Structured advanced-search filter payload. Same shape the UI Advanced Search modal posts. Example: {"asset_tag":"AST-", "warranty_expires":"2026-01-01 - 2026-12-31", "asset_eol_date":"2026-01-01 - 2027-12-31", "status_id":"4", "category":"1,2,3"}. Supports date ranges ("start - end"), enum lists (comma-joined IDs), custom fields by their db_column name (_snipeit_*), and the standard columns listed in Asset::$allowed_columns.'),
            'status_type' => $schema->string()
                ->description('Filter by status type: RTD (ready to deploy), Deployed, Archived, Pending, or Undeployable'),
            'company_id' => $schema->number()
                ->description('Filter by company ID'),
            'location_id' => $schema->number()
                ->description('Filter by current location ID (where the asset physically is right now)'),
            'rtd_location_id' => $schema->number()
                ->description('Filter by default (Ready to Deploy) location ID. Use this when looking for assets whose home base is a given location, regardless of where they are checked out to right now.'),
            'category_id' => $schema->number()
                ->description('Filter by category ID'),
            'model_id' => $schema->number()
                ->description('Filter by model ID'),
            'manufacturer_id' => $schema->number()
                ->description('Filter by manufacturer ID'),
            'supplier_id' => $schema->number()
                ->description('Filter by supplier ID'),
            'limit' => $schema->number()
                ->description('Number of results to return (default: 25, max: '.config('app.max_results').')'),
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
            'assets' => $schema->array()->description('Matched asset rows including lifecycle fields (purchase_date, warranty_expires, asset_eol_date), last-* timestamps, and child-item counts.'),
        ];
    }
}
