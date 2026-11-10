<?php

namespace App\Mcp\Tools;

use App\Models\Actionlog;
use App\Models\Asset;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_asset_notes')]
#[Title('List Asset Notes')]
#[Description('List manual notes added to a Snipe-IT asset, identified by asset tag, serial number, or numeric ID')]
class ListAssetNotesTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'asset_tag' => 'nullable|string|max:100',
            'serial' => 'nullable|string|max:255',
            'id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $asset = $this->resolveAsset($request);

        if (! $asset) {
            return Response::make(Response::error(trans('mcp.asset_not_found')));
        }

        if (! Gate::allows('view', $asset)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $query = Actionlog::with('adminuser:id,username')
            ->where('item_type', Asset::class)
            ->where('item_id', $asset->id)
            ->where('action_type', 'note added')
            ->orderBy('created_at', 'desc');

        $total = (clone $query)->count();
        $records = $query->skip($offset)->take($limit)
            ->get(['id', 'created_at', 'note', 'created_by', 'item_id', 'action_type']);

        $notes = $records->map(fn ($n) => [
            'id' => $n->id,
            'created_at' => $n->created_at?->toISOString(),
            'note' => $n->note,
            'created_by_id' => $n->created_by,
            'created_by_username' => $n->adminuser?->username,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_asset_notes', [
                'asset_tag' => $asset->asset_tag,
                'total' => $total,
                'count' => count($notes),
            ]))
        )->withStructuredContent([
            'asset_id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'notes' => $notes,
        ]);
    }

    private function resolveAsset(Request $request): ?Asset
    {
        if ($request->filled('asset_tag')) {
            return Asset::where('asset_tag', $request->get('asset_tag'))->first();
        }
        if ($request->filled('serial')) {
            return Asset::where('serial', $request->get('serial'))->first();
        }
        if ($request->filled('id')) {
            return Asset::find($request->get('id'));
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'asset_tag' => $schema->string()->description('Asset tag of the asset'),
            'serial' => $schema->string()->description('Serial number of the asset'),
            'id' => $schema->number()->description('Numeric ID of the asset'),
            'limit' => $schema->number()->description('Number of notes to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of notes to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'asset_id' => $schema->number()->description('Numeric ID of the asset')->required(),
            'asset_tag' => $schema->string()->description('Asset tag of the asset')->required(),
            'total' => $schema->number()->description('Total number of notes on this asset')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'notes' => $schema->array()->description('List of notes'),
        ];
    }
}
