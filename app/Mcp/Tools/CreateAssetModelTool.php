<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\AssetModel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
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
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', AssetModel::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $assetModel = new AssetModel;
        try {
            $validated = Validator::make($request->all(), $assetModel->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $assetModel->fill(array_merge($request->all(), $validated));
        $assetModel->created_by = auth()->id();

        if ($assetModel->save()) {
            return Response::make(
                Response::text(trans('mcp.asset_model_created', ['name' => $assetModel->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.asset_model_created', ['name' => $assetModel->name]),
                'id' => $assetModel->id,
                'name' => $assetModel->name,
                'category_id' => $assetModel->category_id,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $assetModel->getErrors()->first()])));
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
