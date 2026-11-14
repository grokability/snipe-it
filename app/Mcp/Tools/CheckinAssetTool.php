<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Http\Requests\AssetCheckinRequest;
use App\Mcp\Traits\HasMcpScope;
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

#[Name('checkin_asset')]
#[Title('Check In Asset')]
#[Description('Check a currently checked-out Snipe-IT asset back in')]
class CheckinAssetTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Checkin)) {
            return $denied;
        }

        // Shared checkin rules come from AssetCheckinRequest::rulesArray()
        // so the require_checkinout_notes setting (and any future rule
        // additions) stay in sync between HTTP and MCP paths. Catch
        // validation failures and convert to an MCP error response.
        try {
            $request->validate(array_merge(AssetCheckinRequest::rulesArray(), [
                'asset_tag' => 'nullable|max:100',
                'id' => 'nullable|integer',
                'note' => 'nullable|string|max:1000',
            ]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $asset = $this->resolveAsset($request);

        if (! $asset) {
            return Response::make(Response::error(trans('mcp.asset_not_found')));
        }

        if (! Gate::allows('checkin', $asset)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if (is_null($asset->assignedTo)) {
            return Response::make(Response::error(trans('mcp.asset_not_checked_out', ['asset_tag' => $asset->asset_tag])));
        }

        if ($asset->checkIn(auth()->user(), $request->get('note'))) {
            return Response::make(
                Response::text(trans('mcp.asset_checked_in', ['asset_tag' => $asset->asset_tag]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.asset_checked_in', ['asset_tag' => $asset->asset_tag]),
                'asset_tag' => $asset->asset_tag,
                'model' => $asset->model?->name,
                'location' => $asset->location?->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.checkin_failed_error', ['error' => $asset->getErrors()->first()])));
    }

    private function resolveAsset(Request $request): ?Asset
    {
        if ($request->filled('asset_tag')) {
            return Asset::where('asset_tag', $request->get('asset_tag'))
                ->with('model', 'location')
                ->first();
        }

        if ($request->filled('id')) {
            return Asset::with('model', 'location')->find($request->get('id'));
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'asset_tag' => $schema->string()
                ->description('Asset tag of the asset to check in'),
            'id' => $schema->number()
                ->description('Numeric ID of the asset to check in'),
            'note' => $schema->string()
                ->description('Optional note to attach to this checkin'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->string()->description('True if the checkin succeeded'),
            'error' => $schema->string()->description('True if the checkin failed'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'asset_tag' => $schema->string()->description('Asset tag of the checked-in asset'),
            'model' => $schema->string()->description('Model name of the checked-in asset'),
            'location' => $schema->string()->description('Location the asset returned to'),
        ];
    }
}
