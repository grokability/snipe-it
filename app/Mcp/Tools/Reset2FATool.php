<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\SnipeMcpTool;
use App\Models\Actionlog;
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

#[Name('reset_2fa')]
#[Title('Reset Two-Factor Authentication')]
#[Description('Reset two-factor authentication for a Snipe-IT user')]
class Reset2FATool extends Tool
{
    use SnipeMcpTool;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        $request->validate([
            'id' => 'required|integer',
        ]);

        $user = User::find($request->get('id'));

        // Single error path for every denial case (not found, policy,
        // canEditAuthFields, twoFactorResettable, demo mode) so the
        // response cannot be used to probe which user IDs exist.
        if (! $user
            || ! Gate::allows('update', $user)
            || ! $user->twoFactorResettable()
            || ! Gate::allows('canEditAuthFields', $user)
            || ! Gate::allows('editableOnDemo')
        ) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.user')])));
        }

        $user->two_factor_secret = null;
        $user->two_factor_enrolled = 0;
        $user->two_factor_optin = 0;
        $user->saveQuietly();

        $log = new Actionlog;
        $log->target_type = User::class;
        $log->target_id = $user->id;
        $log->item_type = User::class;
        $log->item_id = $user->id;
        $log->created_at = now();
        $log->created_by = auth()->id();
        $log->logaction('2FA reset');

        return $this->structuredResponse(
            trans('mcp.two_factor_reset', ['username' => $user->username]),
            [
                'success' => true,
                'message' => trans('mcp.two_factor_reset', ['username' => $user->username]),
                'id' => $user->id,
                'username' => $user->username,
            ]
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the user whose 2FA should be reset (required)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the reset succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the user'),
            'username' => $schema->string()->description('Username of the user'),
        ];
    }
}
