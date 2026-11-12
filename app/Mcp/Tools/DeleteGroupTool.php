<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Group;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_group')]
#[Title('Delete Group')]
#[Description('Delete a Snipe-IT permission group by ID or name. The group must have no users assigned.')]
class DeleteGroupTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Superadmin)) {
            return $denied;
        }

        if (! Gate::allows('superadmin')) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        if ($request->filled('id')) {
            $group = Group::find($request->get('id'));
        } elseif ($request->filled('name')) {
            $group = Group::where('name', $request->get('name'))->first();
        } else {
            return Response::make(Response::error(trans('mcp.id_or_name_required')));
        }

        if (! $group) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.group')])));
        }

        if (! $group->isDeletable()) {
            return Response::make(Response::error(trans('mcp.cannot_delete_has_dependents', [
                'model' => trans('general.group'),
                'name' => $group->name,
            ])));
        }

        $groupName = $group->name;

        if ($group->delete()) {
            return Response::make(
                Response::text(trans('mcp.group_deleted', ['name' => $groupName]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.group_deleted', ['name' => $groupName]),
                'name' => $groupName,
            ]);
        }

        return Response::make(Response::error(trans('mcp.delete_failed')));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric group ID to delete'),
            'name' => $schema->string()->description('Group name to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted group'),
        ];
    }
}
