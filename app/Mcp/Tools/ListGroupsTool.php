<?php

namespace App\Mcp\Tools;

use App\Models\Group;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_groups')]
#[Title('List Groups')]
#[Description('List Snipe-IT permission groups with optional search and pagination')]
class ListGroupsTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('superadmin')) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $groups = Group::withCount('users as users_count')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $groups->TextSearch($request->get('search'));
        }

        $total = $groups->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $groups->skip($offset)->take($limit)->get();

        $groupsData = $results->map(fn (Group $group) => [
            'id' => $group->id,
            'name' => $group->name,
            'notes' => $group->notes,
            'users_count' => $group->users_count,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_groups', ['total' => $total, 'count' => count($groupsData)]))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'groups' => $groupsData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword to search groups by name or notes'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching groups')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'groups' => $schema->array()->description('List of groups')->required(),
        ];
    }
}
