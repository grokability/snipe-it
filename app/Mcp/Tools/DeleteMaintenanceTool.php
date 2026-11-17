<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\SnipeMcpTool;
use App\Models\Maintenance;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_maintenance')]
#[Title('Delete Maintenance')]
#[Description('Soft-delete a maintenance record by numeric ID')]
class DeleteMaintenanceTool extends Tool
{
    use SnipeMcpTool;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        $request->validate([
            'id' => 'required|integer',
        ]);

        $maintenance = Maintenance::find($request->get('id'));

        if (! $maintenance) {
            return Response::make(Response::error(trans('admin/maintenances/message.not_found')));
        }

        if (! $maintenance->isDeletable()) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $maintenanceName = $maintenance->name;
        $maintenanceId = $maintenance->id;

        if ($maintenance->delete()) {
            return $this->structuredResponse(
                trans('admin/maintenances/message.delete.success'),
                [
                    'success' => true,
                    'message' => trans('admin/maintenances/message.delete.success'),
                    'id' => $maintenanceId,
                    'name' => $maintenanceName,
                ]
            );
        }

        return Response::make(Response::error(trans('mcp.delete_failed')));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the maintenance record to delete (required)')->required(),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the deleted maintenance'),
            'name' => $schema->string()->description('Name/title of the deleted maintenance'),
        ];
    }
}
