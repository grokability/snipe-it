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
#[Description('Create a new Snipe-IT permission group. Requires superadmin. Permissions are a JSON object mapping permission keys to 1 (grant) or -1 (deny).')]
class CreateGroupTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('superadmin')) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'permissions' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $permissions = null;
        if ($request->filled('permissions')) {
            $result = $this->parseAndValidatePermissions($request->get('permissions'));
            if (is_string($result)) {
                return Response::make(Response::error($result));
            }
            $permissions = $result;
        }

        $group = new Group;
        $group->name = $request->get('name');
        if ($permissions !== null) {
            $group->permissions = json_encode($permissions);
        }
        if ($request->filled('notes')) {
            $group->notes = $request->get('notes');
        }
        $group->created_by = auth()->id();

        if ($group->save()) {
            return Response::make(
                Response::text(trans('mcp.group_created', ['name' => $group->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.group_created', ['name' => $group->name]),
                'id' => $group->id,
                'name' => $group->name,
                'permissions' => $group->decodePermissions(),
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $group->getErrors()->first()])));
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
            'name' => $schema->string()->description('Group name (required, must be unique)'),
            'permissions' => $schema->string()->description(
                'JSON object mapping permission keys to 1 (grant) or -1 (deny). '.
                'Valid keys include: superuser, admin, import, reports.view, '.
                'assets.view, assets.create, assets.edit, assets.delete, assets.checkout, assets.checkin, assets.audit, '.
                'users.view, users.create, users.edit, users.delete, '.
                'licenses.view, licenses.create, licenses.edit, licenses.delete, licenses.checkout, licenses.checkin, '.
                'accessories.view, accessories.create, accessories.edit, accessories.delete, accessories.checkout, accessories.checkin, '.
                'components.view, components.create, components.edit, components.delete, components.checkout, components.checkin, '.
                'consumables.view, consumables.create, consumables.edit, consumables.delete, consumables.checkout, '.
                'and many more. Example: {"assets.view":1,"assets.create":1,"assets.edit":-1}'
            ),
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
            'permissions' => $schema->object()->description('Permissions set on the group'),
        ];
    }
}
