<?php

namespace App\Mcp\Tools;

use App\Models\AssetModel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_asset_model')]
#[Title('Delete Asset Model')]
#[Description('Soft-delete a Snipe-IT asset model by numeric ID or name')]
class DeleteAssetModelTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $model = $this->resolveModel($request);

        if (! $model) {
            return Response::make(Response::error(trans('mcp.asset_model_not_found')));
        }

        if (! Gate::allows('delete', $model)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if ($model->assets()->count() > 0) {
            return Response::make(Response::error(trans('mcp.model_has_assets')));
        }

        $name = $model->name;

        $model->delete();

        return Response::make(
            Response::text(trans('mcp.asset_model_deleted', ['name' => $name]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.asset_model_deleted', ['name' => $name]),
            'name' => $name,
        ]);
    }

    private function resolveModel(Request $request): ?AssetModel
    {
        if ($request->filled('id')) {
            return AssetModel::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return AssetModel::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the asset model to delete'),
            'name' => $schema->string()->description('Name of the asset model to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted asset model'),
        ];
    }
}
