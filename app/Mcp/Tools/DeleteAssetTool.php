<?php

namespace App\Mcp\Tools;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_asset')]
#[Title('Delete Asset')]
#[Description('Soft-delete a Snipe-IT asset. If the asset is currently checked out it will be checked in first.')]
class DeleteAssetTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'asset_tag' => 'nullable|max:100',
            'serial' => 'nullable|string|max:255',
            'id' => 'nullable|integer',
        ]);

        $asset = $this->resolveAsset($request);

        if (! $asset) {
            return Response::make(Response::error(trans('mcp.asset_not_found')));
        }

        if (! Gate::allows('delete', $asset)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $assetTag = $asset->asset_tag;

        if ($asset->assignedTo) {
            $target = $asset->assignedTo;
            $originalValues = $asset->getRawOriginal();
            event(new CheckoutableCheckedIn($asset, $target, auth()->user(), 'Checked in on delete', date('Y-m-d H:i:s'), $originalValues));
            DB::table('assets')->where('id', $asset->id)->update(['assigned_to' => null]);
        }

        $asset->delete();

        return Response::make(
            Response::text(trans('mcp.asset_deleted', ['asset_tag' => $assetTag]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.asset_deleted', ['asset_tag' => $assetTag]),
            'asset_tag' => $assetTag,
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
            'asset_tag' => $schema->string()->description('Asset tag of the asset to delete'),
            'serial' => $schema->string()->description('Serial number of the asset to delete'),
            'id' => $schema->number()->description('Numeric ID of the asset to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'error' => $schema->boolean()->description('True if the deletion failed'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'asset_tag' => $schema->string()->description('Asset tag of the deleted asset'),
        ];
    }
}
