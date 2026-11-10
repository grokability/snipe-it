<?php

namespace App\Mcp\Tools;

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

#[Name('update_asset')]
#[Title('Update Asset')]
#[Description('Update fields on a Snipe-IT asset identified by asset tag, serial, or numeric ID')]
class UpdateAssetTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'asset_tag' => 'nullable|max:100',
            'serial' => 'nullable|string|max:255',
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_asset_tag' => 'nullable|string|max:255',
            'new_serial' => 'nullable|string|max:255',
            'status_id' => 'nullable|integer|exists:status_labels,id',
            'model_id' => 'nullable|integer|exists:models,id',
            'notes' => 'nullable|string|max:65535',
            'order_number' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric',
            'warranty_months' => 'nullable|integer',
            'location_id' => 'nullable|integer|exists:locations,id',
            'rtd_location_id' => 'nullable|integer|exists:locations,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'requestable' => 'nullable|boolean',
            'byod' => 'nullable|boolean',
            'asset_eol_date' => 'nullable|date',
            'expected_checkin' => 'nullable|date',
            'next_audit_date' => 'nullable|date',
        ]);

        $asset = $this->resolveAsset($request);

        if (! $asset) {
            return Response::make(Response::error(trans('mcp.asset_not_found')));
        }

        if (! Gate::allows('update', $asset)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $updatable = [
            'name', 'status_id', 'model_id', 'notes', 'order_number',
            'purchase_date', 'purchase_cost', 'warranty_months',
            'location_id', 'rtd_location_id', 'supplier_id',
            'requestable', 'byod', 'asset_eol_date', 'expected_checkin', 'next_audit_date',
        ];

        foreach ($updatable as $field) {
            if ($request->filled($field)) {
                $asset->{$field} = $request->get($field);
            }
        }

        // new_asset_tag / new_serial let callers change the identifiers without
        // conflicting with the asset_tag/serial used to look up the asset
        if ($request->filled('new_asset_tag')) {
            $asset->asset_tag = $request->get('new_asset_tag');
        }
        if ($request->filled('new_serial')) {
            $asset->serial = $request->get('new_serial');
        }

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
            'status_id' => $schema->number()->description('Status label ID'),
            'model_id' => $schema->number()->description('Model ID'),
            'notes' => $schema->string()->description('Notes'),
            'order_number' => $schema->string()->description('Order number'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'purchase_cost' => $schema->number()->description('Purchase cost'),
            'warranty_months' => $schema->number()->description('Warranty length in months'),
            'location_id' => $schema->number()->description('Current location ID'),
            'rtd_location_id' => $schema->number()->description('Default RTD location ID'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
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
