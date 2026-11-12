<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
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

#[Name('restore_asset')]
#[Title('Restore Asset')]
#[Description('Restore a soft-deleted Snipe-IT asset')]
class RestoreAssetTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Delete)) {
            return $denied;
        }

        if (! Gate::allows('delete', Asset::class)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.asset')])));
        }

        $request->validate([
            'id' => 'required|integer',
        ]);

        $asset = Asset::withTrashed()->find($request->get('id'));

        if (! $asset) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.asset')])));
        }

        if (! $asset->deleted_at) {
            return Response::make(Response::error(trans('mcp.asset_not_deleted')));
        }

        $asset->restore();

        return Response::make(
            Response::text(trans('mcp.asset_restored', ['asset_tag' => $asset->asset_tag]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.asset_restored', ['asset_tag' => $asset->asset_tag]),
            'id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the asset to restore (required)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the restore succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the restored asset'),
            'asset_tag' => $schema->string()->description('Asset tag of the restored asset'),
        ];
    }
}
