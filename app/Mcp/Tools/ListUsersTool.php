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

#[Name('list_users')]
#[Title('List Users')]
#[Description('Search and list Snipe-IT users with optional filtering by keyword, company, department, and location')]
class ListUsersTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'company_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'activated' => 'nullable|boolean',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $users = User::with('company', 'department', 'userloc', 'manager')
            ->withCount(['assets as assets_count', 'licenses as licenses_count']);

        if ($request->filled('search')) {
            $users->TextSearch($request->get('search'));
        }

        if ($request->filled('company_id')) {
            $users->where('users.company_id', '=', $request->get('company_id'));
        }

        if ($request->filled('department_id')) {
            $users->where('users.department_id', '=', $request->get('department_id'));
        }

        if ($request->filled('location_id')) {
            $users->where('users.location_id', '=', $request->get('location_id'));
        }

        if ($request->has('activated')) {
            $users->where('users.activated', '=', $request->get('activated'));
        }

        $users->orderBy('users.last_name', 'asc')->orderBy('users.first_name', 'asc');

        $total = $users->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $users->skip($offset)->take($limit)->get();

        $usersData = $results->map(fn (User $user) => [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'email' => $user->email,
            'jobtitle' => $user->jobtitle,
            'company' => $user->company?->name,
            'department' => $user->department?->name,
            'location' => $user->userloc?->name,
            'manager' => $user->manager ? trim($user->manager->first_name.' '.$user->manager->last_name) : null,
            'activated' => (bool) $user->activated,
            'assets_count' => $user->assets_count,
            'licenses_count' => $user->licenses_count,
        ])->values()->all();

        return Response::make(
            Response::text("Found {$total} users, returning ".count($usersData))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'users' => $usersData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword to search across name, username, email, and employee number'),
            'company_id' => $schema->number()->description('Filter by company ID'),
            'department_id' => $schema->number()->description('Filter by department ID'),
            'location_id' => $schema->number()->description('Filter by location ID'),
            'activated' => $schema->boolean()->description('Filter by account activated status'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching users')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
        ];
    }
}
