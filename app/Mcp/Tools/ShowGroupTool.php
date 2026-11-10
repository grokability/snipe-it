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

#[Name('show_group')]
#[Title('Show Group')]
#[Description('Look up a single Snipe-IT permission group by numeric ID or name')]
class ShowGroupTool extends Tool
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
            $group = Group::withCount('users as users_count')->find($request->get('id'));
        } elseif ($request->filled('name')) {
            $group = Group::withCount('users as users_count')
                ->where('name', $request->get('name'))
                ->first();
        } else {
            return Response::make(Response::error('Please provide an id or name'));
        }

        if (! $group) {
            return Response::make(Response::error('Group not found'));
        }

        return Response::make(
            Response::text('Group '.$group->name.' found')
        )->withStructuredContent([
            'id' => $group->id,
            'name' => $group->name,
            'notes' => $group->notes,
            'permissions' => $group->decodePermissions(),
            'users_count' => $group->users_count,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric group ID'),
            'name' => $schema->string()->description('Group name to look up'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric group ID')->required(),
            'name' => $schema->string()->description('Group name')->required(),
            'notes' => $schema->string()->description('Notes about the group'),
            'permissions' => $schema->object()->description('Decoded permissions array'),
            'users_count' => $schema->number()->description('Number of users in this group'),
        ];
    }
}
