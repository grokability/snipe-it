<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\SnipeMcpTool;
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

#[Name('update_maintenance_type')]
#[Title('Update Maintenance Type')]
#[Description('Rename or re-color a maintenance type by numeric ID or current name')]
class UpdateMaintenanceTypeTool extends Tool
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
            'new_name' => 'nullable|string|max:100',
            'tag_color' => 'nullable|string|max:7',
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

        $input = [];
        if ($request->filled('new_name')) {
            $input['name'] = $request->get('new_name');
        }
        if ($request->has('tag_color')) {
            $input['tag_color'] = $request->get('tag_color');
        }

        if (empty($input)) {
            return Response::make(Response::error(trans('mcp.update_no_fields')));
        }

        $rules = array_intersect_key($type->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $type->fill(array_merge($input, $validated));

        if ($type->save()) {
            return $this->structuredResponse(
                trans('admin/maintenance_types/message.update.success'),
                [
                    'success' => true,
                    'message' => trans('admin/maintenance_types/message.update.success'),
                    'id' => $type->id,
                    'name' => $type->name,
                    'tag_color' => $type->tag_color,
                ]
            );
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $type->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the maintenance type to update'),
            'name' => $schema->string()->description('Current name of the maintenance type to look up (alternative to id)'),
            'new_name' => $schema->string()->description('New name to rename the maintenance type to'),
            'tag_color' => $schema->string()->description('New hex color for the UI tag (e.g. #ff9900). Pass an empty string to clear.'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the maintenance type'),
            'name' => $schema->string()->description('Name of the maintenance type'),
            'tag_color' => $schema->string()->description('Tag color')->nullable(),
        ];
    }
}
