<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Asset;
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

#[Name('create_asset')]
#[Title('Create Asset')]
#[Description('Create a new Snipe-IT asset')]
class CreateAssetTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', Asset::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $asset = new Asset;
        try {
            $validated = Validator::make($request->all(), $asset->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $asset->fill(array_merge($request->all(), $validated));
        $asset->created_by = auth()->id();

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
