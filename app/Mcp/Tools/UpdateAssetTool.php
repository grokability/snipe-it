<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Asset;
use App\Models\AssetModel;
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

#[Name('update_asset')]
#[Title('Update Asset')]
#[Description('Update fields on a Snipe-IT asset identified by asset tag, serial, or numeric ID')]
class UpdateAssetTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        try {
            $request->validate([
                'asset_tag' => 'nullable|max:255',
                'serial' => 'nullable|max:255',
                'id' => 'nullable|integer',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $asset = $this->resolveAsset($request);

        if (! $asset || ! Gate::allows('update', $asset)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.asset')])));
        }

        $fkMap = [
            ['model_id', 'model_name', AssetModel::class],
            ['status_id', 'status_name', Statuslabel::class],
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

        // new_asset_tag / new_serial let callers change the identifiers
        // without conflicting with the asset_tag/serial used to look up
        // the asset. Map them to the model columns first so the
        // validator sees them, then intersect with the model's rules so
        // partial updates only validate what the caller actually sent.
        $input = array_merge($request->all(), $resolvedFks);
        unset($input['asset_tag'], $input['serial']);
        if ($request->filled('new_asset_tag')) {
            $input['asset_tag'] = $request->get('new_asset_tag');
        }
        if ($request->filled('new_serial')) {
            $input['serial'] = $request->get('new_serial');
        }

        $rules = array_intersect_key($asset->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $asset->fill(array_merge($input, $validated));

        if ($asset->save()) {
            return Response::make(
                Response::text(trans('mcp.asset_updated', ['asset_tag' => $asset->asset_tag]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.asset_updated', ['asset_tag' => $asset->asset_tag]),
                'asset_tag' => $asset->asset_tag,
                'id' => $asset->id,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $asset->getErrors()->first()])));
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
            'asset_tag' => $schema->string()->description('Asset tag to identify the asset'),
            'serial' => $schema->string()->description('Serial number to identify the asset'),
            'id' => $schema->number()->description('Numeric ID to identify the asset'),
            'name' => $schema->string()->description('New display name'),
            'new_asset_tag' => $schema->string()->description('New asset tag (renames the asset tag itself)'),
            'new_serial' => $schema->string()->description('New serial number'),
            'status_id' => $schema->number()->description('Status label ID. Alternative: status_name.'),
            'status_name' => $schema->string()->description('Status label name. Resolved to an ID server-side.'),
            'model_id' => $schema->number()->description('Model ID. Alternative: model_name.'),
            'model_name' => $schema->string()->description('Asset model name. Resolved to an ID server-side.'),
            'notes' => $schema->string()->description('Notes'),
            'order_number' => $schema->string()->description('Order number'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'purchase_cost' => $schema->number()->description('Purchase cost'),
            'warranty_months' => $schema->number()->description('Warranty length in months'),
            'location_id' => $schema->number()->description('Current location ID. Alternative: location_name.'),
            'location_name' => $schema->string()->description('Current location name. Resolved to an ID server-side.'),
            'rtd_location_id' => $schema->number()->description('Default RTD location ID. Alternative: rtd_location_name.'),
            'rtd_location_name' => $schema->string()->description('Default RTD location name. Resolved to an ID server-side.'),
            'supplier_id' => $schema->number()->description('Supplier ID. Alternative: supplier_name.'),
            'supplier_name' => $schema->string()->description('Supplier name. Resolved to an ID server-side.'),
            'requestable' => $schema->boolean()->description('Whether the asset is user-requestable'),
            'byod' => $schema->boolean()->description('Bring-your-own-device flag'),
            'asset_eol_date' => $schema->string()->description('Asset end-of-life date (YYYY-MM-DD)'),
            'expected_checkin' => $schema->string()->description('Expected check-in date (YYYY-MM-DD)'),
            'next_audit_date' => $schema->string()->description('Next scheduled audit date (YYYY-MM-DD)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'error' => $schema->boolean()->description('True if the update failed'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'asset_tag' => $schema->string()->description('Asset tag of the updated asset'),
            'id' => $schema->number()->description('Numeric ID of the updated asset'),
        ];
    }
}
