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

#[Name('restore_user')]
#[Title('Restore User')]
#[Description('Restore a soft-deleted Snipe-IT user')]
class RestoreUserTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Delete)) {
            return $denied;
        }

        if (! Gate::allows('delete', User::class)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.user')])));
        }

        $request->validate([
            'id' => 'required|integer',
        ]);

        $user = User::withTrashed()->find($request->get('id'));

        if (! $user) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.user')])));
        }

        if (! $user->deleted_at) {
            return Response::make(Response::error(trans('mcp.user_not_deleted')));
        }

        $user->restore();

        return Response::make(
            Response::text(trans('mcp.user_restored', ['username' => $user->username]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.user_restored', ['username' => $user->username]),
            'id' => $user->id,
            'username' => $user->username,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the user to restore (required)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the restore succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the restored user'),
            'username' => $schema->string()->description('Username of the restored user'),
        ];
    }
}
