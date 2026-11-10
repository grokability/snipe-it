<?php

namespace App\Mcp\Tools;

use App\Models\Statuslabel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_status_labels')]
#[Title('List Status Labels')]
#[Description('Search and list Snipe-IT status labels with optional filtering and pagination')]
class ListStatusLabelsTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('view', Statuslabel::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'status_type' => 'nullable|string|in:deployable,pending,archived,undeployable',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $labels = Statuslabel::withCount('assets as assets_count');

        if ($request->filled('search')) {
            $labels->TextSearch($request->get('search'));
        }

        if ($request->filled('status_type')) {
            $type = $request->get('status_type');
            if ($type === 'deployable') {
                $labels->Deployable();
            } elseif ($type === 'pending') {
                $labels->Pending();
            } elseif ($type === 'archived') {
                $labels->Archived();
            } elseif ($type === 'undeployable') {
                $labels->Undeployable();
            }
        }

        $total = $labels->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $labels->skip($offset)->take($limit)->get();

        $labelsData = $results->map(fn (Statuslabel $label) => [
            'id' => $label->id,
            'name' => $label->name,
            'type' => $label->getStatuslabelType(),
            'color' => $label->color,
            'assets_count' => $label->assets_count,
            'deployable' => $label->deployable,
            'pending' => $label->pending,
            'archived' => $label->archived,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_status_labels', ['total' => $total, 'count' => count($labelsData)]))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'status_labels' => $labelsData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword to search across status label name and notes'),
            'status_type' => $schema->string()->description('Filter by type: deployable, pending, archived, undeployable'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching status labels')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'status_labels' => $schema->array()->description('List of status labels')->required(),
        ];
    }
}
