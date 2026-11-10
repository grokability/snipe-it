<?php

namespace App\Mcp\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_current_user')]
#[Title('Get Current User')]
#[Description('Return information about the currently authenticated Snipe-IT user')]
class GetCurrentUserTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! auth()->check()) {
            return Response::make(Response::error('Not authenticated'));
        }

        $user = User::with('company', 'department', 'userloc')->find(auth()->id());

        if (! $user) {
            return Response::make(Response::error('Not authenticated'));
        }

        return Response::make(
            Response::text('Current user: '.$user->username)
        )->withStructuredContent([
            'id' => $user->id,
            'username' => $user->username,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'company' => $user->company?->name,
            'department' => $user->department?->name,
            'location' => $user->userloc?->name,
            'employee_num' => $user->employee_num,
            'title' => $user->jobtitle,
            'phone' => $user->phone,
            'activated' => (bool) $user->activated,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric user ID')->required(),
            'username' => $schema->string()->description('Username')->required(),
            'first_name' => $schema->string()->description('First name'),
            'last_name' => $schema->string()->description('Last name'),
            'email' => $schema->string()->description('Email address'),
            'company' => $schema->string()->description('Company name'),
            'department' => $schema->string()->description('Department name'),
            'location' => $schema->string()->description('Default location name'),
            'employee_num' => $schema->string()->description('Employee number'),
            'title' => $schema->string()->description('Job title'),
            'phone' => $schema->string()->description('Phone number'),
            'activated' => $schema->boolean()->description('Whether the account is activated'),
        ];
    }
}
