<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\SnipeMcpTool;
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

#[Name('delete_maintenance_type')]
#[Title('Delete Maintenance Type')]
#[Description('Soft-delete a maintenance type by ID or name. The type must have no maintenances assigned.')]
class DeleteMaintenanceTypeTool extends Tool
{
    use SnipeMcpTool;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('update', Asset::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:100',
        ]);

        if ($request->filled('id')) {
            $type = MaintenanceType::find($request->get('id'));
        } elseif ($request->filled('name')) {
            $type = MaintenanceType::where('name', $request->get('name'))->first();
        } else {
            return Response::make(Response::error(trans('mcp.id_or_name_required')));
        }

        if (! $type) {
            return Response::make(Response::error(trans('admin/maintenance_types/message.not_found')));
        }

        if (! $type->isDeletable()) {
            return Response::make(Response::error(trans('general.cannot_be_deleted.assoc_maintenances_no_count', [
                'item_name' => $type->name,
                'item' => 'maintenance type',
            ])));
        }

        $typeName = $type->name;

        if ($type->delete()) {
            return $this->structuredResponse(
                trans('admin/maintenance_types/message.delete.success'),
                [
                    'success' => true,
                    'message' => trans('admin/maintenance_types/message.delete.success'),
                    'name' => $typeName,
                ]
            );
        }

        return Response::make(Response::error(trans('mcp.delete_failed')));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the maintenance type to delete'),
            'name' => $schema->string()->description('Name of the maintenance type to delete (alternative to id)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted maintenance type'),
        ];
    }
}
