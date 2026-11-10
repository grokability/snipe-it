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

#[Name('reset_2fa')]
#[Title('Reset Two-Factor Authentication')]
#[Description('Reset two-factor authentication for a Snipe-IT user')]
class Reset2FATool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('update', User::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $request->validate([
            'id' => 'required|integer',
        ]);

        $user = User::find($request->get('id'));

        if (! $user) {
            return Response::make(Response::error('User not found'));
        }

        $user->two_factor_secret = null;
        $user->two_factor_enrolled = 0;
        $user->two_factor_optin = 0;
        $user->save();

        return Response::make(
            Response::text('Two-factor authentication reset for '.$user->username)
        )->withStructuredContent([
            'success' => true,
            'message' => 'Two-factor authentication reset successfully',
            'id' => $user->id,
            'username' => $user->username,
        ]);
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
