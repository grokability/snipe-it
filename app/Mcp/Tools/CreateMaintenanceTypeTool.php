<?php

namespace App\Mcp\Tools;

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

#[Name('create_maintenance_type')]
#[Title('Create Maintenance Type')]
#[Description('Add a new maintenance type to the taxonomy. Only do this after list_maintenance_types confirms the name does not already exist in some variant. Taxonomy sprawl ("Repair" vs "repair" vs "Repairs") makes future reporting painful.')]
class CreateMaintenanceTypeTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        // No maintenances.create permission exists in Snipe-IT. Maintenance
        // types are created in the context of asset maintenance work, so
        // this gates on assets.edit (the same permission MaintenancePolicy
        // uses when there is no asset instance to check against).
        if (! Gate::allows('update', Asset::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'tag_color' => 'nullable|string|max:191',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $type = new MaintenanceType;
        $type->name = $request->get('name');
        $type->created_by = auth()->id();

        if ($request->filled('tag_color')) {
            $type->tag_color = $request->get('tag_color');
        }

        if ($type->save()) {
            return Response::make(
                Response::text(trans('mcp.maintenance_type_created', ['name' => $type->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.maintenance_type_created', ['name' => $type->name]),
                'id' => $type->id,
                'name' => $type->name,
                'tag_color' => $type->tag_color,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $type->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Name of the maintenance type (required, must be unique). Examples: "Repair", "Preventive Maintenance", "Upgrade", "Warranty Service".'),
            'tag_color' => $schema->string()->description('Optional hex color (e.g. #ff9900) used by the UI to tag maintenance rows of this type.'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the maintenance type was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new maintenance type (use this as maintenance_type_id on create_maintenance)'),
            'name' => $schema->string()->description('Name of the new maintenance type'),
            'tag_color' => $schema->string()->description('Tag color of the new maintenance type'),
        ];
    }
}
