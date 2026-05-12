<?php

namespace App\Mcp\Tools;

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

#[Name('delete_user')]
#[Title('Delete User')]
#[Description('Soft-delete a Snipe-IT user. The user must have no assets, licenses, accessories, or consumables assigned.')]
class DeleteUserTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'username' => 'nullable|string|max:191',
            'email' => 'nullable|string|max:191',
        ]);

        $user = $this->resolveUser($request);

        if (! $user) {
            return Response::make(Response::error(trans('mcp.user_not_found')));
        }

        if (! Gate::allows('delete', $user)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if ($user->id === auth()->id()) {
            return Response::make(Response::error(trans('mcp.user_cannot_delete_self')));
        }

        if ($user->allAssignedCount() > 0) {
            return Response::make(Response::error(trans('mcp.user_has_items')));
        }

        $username = $user->username;

        if ($user->delete()) {
            return Response::make(
                Response::text(trans('mcp.user_deleted', ['username' => $username]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.user_deleted', ['username' => $username]),
                'username' => $username,
            ]);
        }

        return Response::make(Response::error(trans('mcp.delete_failed_error', ['error' => $user->getErrors()->first()])));
    }

    private function resolveUser(Request $request): ?User
    {
        if ($request->filled('id')) {
            return User::find($request->get('id'));
        }
        if ($request->filled('username')) {
            return User::where('username', $request->get('username'))->first();
        }
        if ($request->filled('email')) {
            return User::where('email', $request->get('email'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric user ID to delete'),
            'username' => $schema->string()->description('Username of the user to delete'),
            'email' => $schema->string()->description('Email address of the user to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'username' => $schema->string()->description('Username of the deleted user'),
        ];
    }
}
