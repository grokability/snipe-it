<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\SnipeMcpTool;
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
    use SnipeMcpTool;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Read)) {
            return $denied;
        }

        if (! auth()->check()) {
            return Response::make(Response::error(trans('mcp.not_authenticated')));
        }

        $user = User::with('company', 'department', 'userloc')->find(auth()->id());

        if (! $user) {
            return Response::make(Response::error(trans('mcp.not_authenticated')));
        }

        return $this->structuredResponse(
            trans('mcp.current_user', ['username' => $user->username]),
            [
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
            ]
        );
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
            'first_name' => $schema->string()->description('First name')->nullable(),
            'last_name' => $schema->string()->description('Last name')->nullable(),
            'email' => $schema->string()->description('Email address')->nullable(),
            'company' => $schema->string()->description('Company name')->nullable(),
            'department' => $schema->string()->description('Department name')->nullable(),
            'location' => $schema->string()->description('Default location name')->nullable(),
            'employee_num' => $schema->string()->description('Employee number')->nullable(),
            'title' => $schema->string()->description('Job title')->nullable(),
            'phone' => $schema->string()->description('Phone number')->nullable(),
            'activated' => $schema->boolean()->description('Whether the account is activated'),
        ];
    }
}
