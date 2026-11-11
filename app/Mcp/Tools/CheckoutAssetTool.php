<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Http\Requests\AssetCheckoutRequest;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use App\Services\AssetCheckoutService;
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
    use HasMcpScope;

    public function __construct(private readonly AssetCheckoutService $checkoutService) {}

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Checkout)) {
            return $denied;
        }

        // Shared checkout rules come from AssetCheckoutRequest::rulesArray()
        // so any rule change (new field, tightened validation, settings-
        // driven required_note) stays in sync for the HTTP form, the API
        // endpoint, and this MCP tool. Lookup-only fields (asset_tag,
        // id) are MCP-specific and stay inline.
        //
        // Catch and convert validation failures to an MCP error response
        // so callers see a structured error instead of an uncaught
        // ValidationException. The HTTP form already renders its own
        // response for the same rules via FormRequest.
        try {
            $request->validate(array_merge(AssetCheckoutRequest::rulesArray(), [
                'asset_tag' => 'nullable|max:100',
                'id' => 'nullable|integer',
            ]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $asset = $this->resolveAsset($request);

        if (! $asset) {
            return Response::make(Response::error(trans('mcp.asset_not_found')));
        }

        if (! Gate::allows('checkout', $asset)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if (! $asset->availableForCheckout()) {
            return Response::make(Response::error(trans('mcp.asset_not_available', ['asset_tag' => $asset->asset_tag])));
        }

        $checkoutType = $request->get('checkout_to_type');

        $target = match ($checkoutType) {
            'user' => User::find($request->get('assigned_user')),
            'location' => Location::find($request->get('assigned_location')),
            'asset' => Asset::where('id', '!=', $asset->id)->find($request->get('assigned_asset')),
        };

        if (! $target) {
            return Response::make(Response::error(trans('mcp.checkout_target_not_found', ['type' => $checkoutType])));
        }

        if ($this->checkoutService->checkout(
            $asset,
            $target,
            auth()->user(),
            checkoutAt: $request->filled('checkout_at') ? $request->get('checkout_at') : null,
            expectedCheckin: $request->filled('expected_checkin') ? $request->get('expected_checkin') : null,
            note: $request->filled('note') ? $request->get('note') : null,
        )) {
            return Response::make(
                Response::text(trans('mcp.asset_checked_out', ['asset_tag' => $asset->asset_tag]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.asset_checked_out', ['asset_tag' => $asset->asset_tag]),
                'asset_tag' => $asset->asset_tag,
                'checked_out_to_type' => $checkoutType,
                'checked_out_to_id' => $target->id,
            ]);
        }

        return Response::make(Response::error(trans('mcp.checkout_failed')));
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
