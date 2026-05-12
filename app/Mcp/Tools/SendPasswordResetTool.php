<?php

namespace App\Mcp\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('send_password_reset')]
#[Title('Send Password Reset Email')]
#[Description('Send a password reset link to a Snipe-IT user identified by numeric ID, username, or email address. The user must be active, have an email address, and not be an LDAP-imported account.')]
class SendPasswordResetTool extends Tool
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

        if (! Gate::allows('view', $user)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if (! $user->activated) {
            return Response::make(Response::error(trans('mcp.password_reset_user_inactive', ['username' => $user->username])));
        }

        if (empty($user->email)) {
            return Response::make(Response::error(trans('mcp.password_reset_no_email', ['username' => $user->username])));
        }

        if ($user->ldap_import) {
            return Response::make(Response::error(trans('mcp.password_reset_ldap_user', ['username' => $user->username])));
        }

        try {
            $result = Password::sendResetLink(['email' => trim($user->email)]);
        } catch (\Exception $e) {
            return Response::make(Response::error(trans('mcp.password_reset_send_failed', ['error' => $e->getMessage()])));
        }

        if ($result === Password::RESET_LINK_SENT) {
            return Response::make(
                Response::text(trans('mcp.password_reset_sent', ['email' => $user->email]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.password_reset_sent', ['email' => $user->email]),
                'username' => $user->username,
                'email' => $user->email,
            ]);
        }

        return Response::make(Response::error(trans('mcp.password_reset_send_failed', ['error' => $result])));
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
            'id' => $schema->number()->description('Numeric ID of the user'),
            'username' => $schema->string()->description('Username of the user'),
            'email' => $schema->string()->description('Email address of the user'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the reset email was sent'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'username' => $schema->string()->description('Username of the user'),
            'email' => $schema->string()->description('Email address the reset link was sent to'),
        ];
    }
}
