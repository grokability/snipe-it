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

#[Name('update_asset_model')]
#[Title('Update Asset Model')]
#[Description('Update fields on a Snipe-IT asset model identified by numeric ID or name')]
class UpdateAssetModelTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_name' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
            'depreciation_id' => 'nullable|integer|exists:depreciations,id',
            'model_number' => 'nullable|string|max:255',
            'eol' => 'nullable|integer|min:0|max:240',
            'min_amt' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'requestable' => 'nullable|boolean',
            'require_serial' => 'nullable|boolean',
        ]);

        $model = $this->resolveModel($request);

        if (! $model) {
            return Response::make(Response::error('Asset model not found'));
        }

        if (! Gate::allows('update', $model)) {
            return Response::make(Response::error('Unauthorized'));
        }

        if ($request->filled('new_name')) {
            $model->name = $request->get('new_name');
        }

        foreach (['category_id', 'manufacturer_id', 'depreciation_id', 'model_number', 'eol', 'min_amt', 'notes', 'requestable', 'require_serial'] as $field) {
            if ($request->filled($field)) {
                $model->{$field} = $request->get($field);
            }
        }

        if ($model->save()) {
            return Response::make(
                Response::text('Asset model '.$model->name.' updated successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Asset model updated successfully',
                'id' => $model->id,
                'name' => $model->name,
            ]);
        }

        return Response::make(Response::error('Update failed: '.$model->getErrors()->first()));
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
            'id' => $schema->number()->description('Numeric ID to identify the asset model'),
            'name' => $schema->string()->description('Name to identify the asset model'),
            'new_name' => $schema->string()->description('New name (renames the asset model)'),
            'category_id' => $schema->number()->description('Category ID'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'depreciation_id' => $schema->number()->description('Depreciation schedule ID'),
            'model_number' => $schema->string()->description('Model number'),
            'eol' => $schema->number()->description('End of life in months (0-240)'),
            'min_amt' => $schema->number()->description('Minimum quantity alert threshold'),
            'notes' => $schema->string()->description('Notes'),
            'requestable' => $schema->boolean()->description('Whether the model can be requested'),
            'require_serial' => $schema->boolean()->description('Whether serial numbers are required'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the asset model'),
            'name' => $schema->string()->description('Name of the asset model'),
        ];
    }
}
