<?php

namespace App\Mcp\Tools;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('checkout_asset')]
#[Title('Checkout Asset')]
#[Description('Check out a Snipe-IT asset to a user, location, or another asset')]
class CheckoutAssetTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'asset_tag' => 'nullable|max:100',
            'id' => 'nullable|integer',
            'checkout_to_type' => 'required|string|in:user,location,asset',
            'assigned_user' => 'nullable|integer',
            'assigned_location' => 'nullable|integer',
            'assigned_asset' => 'nullable|integer',
            'note' => 'nullable|string|max:1000',
            'checkout_at' => 'nullable|date',
            'expected_checkin' => 'nullable|date',
        ]);

        $asset = $this->resolveAsset($request);

        if (! $asset) {
            return Response::make(Response::error('Asset not found'));
        }

        if (! Gate::allows('checkout', $asset)) {
            return Response::make(Response::error('Unauthorized'));
        }

        if (! $asset->availableForCheckout()) {
            return Response::make(Response::error('Asset '.$asset->asset_tag.' is not available for checkout'));
        }

        $checkoutType = $request->get('checkout_to_type');
        $target = null;

        if ($checkoutType === 'user') {
            $target = User::find($request->get('assigned_user'));
            if ($target) {
                $asset->location_id = $target->location_id ?? $asset->location_id;
            }
        } elseif ($checkoutType === 'location') {
            $target = Location::find($request->get('assigned_location'));
            if ($target) {
                $asset->location_id = $target->id;
            }
        } elseif ($checkoutType === 'asset') {
            $target = Asset::where('id', '!=', $asset->id)->find($request->get('assigned_asset'));
            if ($target) {
                $asset->location_id = $target->location_id ?? $asset->location_id;
            }
        }

        if (! $target) {
            return Response::make(Response::error('The specified '.$checkoutType.' was not found'));
        }

        $checkoutAt = $request->filled('checkout_at') ? $request->get('checkout_at') : date('Y-m-d H:i:s');
        $expectedCheckin = $request->filled('expected_checkin') ? $request->get('expected_checkin') : null;
        $note = $request->filled('note') ? $request->get('note') : null;

        if ($asset->checkOut($target, auth()->user(), $checkoutAt, $expectedCheckin, $note, $asset->name, $asset->location_id)) {
            return Response::make(
                Response::text('Asset '.$asset->asset_tag.' checked out successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Asset checked out successfully',
                'asset_tag' => $asset->asset_tag,
                'checked_out_to_type' => $checkoutType,
                'checked_out_to_id' => $target->id,
            ]);
        }

        return Response::make(Response::error('Checkout failed'));
    }

    private function resolveAsset(Request $request): ?Asset
    {
        if ($request->filled('asset_tag')) {
            return Asset::where('asset_tag', $request->get('asset_tag'))
                ->with('status')
                ->first();
        }

        if ($request->filled('id')) {
            return Asset::with('status')->find($request->get('id'));
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'asset_tag' => $schema->string()
                ->description('Asset tag of the asset to check out'),
            'id' => $schema->number()
                ->description('Numeric ID of the asset to check out'),
            'checkout_to_type' => $schema->string()
                ->description('What to check the asset out to: user, location, or asset')
                ->required(),
            'assigned_user' => $schema->number()
                ->description('ID of the user to check the asset out to (when checkout_to_type is user)'),
            'assigned_location' => $schema->number()
                ->description('ID of the location to check the asset out to (when checkout_to_type is location)'),
            'assigned_asset' => $schema->number()
                ->description('ID of the asset to check the asset out to (when checkout_to_type is asset)'),
            'note' => $schema->string()
                ->description('Optional note to attach to this checkout'),
            'checkout_at' => $schema->string()
                ->description('Checkout date/time (defaults to now, format: YYYY-MM-DD)'),
            'expected_checkin' => $schema->string()
                ->description('Expected checkin date (format: YYYY-MM-DD)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->string()->description('True if the checkout succeeded'),
            'error' => $schema->string()->description('True if the checkout failed'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'asset_tag' => $schema->string()->description('Asset tag of the checked-out asset'),
            'checked_out_to_type' => $schema->string()->description('Type of entity the asset was checked out to'),
            'checked_out_to_id' => $schema->number()->description('ID of the entity the asset was checked out to'),
        ];
    }
}
