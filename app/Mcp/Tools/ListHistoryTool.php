<?php

namespace App\Mcp\Tools;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\Location;
use App\Models\Maintenance;
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

#[Name('list_history')]
#[Title('List History')]
#[Description('List the activity history for a Snipe-IT object. Supported types: accessory, asset, asset_model, component, consumable, license, location, maintenance, user')]
class ListHistoryTool extends Tool
{
    private const TYPE_MAP = [
        'accessory' => Accessory::class,
        'asset' => Asset::class,
        'asset_model' => AssetModel::class,
        'component' => Component::class,
        'consumable' => Consumable::class,
        'license' => License::class,
        'location' => Location::class,
        'maintenance' => Maintenance::class,
        'user' => User::class,
    ];

    public function handle(Request $request): ResponseFactory
    {
        $validTypes = implode(',', array_keys(self::TYPE_MAP));

        $request->validate([
            'object_type' => 'required|string|in:'.$validTypes,
            'id' => 'required|integer|min:1',
            'search' => 'nullable|string|max:255',
            'action_type' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $objectType = $request->get('object_type');
        $modelClass = self::TYPE_MAP[$objectType];

        $object = $modelClass::withTrashed()->find($request->get('id'));

        if (! $object) {
            return Response::make(Response::error(trans('mcp.object_not_found', ['type' => $objectType])));
        }

        if (! Gate::allows('history', $object)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $modelClass = get_class($object);
        $modelId = $object->getKey();

        // Wrap the item/target OR in a subquery so additional filters apply to both sides.
        $history = Actionlog::where(function ($q) use ($modelClass, $modelId) {
            $q->where('item_type', $modelClass)
                ->where('item_id', $modelId)
                ->orWhere(function ($q2) use ($modelClass, $modelId) {
                    $q2->where('target_type', $modelClass)
                        ->where('target_id', $modelId);
                });
        });

        if ($request->filled('search')) {
            $history->TextSearch(e($request->get('search')));
        }

        if ($request->filled('action_type')) {
            $history->where('action_type', $request->get('action_type'));
        }

        $history->orderBy('action_logs.created_at', 'desc');

        $total = (clone $history)->count();
        $records = $history->skip($offset)->take($limit)->forApiHistory()->get();

        $entries = $records->map(fn ($log) => [
            'id' => $log->id,
            'action_type' => $log->action_type,
            'created_at' => $log->created_at?->toISOString(),
            'note' => $log->note,
            'created_by' => $log->adminuser ? [
                'id' => $log->adminuser->id,
                'username' => $log->adminuser->username,
            ] : null,
            'target' => $log->target ? [
                'id' => $log->target->getKey(),
                'type' => class_basename($log->target_type),
                'name' => $log->target->present()->name() ?? null,
            ] : null,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_history', [
                'total' => $total,
                'count' => count($entries),
                'type' => $objectType,
            ]))
        )->withStructuredContent([
            'object_type' => $objectType,
            'object_id' => $object->id,
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'history' => $entries,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'object_type' => $schema->string()->description('Type of object: accessory, asset, asset_model, component, consumable, license, location, maintenance, user'),
            'id' => $schema->number()->description('Numeric ID of the object'),
            'search' => $schema->string()->description('Filter history by keyword'),
            'action_type' => $schema->string()->description('Filter by action type (e.g. checkout, checkin, update, note added, uploaded)'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'object_type' => $schema->string()->description('Type of object queried')->required(),
            'object_id' => $schema->number()->description('ID of the object queried')->required(),
            'total' => $schema->number()->description('Total number of history entries')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'history' => $schema->array()->description('List of history entries'),
        ];
    }
}
