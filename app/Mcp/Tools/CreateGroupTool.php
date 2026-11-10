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

#[Name('create_group')]
#[Title('Create Group')]
#[Description('Create a new Snipe-IT permission group')]
class CreateGroupTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('superadmin')) {
            return Response::make(Response::error('Unauthorized'));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'notes' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $group = new Group;
        $group->name = $request->get('name');
        if ($request->filled('notes')) {
            $group->notes = $request->get('notes');
        }
        $group->created_by = auth()->id();

        if ($group->save()) {
            return Response::make(
                Response::text('Group '.$group->name.' created successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Group created successfully',
                'id' => $group->id,
                'name' => $group->name,
            ]);
        }

        return Response::make(Response::error('Create failed: '.$group->getErrors()->first()));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Group name (required, must be unique)'),
            'notes' => $schema->string()->description('Notes about the group'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the group was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new group'),
            'name' => $schema->string()->description('Name of the new group'),
        ];
    }
}
