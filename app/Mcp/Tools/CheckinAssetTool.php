<?php

namespace App\Mcp\Tools;

use App\Events\CheckoutableCheckedIn;
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
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'asset_tag' => 'nullable|max:100',
            'id' => 'nullable|integer',
            'note' => 'nullable|string|max:1000',
        ]);

        $asset = $this->resolveAsset($request);

        if (! $asset) {
            return Response::make(Response::error(trans('mcp.asset_not_found')));
        }

        if (! Gate::allows('checkin', $asset)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $target = $asset->assignedTo;

        if (is_null($target)) {
            return Response::make(Response::error(trans('mcp.asset_not_checked_out', ['asset_tag' => $asset->asset_tag])));
        }

        $originalValues = $asset->getRawOriginal();
        $checkinAt = date('Y-m-d H:i:s');

        $asset->expected_checkin = null;
        $asset->last_checkin = now();
        $asset->assignedTo()->disassociate($asset);
        $asset->accepted = null;
        $asset->location_id = $asset->rtd_location_id;

        if ($asset->save()) {
            event(new CheckoutableCheckedIn($asset, $target, auth()->user(), $request->get('note'), $checkinAt, $originalValues));

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
