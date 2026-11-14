<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\Supplier;
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
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', Asset::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Resolve name-based FK inputs to numeric IDs before validation
        // so the downstream rules + fill see a consistent shape.
        // Lookups run through the model's normal global scopes, so a
        // non-superuser in a locked-down FMCS install gets a not-found
        // error for a name that only exists in a tenant they cannot
        // see, matching the UI's "that record does not exist" posture.
        $fkMap = [
            ['model_id', 'model_name', AssetModel::class],
            ['status_id', 'status_name', Statuslabel::class],
            ['company_id', 'company_name', Company::class],
            ['location_id', 'location_name', Location::class],
            ['rtd_location_id', 'rtd_location_name', Location::class],
            ['supplier_id', 'supplier_name', Supplier::class],
        ];

        $resolvedFks = [];
        foreach ($fkMap as [$idField, $nameField, $modelClass]) {
            $resolved = $this->resolveFkByName($request, $idField, $nameField, $modelClass);
            if ($this->fkResolutionFailed($resolved)) {
                return Response::make(Response::error($this->fkErrorMessage($resolved)));
            }
            if ($resolved !== null) {
                $resolvedFks[$idField] = $resolved;
            }
        }

        $input = array_merge($request->all(), $resolvedFks);

        $asset = new Asset;
        try {
            $validated = Validator::make($input, $asset->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $asset->fill(array_merge($input, $validated));
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
            'model_id' => $schema->number()->description('Asset model ID. One of model_id or model_name is required.'),
            'model_name' => $schema->string()->description('Asset model name. Resolved to an ID server-side. Fails if the name matches more than one model; pass model_id to disambiguate.'),
            'status_id' => $schema->number()->description('Status label ID. One of status_id or status_name is required.'),
            'status_name' => $schema->string()->description('Status label name (e.g. "Ready to Deploy"). Resolved to an ID server-side.'),
            'asset_tag' => $schema->string()->description('Asset tag (required)'),
            'name' => $schema->string()->description('Display name for the asset'),
            'serial' => $schema->string()->description('Serial number'),
            'company_id' => $schema->number()->description('Company ID. Alternative: company_name.'),
            'company_name' => $schema->string()->description('Company name. Resolved to an ID server-side.'),
            'location_id' => $schema->number()->description('Current location ID. Alternative: location_name.'),
            'location_name' => $schema->string()->description('Current location name. Resolved to an ID server-side.'),
            'rtd_location_id' => $schema->number()->description('Default RTD location ID. Alternative: rtd_location_name.'),
            'rtd_location_name' => $schema->string()->description('Default RTD location name. Resolved to an ID server-side.'),
            'supplier_id' => $schema->number()->description('Supplier ID. Alternative: supplier_name.'),
            'supplier_name' => $schema->string()->description('Supplier name. Resolved to an ID server-side.'),
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
