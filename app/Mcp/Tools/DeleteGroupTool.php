<?php

namespace App\Mcp\Tools;

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
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('superadmin')) {
            return Response::make(Response::error('Unauthorized'));
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
            return Response::make(Response::error('Please provide an id or name'));
        }

        if (! $group) {
            return Response::make(Response::error('Group not found'));
        }

        $groupName = $group->name;

        if ($group->delete()) {
            return Response::make(
                Response::text('Group '.$groupName.' deleted successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Group deleted successfully',
                'name' => $groupName,
            ]);
        }

        return Response::make(Response::error('Delete failed'));
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
