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

#[Name('show_asset')]
#[Title('Show Asset Details')]
#[Description('Look up a single Snipe-IT asset by asset tag or numeric ID and return its full details')]
class ShowAssetTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'asset_tag' => 'nullable|max:100',
            'serial' => 'nullable|string|max:255',
            'id' => 'nullable|integer',
        ]);

        $with = ['status', 'model.category', 'model.manufacturer', 'location', 'defaultLoc', 'company', 'supplier', 'adminuser'];
        $asset = null;

        if ($request->filled('asset_tag')) {
            $asset = Asset::where('asset_tag', $request->get('asset_tag'))->with($with)->first();
        } elseif ($request->filled('serial')) {
            $asset = Asset::where('serial', $request->get('serial'))->with($with)->first();
        } elseif ($request->filled('id')) {
            $asset = Asset::with($with)->find($request->get('id'));
        }

        if (! $asset) {
            return Response::make(
                Response::error('Asset not found')
            );
        }

        if (! Gate::allows('view', $asset)) {
            return Response::make(Response::error('Unauthorized'));
        }

        return Response::make(
            Response::text('Asset '.$asset->asset_tag.' found')
        )->withStructuredContent($this->formatAsset($asset));
    }

    private function formatAsset(Asset $asset): array
    {
        return [
            'id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
            'name' => $asset->name,
            'serial' => $asset->serial,
            'status' => $asset->status?->name,
            'status_type' => $asset->status?->getStatuslabelType(),
            'model' => $asset->model?->name,
            'model_number' => $asset->model?->model_number,
            'category' => $asset->model?->category?->name,
            'manufacturer' => $asset->model?->manufacturer?->name,
            'company' => $asset->company?->name,
            'location' => $asset->location?->name,
            'rtd_location' => $asset->defaultLoc?->name,
            'supplier' => $asset->supplier?->name,
            'assigned_to_id' => $asset->assigned_to,
            'assigned_to_type' => $asset->assigned_type ? class_basename($asset->assigned_type) : null,
            'notes' => $asset->notes,
            'order_number' => $asset->order_number,
            'purchase_date' => $asset->purchase_date?->format('Y-m-d'),
            'purchase_cost' => $asset->purchase_cost,
            'warranty_months' => $asset->warranty_months,
            'last_checkout' => $asset->last_checkout,
            'last_checkin' => $asset->last_checkin,
            'expected_checkin' => $asset->expected_checkin,
            'last_audit_date' => $asset->last_audit_date,
            'created_at' => $asset->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $asset->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'asset_tag' => $schema->string()
                ->description('The asset tag of the asset to look up'),
            'serial' => $schema->string()
                ->description('The serial number of the asset to look up'),
            'id' => $schema->number()
                ->description('The numeric ID of the asset to look up'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric asset ID'),
            'asset_tag' => $schema->string()->description('Asset tag'),
            'name' => $schema->string()->description('Asset name'),
            'serial' => $schema->string()->description('Serial number'),
            'status' => $schema->string()->description('Status label name'),
            'status_type' => $schema->string()->description('Status type: deployable, pending, or archived'),
            'model' => $schema->string()->description('Asset model name'),
            'model_number' => $schema->string()->description('Model number'),
            'category' => $schema->string()->description('Category name'),
            'manufacturer' => $schema->string()->description('Manufacturer name'),
            'company' => $schema->string()->description('Company name'),
            'location' => $schema->string()->description('Current location name'),
            'rtd_location' => $schema->string()->description('Default return-to-deploy location name'),
            'assigned_to_id' => $schema->number()->description('ID of the entity this asset is currently assigned to'),
            'assigned_to_type' => $schema->string()->description('Type of entity assigned to: User, Asset, or Location'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'purchase_cost' => $schema->string()->description('Purchase cost'),
            'last_checkout' => $schema->string()->description('Date of last checkout'),
            'last_checkin' => $schema->string()->description('Date of last checkin'),
        ];
    }
}
