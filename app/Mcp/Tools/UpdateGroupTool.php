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
#[Description('Update an existing Snipe-IT permission group by ID or name. Requires superadmin. Supplying permissions replaces the group\'s entire permission set.')]
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
                'permissions' => 'nullable|string',
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

        if ($request->filled('permissions')) {
            $result = $this->parseAndValidatePermissions($request->get('permissions'));
            if (is_string($result)) {
                return Response::make(Response::error($result));
            }
            $group->permissions = json_encode($result);
        }

        if ($group->save()) {
            return Response::make(
                Response::text(trans('mcp.group_updated', ['name' => $group->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.group_updated', ['name' => $group->name]),
                'id' => $group->id,
                'name' => $group->name,
                'permissions' => $group->decodePermissions(),
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $group->getErrors()->first()])));
    }

    /**
     * Parse a JSON permissions string and validate all keys against config('permissions').
     * Returns the decoded array on success, or an error string on failure.
     */
    private function parseAndValidatePermissions(string $raw): array|string
    {
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return trans('mcp.invalid_permissions_format');
        }

        $validKeys = collect(config('permissions'))
            ->flatMap(fn ($perms) => collect($perms)->pluck('permission'))
            ->unique()
            ->flip()
            ->all();

        foreach (array_keys($decoded) as $key) {
            if (! isset($validKeys[$key])) {
                return trans('mcp.invalid_permission_key', ['key' => $key]);
            }
            if (! in_array((int) $decoded[$key], [1, -1], true)) {
                return trans('mcp.invalid_permission_value', ['key' => $key]);
            }
        }

        return array_map('intval', $decoded);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric group ID to update'),
            'name' => $schema->string()->description('Group name to look up for updating'),
            'new_name' => $schema->string()->description('New name to rename the group to'),
            'permissions' => $schema->string()->description(
                'JSON object replacing the group\'s entire permission set. '.
                'Map permission keys to 1 (grant) or -1 (deny). '.
                'Example: {"assets.view":1,"assets.create":1,"assets.edit":-1}. '.
                'Omit to leave existing permissions unchanged.'
            ),
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
            'permissions' => $schema->object()->description('Permissions now set on the group'),
        ];
    }
}
