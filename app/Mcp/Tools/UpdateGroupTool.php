<?php

namespace App\Mcp\Tools;

use App\Models\Group;
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

#[Name('update_group')]
#[Title('Update Group')]
#[Description('Update an existing Snipe-IT permission group by ID or name')]
class UpdateGroupTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('superadmin')) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        try {
            $request->validate([
                'id' => 'nullable|integer',
                'name' => 'nullable|string|max:255',
                'new_name' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        if ($request->filled('id')) {
            $group = Group::find($request->get('id'));
        } elseif ($request->filled('name')) {
            $group = Group::where('name', $request->get('name'))->first();
        } else {
            return Response::make(Response::error(trans('mcp.id_or_name_required')));
        }

        if (! $group) {
            return Response::make(Response::error(trans('mcp.group_not_found')));
        }

        if ($request->filled('new_name')) {
            $group->name = $request->get('new_name');
        }

        if ($request->filled('notes')) {
            $group->notes = $request->get('notes');
        }

        if ($group->save()) {
            return Response::make(
                Response::text(trans('mcp.group_updated', ['name' => $group->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.group_updated', ['name' => $group->name]),
                'id' => $group->id,
                'name' => $group->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $group->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric group ID to update'),
            'name' => $schema->string()->description('Group name to look up for updating'),
            'new_name' => $schema->string()->description('New name to rename the group to'),
            'notes' => $schema->string()->description('Updated notes for the group'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the updated group'),
            'name' => $schema->string()->description('Name of the updated group'),
        ];
    }
}
