<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\CalendarEvent;
use App\Models\Component;
use App\Models\Consumable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_activity_log')]
#[Title('Get Activity Log')]
#[Description('Retrieve the Snipe-IT activity log with optional filtering by item type, item ID, user, and action type')]
class GetActivityLogTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Read)) {
            return $denied;
        }

        $request->validate([
            'item_type' => 'nullable|string|max:255',
            'item_id' => 'nullable|integer',
            'user_id' => 'nullable|integer',
            'action_type' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:'.config('app.max_results'),
            'offset' => 'nullable|integer|min:0',
        ]);

        $logs = Actionlog::with('user', 'item')->orderBy('created_at', 'desc');

        // Mirrors Api\DashboardController::activity. Callers with
        // activity.view (reports / admin users) get the full feed.
        // Callers without it get a feed restricted to item_type /
        // target_type they can view at the class level, which
        // matches what they would see on the dashboard activity
        // widget. HasCalendarEvents sources plus the three non-
        // calendar checkoutables cover every polymorphic subject
        // the activity log attaches to.
        if (! Gate::allows('activity.view')) {
            $candidateTypes = array_merge(
                CalendarEvent::sourceModels(),
                [Accessory::class, Consumable::class, Component::class],
            );
            $viewableTypes = array_values(array_filter(
                $candidateTypes,
                fn ($class) => Gate::allows('view', $class),
            ));

            $logs->where(function ($sub) use ($viewableTypes) {
                $sub->whereIn('item_type', $viewableTypes)
                    ->orWhereIn('target_type', $viewableTypes);
            });
        }

        if ($request->filled('item_type')) {
            $logs->where('item_type', $request->get('item_type'));
        }

        if ($request->filled('item_id')) {
            $logs->where('item_id', $request->get('item_id'));
        }

        if ($request->filled('user_id')) {
            $logs->where('user_id', $request->get('user_id'));
        }

        if ($request->filled('action_type')) {
            $logs->where('action_type', $request->get('action_type'));
        }

        $total = $logs->count();
        $limit = $request->filled('limit') ? max(1, min(config('app.max_results'), (int) $request->get('limit'))) : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $logs->skip($offset)->take($limit)->get();

        $activityData = $results->map(fn (Actionlog $log) => [
            'id' => $log->id,
            'action_type' => $log->action_type,
            'item_type' => $log->item_type,
            'item_id' => $log->item_id,
            'user_id' => $log->user_id,
            'user' => $log->user?->username,
            'note' => $log->note,
            'created_at' => $log->created_at?->toDateTimeString(),
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_activity', ['total' => $total, 'count' => count($activityData)]))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'activity' => $activityData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'item_type' => $schema->string()->description('Filter by item type (e.g. App\\Models\\Asset)'),
            'item_id' => $schema->number()->description('Filter by item ID'),
            'user_id' => $schema->number()->description('Filter by user ID'),
            'action_type' => $schema->string()->description('Filter by action type (e.g. checkout, checkin, update)'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: '.config('app.max_results').')'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching log entries')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'activity' => $schema->array()->description('List of activity log entries')->required(),
        ];
    }
}
