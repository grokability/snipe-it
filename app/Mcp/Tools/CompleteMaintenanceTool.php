<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\SnipeMcpTool;
use App\Models\Maintenance;
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

#[Name('complete_maintenance')]
#[Title('Complete Maintenance')]
#[Description('Mark a maintenance completed. Defaults completed_at to now, stamps completed_by.')]
class CompleteMaintenanceTool extends Tool
{
    use SnipeMcpTool;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        $request->validate([
            'id' => 'required|integer',
            'completed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $maintenance = Maintenance::find($request->get('id'));

        if (! $maintenance) {
            return Response::make(Response::error(trans('admin/maintenances/message.not_found')));
        }

        if (! Gate::allows('update', $maintenance)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if ($maintenance->completed_at !== null) {
            return Response::make(Response::error(trans('mcp.maintenance_already_completed', [
                'name' => $maintenance->name,
                'completed_at' => $maintenance->completed_at->toDateTimeString(),
            ])));
        }

        $completedAt = $request->filled('completed_at')
            ? $request->get('completed_at')
            : now()->toDateTimeString();

        $input = [
            'completed_at' => $completedAt,
        ];
        if ($request->filled('notes')) {
            $input['notes'] = $request->get('notes');
        }

        $rules = array_intersect_key($maintenance->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $maintenance->fill(array_merge($input, $validated));
        $maintenance->completed_by = auth()->id();

        if ($maintenance->save()) {
            $maintenance->refresh();

            return $this->structuredResponse(
                trans('admin/maintenances/message.complete.success'),
                [
                    'success' => true,
                    'message' => trans('admin/maintenances/message.complete.success'),
                    'id' => $maintenance->id,
                    'name' => $maintenance->name,
                    'completed_at' => $maintenance->completed_at->toDateTimeString(),
                    'completed_by_id' => $maintenance->completed_by,
                    'asset_tag' => $maintenance->asset?->asset_tag,
                ]
            );
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $maintenance->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the maintenance record')->required(),
            'completed_at' => $schema->string()->description('Completion timestamp (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS). Defaults to now.'),
            'notes' => $schema->string()->description('Closing notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the completion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the maintenance'),
            'name' => $schema->string()->description('Name/title of the maintenance'),
            'completed_at' => $schema->string()->description('Completion timestamp just recorded'),
            'completed_by_id' => $schema->number()->description('ID of the user who marked it complete'),
            'asset_tag' => $schema->string()->description('Asset tag of the parent asset')->nullable(),
        ];
    }
}
