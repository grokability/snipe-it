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

#[Name('add_asset_note')]
#[Title('Add Asset Note')]
#[Description('Add a manual note to a Snipe-IT asset identified by asset tag, serial number, or numeric ID')]
class AddAssetNoteTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'asset_tag' => 'nullable|string|max:100',
            'serial' => 'nullable|string|max:255',
            'id' => 'nullable|integer',
            'note' => 'required|string|max:50000',
        ]);

        $asset = $this->resolveAsset($request);

        if (! $asset) {
            return Response::make(Response::error(trans('mcp.asset_not_found')));
        }

        if (! Gate::allows('update', $asset)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $logaction = new Actionlog;
        $logaction->item_type = Asset::class;
        $logaction->item_id = $asset->id;
        $logaction->note = $request->get('note');
        $logaction->created_by = auth()->id();

        if ($logaction->logaction('note added')) {
            return Response::make(
                Response::text(trans('mcp.note_added_to_asset', ['asset_tag' => $asset->asset_tag]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.note_added_successfully'),
                'asset_tag' => $asset->asset_tag,
                'asset_id' => $asset->id,
                'note' => $logaction->note,
            ]);
        }

        return Response::make(Response::error(trans('mcp.note_save_failed')));
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
            'note' => $schema->string()->description('Note text to add to the asset'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the note was saved'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'asset_tag' => $schema->string()->description('Asset tag of the asset'),
            'asset_id' => $schema->number()->description('Numeric ID of the asset'),
            'note' => $schema->string()->description('The note that was saved'),
        ];
    }
}
