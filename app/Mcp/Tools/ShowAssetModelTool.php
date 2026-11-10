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

#[Name('show_asset_model')]
#[Title('Show Asset Model Details')]
#[Description('Look up a single Snipe-IT asset model by numeric ID or name and return its full details')]
class ShowAssetModelTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $model = $this->resolveModel($request);

        if ($model === false) {
            return Response::make(Response::error(trans('mcp.id_or_name_required')));
        }

        if (! $model) {
            return Response::make(Response::error(trans('mcp.asset_model_not_found')));
        }

        if (! Gate::allows('view', $model)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $model->loadCount('assets as assets_count');

        return Response::make(
            Response::text(trans('mcp.asset_model_found', ['name' => $model->name]))
        )->withStructuredContent([
            'id' => $model->id,
            'name' => $model->name,
            'model_number' => $model->model_number,
            'category_id' => $model->category_id,
            'category' => $model->category?->name,
            'manufacturer_id' => $model->manufacturer_id,
            'manufacturer' => $model->manufacturer?->name,
            'depreciation_id' => $model->depreciation_id,
            'depreciation' => $model->depreciation?->name,
            'assets_count' => $model->assets_count,
            'eol' => $model->eol,
            'min_amt' => $model->min_amt,
            'notes' => $model->notes,
            'created_at' => $model->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $model->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }

    private function resolveModel(Request $request): AssetModel|false|null
    {
        if ($request->filled('id')) {
            return AssetModel::with('category', 'manufacturer', 'depreciation')->find($request->get('id'));
        }
        if ($request->filled('name')) {
            return AssetModel::with('category', 'manufacturer', 'depreciation')->where('name', $request->get('name'))->first();
        }

        return false;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the asset model to look up'),
            'name' => $schema->string()->description('Name of the asset model to look up'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric asset model ID'),
            'name' => $schema->string()->description('Asset model name'),
            'model_number' => $schema->string()->description('Model number'),
            'category_id' => $schema->number()->description('Category ID'),
            'category' => $schema->string()->description('Category name'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'manufacturer' => $schema->string()->description('Manufacturer name'),
            'depreciation_id' => $schema->number()->description('Depreciation schedule ID'),
            'depreciation' => $schema->string()->description('Depreciation schedule name'),
            'assets_count' => $schema->number()->description('Number of assets using this model'),
            'eol' => $schema->number()->description('End of life in months'),
            'min_amt' => $schema->number()->description('Minimum quantity alert threshold'),
            'notes' => $schema->string()->description('Notes'),
            'created_at' => $schema->string()->description('Creation timestamp'),
            'updated_at' => $schema->string()->description('Last update timestamp'),
        ];
    }
}
