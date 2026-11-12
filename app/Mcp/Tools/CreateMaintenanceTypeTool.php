<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Asset;
use App\Models\MaintenanceType;
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

#[Name('create_maintenance_type')]
#[Title('Create Maintenance Type')]
#[Description('Add a new maintenance type to the taxonomy. Only do this after list_maintenance_types confirms the name does not already exist in some variant. Taxonomy sprawl ("Repair" vs "repair" vs "Repairs") makes future reporting painful.')]
class CreateMaintenanceTypeTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        // No maintenances.create permission exists in Snipe-IT. Maintenance
        // types are created in the context of asset maintenance work, so
        // this gates on assets.edit (the same permission MaintenancePolicy
        // uses when there is no asset instance to check against).
        if (! Gate::allows('update', Asset::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $type = new MaintenanceType;
        try {
            $validated = Validator::make($request->all(), $type->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $type->fill(array_merge($request->all(), $validated));
        $type->created_by = auth()->id();

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
