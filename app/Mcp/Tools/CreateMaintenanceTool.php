<?php

namespace App\Mcp\Tools;

use App\Models\Asset;
use App\Models\Maintenance;
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

#[Name('create_maintenance')]
#[Title('Create Maintenance')]
#[Description('Create a new maintenance record attached to an asset')]
class CreateMaintenanceTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('update', Asset::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        try {
            $request->validate([
                'asset_id' => 'required_without:asset_tag|nullable|integer|exists:assets,id',
                'asset_tag' => 'required_without:asset_id|nullable|string|max:191',
                'title' => 'required|string|max:100',
                'maintenance_type_id' => 'required|integer|exists:maintenance_types,id',
                'supplier_id' => 'nullable|integer|exists:suppliers,id',
                'is_warranty' => 'nullable|boolean',
                'cost' => 'nullable|numeric|min:0',
                'start_date' => 'nullable|date',
                'expected_completion_date' => 'nullable|date',
                'completion_date' => 'nullable|date',
                'completed_at' => 'nullable|date',
                'notes' => 'nullable|string',
                'responsible_party_id' => 'nullable|integer|exists:users,id',
            ]);
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

        // Write to the polymorphic native columns directly instead of
        // going through the legacy asset_id mutator. Snipe-IT's
        // maintenances table went polymorphic so maintenances can attach
        // to any checkoutable item type. Pinning item_type to Asset here
        // matches the tool's current surface, which is asset-scoped.
        $maintenance = new Maintenance;
        $maintenance->item_id = $asset->id;
        $maintenance->item_type = Asset::class;
        $maintenance->name = $request->get('title');
        $maintenance->maintenance_type_id = $request->get('maintenance_type_id');
        $maintenance->start_date = $request->filled('start_date') ? $request->get('start_date') : now()->format('Y-m-d');
        $maintenance->is_warranty = $request->boolean('is_warranty');
        $maintenance->created_by = auth()->id();

        if ($request->filled('supplier_id')) {
            $maintenance->supplier_id = $request->get('supplier_id');
        }
        if ($request->filled('cost')) {
            $maintenance->cost = $request->get('cost');
        }
        if ($request->filled('notes')) {
            $maintenance->notes = $request->get('notes');
        }

        // Planned completion date. expected_completion_date is the native
        // column name. completion_date is accepted as an alias for
        // compatibility with callers that still use the old name.
        $expected = $request->filled('expected_completion_date')
            ? $request->get('expected_completion_date')
            : $request->get('completion_date');
        if ($expected !== null) {
            $maintenance->expected_completion_date = $expected;
        }

        // Actual completion marks the maintenance as done. Separate from
        // expected_completion_date, which is just the plan.
        if ($request->filled('completed_at')) {
            $maintenance->completed_at = $request->get('completed_at');
            $maintenance->completed_by = auth()->id();
        }

        if ($request->filled('responsible_party_id')) {
            $maintenance->responsible_party_id = $request->get('responsible_party_id');
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
            'maintenance_type_id' => $schema->number()->description('maintenance_types.id row identifying the type of work (required). Use list-style tools to discover available types.'),
            'supplier_id' => $schema->number()->description('Supplier who performed or will perform the work'),
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
