<?php

namespace App\Mcp\Tools;

use App\Models\Asset;
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

#[Name('create_asset')]
#[Title('Create Asset')]
#[Description('Create a new Snipe-IT asset')]
class CreateAssetTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', Asset::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        try {
            $request->validate([
                'model_id' => 'required|integer|exists:models,id',
                'status_id' => 'required|integer|exists:status_labels,id',
                'asset_tag' => 'required|string|max:255',
                'name' => 'nullable|string|max:255',
                'serial' => 'nullable|string',
                'company_id' => 'nullable|integer',
                'location_id' => 'nullable|integer|exists:locations,id',
                'rtd_location_id' => 'nullable|integer|exists:locations,id',
                'supplier_id' => 'nullable|integer|exists:suppliers,id',
                'purchase_date' => 'nullable|date_format:Y-m-d',
                'purchase_cost' => 'nullable|numeric',
                'order_number' => 'nullable|string|max:191',
                'warranty_months' => 'nullable|integer|min:0|max:240',
                'requestable' => 'nullable|boolean',
                'notes' => 'nullable|string|max:65535',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $asset = new Asset;
        $asset->model_id = $request->get('model_id');
        $asset->status_id = $request->get('status_id');
        $asset->asset_tag = $request->get('asset_tag');
        $asset->created_by = auth()->id();

        foreach (['name', 'serial', 'company_id', 'location_id', 'rtd_location_id', 'supplier_id', 'purchase_date', 'purchase_cost', 'order_number', 'warranty_months', 'requestable', 'notes'] as $field) {
            if ($request->filled($field)) {
                $asset->{$field} = $request->get($field);
            }
        }

        if ($asset->save()) {
            return Response::make(
                Response::text(trans('mcp.asset_created', ['asset_tag' => $asset->asset_tag]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.asset_created', ['asset_tag' => $asset->asset_tag]),
                'id' => $asset->id,
                'asset_tag' => $asset->asset_tag,
                'name' => $asset->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $asset->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'model_id' => $schema->number()->description('Asset model ID (required)'),
            'status_id' => $schema->number()->description('Status label ID (required)'),
            'asset_tag' => $schema->string()->description('Asset tag (required)'),
            'name' => $schema->string()->description('Display name for the asset'),
            'serial' => $schema->string()->description('Serial number'),
            'company_id' => $schema->number()->description('Company ID'),
            'location_id' => $schema->number()->description('Current location ID'),
            'rtd_location_id' => $schema->number()->description('Default RTD location ID'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'purchase_cost' => $schema->number()->description('Purchase cost'),
            'order_number' => $schema->string()->description('Order number'),
            'warranty_months' => $schema->number()->description('Warranty length in months (0-240)'),
            'requestable' => $schema->boolean()->description('Whether the asset is user-requestable'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the asset was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new asset'),
            'asset_tag' => $schema->string()->description('Asset tag of the new asset'),
            'name' => $schema->string()->description('Display name of the new asset'),
        ];
    }
}
