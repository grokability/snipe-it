<?php

namespace App\Mcp\Tools;

use App\Models\AssetModel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_asset_model')]
#[Title('Create Asset Model')]
#[Description('Create a new Snipe-IT asset model')]
class CreateAssetModelTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', AssetModel::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|integer|exists:categories,id',
                'model_number' => 'nullable|string|max:255',
                'manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
                'depreciation_id' => 'nullable|integer|exists:depreciations,id',
                'eol' => 'nullable|integer|min:0|max:240',
                'min_amt' => 'nullable|integer|min:0',
                'notes' => 'nullable|string',
                'requestable' => 'nullable|boolean',
                'require_serial' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $assetModel = new AssetModel;
        $assetModel->name = $request->get('name');
        $assetModel->category_id = $request->get('category_id');
        $assetModel->created_by = auth()->id();

        foreach (['model_number', 'manufacturer_id', 'depreciation_id', 'eol', 'min_amt', 'notes', 'requestable', 'require_serial'] as $f) {
            if ($request->filled($f)) {
                $assetModel->{$f} = $request->get($f);
            }
        }

        if ($assetModel->save()) {
            return Response::make(
                Response::text('Asset model '.$assetModel->name.' created successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Asset model created successfully',
                'id' => $assetModel->id,
                'name' => $assetModel->name,
                'category_id' => $assetModel->category_id,
            ]);
        }

        return Response::make(Response::error('Create failed: '.$assetModel->getErrors()->first()));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Asset model name (required)'),
            'category_id' => $schema->number()->description('Category ID (required)'),
            'model_number' => $schema->string()->description('Model number'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'depreciation_id' => $schema->number()->description('Depreciation schedule ID'),
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
            'success' => $schema->boolean()->description('True if the asset model was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new asset model'),
            'name' => $schema->string()->description('Name of the new asset model'),
            'category_id' => $schema->number()->description('Category ID of the new asset model'),
        ];
    }
}
