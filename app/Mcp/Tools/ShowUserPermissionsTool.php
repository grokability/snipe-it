<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('show_user_permissions')]
#[Title('Show User Permissions')]
#[Description('Return the effective permission set for a user, merging their individual grants/denials with inherited group permissions. Omit all identifiers to query the currently authenticated user.')]
class ShowUserPermissionsTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Read)) {
            return $denied;
        }

        $request->validate([
            'id' => 'nullable|integer',
            'username' => 'nullable|string|max:191',
            'email' => 'nullable|string|max:191',
        ]);

        $with = ['groups'];

        if ($request->filled('id')) {
            $user = User::with($with)->find($request->get('id'));
        } elseif ($request->filled('username')) {
            $user = User::with($with)->where('username', $request->get('username'))->first();
        } elseif ($request->filled('email')) {
            $user = User::with($with)->where('email', $request->get('email'))->first();
        } else {
            $user = auth()->check() ? User::with($with)->find(auth()->id()) : null;
        }

        // Self-queries are always allowed. Querying another user's
        // permissions requires the standard user-view gate. Collapse
        // missing-user and gate-refused into the same response so an
        // unauthorized caller can't probe user IDs by diffing errors.
        $isSelf = $user && $user->id === auth()->id();
        if (! $user || (! $isSelf && ! Gate::allows('view', $user))) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.user')])));
        }

        $effectiveBySection = $user->getEffectivePermissionsBySection();

        // Flatten "allowed" sources to a simple string list so AI clients
        // do not have to inspect every section row to answer "what can
        // this user do." Denials stay visible in the sectioned output.
        $allowedKeys = [];
        foreach ($effectiveBySection as $rows) {
            foreach ($rows as $row) {
                if ($row['status'] === 'allowed') {
                    $allowedKeys[] = $row['permission'];
                }
            }
        }

        return Response::make(
            Response::text(trans('mcp.user_permissions_found', ['username' => $user->username, 'count' => count($allowedKeys)]))
        )->withStructuredContent([
            'id' => $user->id,
            'username' => $user->username,
            'is_superuser' => $user->isSuperUser(),
            'groups' => $user->groups->map(fn ($group) => [
                'id' => $group->id,
                'name' => $group->name,
            ])->values()->all(),
            'allowed_permissions' => array_values(array_unique($allowedKeys)),
            'permissions_by_section' => $effectiveBySection,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric user ID. Omit all identifiers to use the currently authenticated user.'),
            'username' => $schema->string()->description('Username'),
            'email' => $schema->string()->description('Email address'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric user ID')->required(),
            'username' => $schema->string()->description('Username')->required(),
            'is_superuser' => $schema->boolean()->description('True when the user has the superuser permission. Superusers implicitly pass every authorization check.')->required(),
            'groups' => $schema->array()->description('Permission groups the user belongs to (id + name).'),
            'allowed_permissions' => $schema->array()->description('Flat list of permission keys (e.g. "assets.view", "licenses.checkout") the user can perform, after merging individual + group grants and denials.'),
            'permissions_by_section' => $schema->object()->description('Permissions grouped by section. Each row carries status (allowed/denied), source (individual/group/superuser), and the group names that contributed the grant or denial.'),
        ];
    }
}
