<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_maintenance')]
#[Title('Create Maintenance')]
#[Description('Create a new maintenance record attached to an asset')]
class CreateMaintenanceTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('update', Asset::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Resolve name-based FK inputs to numeric IDs before validation
        // so the downstream rules + fill see a consistent shape.
        // Lookups run through the model's normal global scopes, so a
        // non-superuser in a locked-down FMCS install gets a not-found
        // error for a name that only exists in a tenant they cannot
        // see, matching the UI's "that record does not exist" posture.
        $fkMap = [
            ['maintenance_type_id', 'maintenance_type_name', MaintenanceType::class],
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

        // MCP-specific lookup: asset_id OR asset_tag. These aren't
        // persisted so they stay as an inline pre-validation step.
        try {
            Validator::make($request->all(), [
                'asset_id' => 'required_without:asset_tag|nullable|integer|exists:assets,id',
                'asset_tag' => 'required_without:asset_id|nullable|string|max:191',
            ])->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $asset = $request->filled('asset_tag')
            ? Asset::where('asset_tag', $request->get('asset_tag'))->first()
            : Asset::find($request->get('asset_id'));

        if (! $asset) {
            return Response::make(Response::error(trans('mcp.asset_not_found')));
        }

        if (! Gate::allows('update', $asset)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Build input with MCP aliases mapped to model column names.
        // The maintenances table went polymorphic so maintenances can
        // attach to any checkoutable item type. Pinning item_type to
        // Asset here matches the tool's current surface, which is
        // asset-scoped.
        $input = array_merge($request->all(), $resolvedFks, [
            'item_id' => $asset->id,
            'item_type' => Asset::class,
            // `title` is the MCP-facing name for `name`. `completion_date`
            // is a back-compat alias for `expected_completion_date`.
            'name' => $request->get('title', $request->get('name')),
            'expected_completion_date' => $request->get('expected_completion_date', $request->get('completion_date')),
            'start_date' => $request->filled('start_date') ? $request->get('start_date') : now()->format('Y-m-d'),
            // is_warranty is a NOT NULL boolean column on the table.
            // Default absent inputs to false so fill() lands an explicit
            // bool instead of leaving it null at insert time.
            'is_warranty' => $request->boolean('is_warranty'),
        ]);

        $maintenance = new Maintenance;
        try {
            $validated = Validator::make($input, $maintenance->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $maintenance->fill(array_merge($input, $validated));
        $maintenance->created_by = auth()->id();

        // Actual completion marks the maintenance as done. Snipe-IT
        // stamps completed_by to the acting user any time completed_at
        // is set, so the audit trail attributes the close-out.
        if ($request->filled('completed_at')) {
            $maintenance->completed_by = auth()->id();
        }

        if ($maintenance->save()) {
            return Response::make(
                Response::text(trans('mcp.maintenance_created', ['name' => $maintenance->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.maintenance_created', ['name' => $maintenance->name]),
                'id' => $maintenance->id,
                'name' => $maintenance->name,
                'item_type' => class_basename($maintenance->item_type),
                'item_id' => $maintenance->item_id,
                'asset_tag' => $asset->asset_tag,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $maintenance->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'asset_id' => $schema->number()->description('Numeric ID of the asset the maintenance is for. One of asset_id or asset_tag is required.'),
            'asset_tag' => $schema->string()->description('Asset tag of the asset the maintenance is for. One of asset_id or asset_tag is required.'),
            'title' => $schema->string()->description('Maintenance title/name (required)'),
            'maintenance_type_id' => $schema->number()->description('maintenance_types.id row identifying the type of work (required). Use list-style tools to discover available types. Alternative: maintenance_type_name.'),
            'maintenance_type_name' => $schema->string()->description('Maintenance type name. Resolved to an ID server-side.'),
            'supplier_id' => $schema->number()->description('Supplier who performed or will perform the work. Alternative: supplier_name.'),
            'supplier_name' => $schema->string()->description('Supplier name. Resolved to an ID server-side.'),
            'is_warranty' => $schema->boolean()->description('Whether this is covered by warranty (defaults to false)'),
            'cost' => $schema->number()->description('Cost in the installs currency'),
            'start_date' => $schema->string()->description('Work start date (YYYY-MM-DD, defaults to today)'),
            'expected_completion_date' => $schema->string()->description('Planned completion date (YYYY-MM-DD). Use when the maintenance is still open.'),
            'completion_date' => $schema->string()->description('Alias for expected_completion_date for backwards compat'),
            'completed_at' => $schema->string()->description('Actual completion timestamp (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS). Set this to mark the maintenance done.'),
            'notes' => $schema->string()->description('Free-form notes'),
            'responsible_party_id' => $schema->number()->description('Technician user ID responsible for the work'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the maintenance was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new maintenance record'),
            'name' => $schema->string()->description('Name/title of the maintenance'),
            'item_type' => $schema->string()->description('Basename of the polymorphic parent model (usually Asset)'),
            'item_id' => $schema->number()->description('Numeric ID of the polymorphic parent'),
            'asset_tag' => $schema->string()->description('Asset tag of the parent asset'),
        ];
    }
}
