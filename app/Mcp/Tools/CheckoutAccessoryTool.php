<?php

namespace App\Mcp\Tools;

use App\Events\CheckoutableCheckedOut;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('checkout_accessory')]
#[Title('Checkout Accessory')]
#[Description('Check out a Snipe-IT accessory to a user, location, or asset')]
class CheckoutAccessoryTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'checkout_to_type' => 'required|in:user,location,asset',
            'assigned_user' => 'nullable|integer',
            'assigned_location' => 'nullable|integer',
            'assigned_asset' => 'nullable|integer',
            'note' => 'nullable|string|max:65535',
        ]);

        $accessory = $this->resolveAccessory($request);

        if (! $accessory) {
            return Response::make(Response::error(trans('mcp.accessory_not_found')));
        }

        if (! Gate::allows('checkout', $accessory)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if ($accessory->numRemaining() < 1) {
            return Response::make(Response::error(trans('mcp.no_units_available')));
        }

        $checkoutType = $request->get('checkout_to_type');

        $target = match ($checkoutType) {
            'user' => User::find($request->get('assigned_user')),
            'location' => Location::find($request->get('assigned_location')),
            'asset' => Asset::find($request->get('assigned_asset')),
        };

        if (! $target) {
            return Response::make(Response::error(trans('mcp.checkout_target_not_found', ['type' => $checkoutType])));
        }

        $checkout = new AccessoryCheckout([
            'accessory_id' => $accessory->id,
            'created_at' => Carbon::now(),
            'assigned_to' => $target->id,
            'assigned_type' => $target::class,
            'note' => $request->get('note'),
        ]);
        $checkout->created_by = auth()->id();
        $checkout->save();

        event(new CheckoutableCheckedOut(
            $accessory,
            $target,
            auth()->user(),
            $request->get('note'),
            [],
            1,
        ));

        return Response::make(
            Response::text(trans('mcp.accessory_checked_out', ['name' => $accessory->name]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.accessory_checked_out', ['name' => $accessory->name]),
            'accessory_id' => $accessory->id,
            'accessory_name' => $accessory->name,
            'checkout_id' => $checkout->id,
            'checked_out_to_type' => $checkoutType,
            'checked_out_to_id' => $target->id,
        ]);
    }

    private function resolveAccessory(Request $request): ?Accessory
    {
        if ($request->filled('id')) {
            return Accessory::withCount('checkouts as checkouts_count')->find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Accessory::withCount('checkouts as checkouts_count')->where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the accessory to check out'),
            'name' => $schema->string()->description('Name of the accessory to check out'),
            'checkout_to_type' => $schema->string()->description('Target type: user, location, or asset (required)'),
            'assigned_user' => $schema->number()->description('User ID to check out to'),
            'assigned_location' => $schema->number()->description('Location ID to check out to'),
            'assigned_asset' => $schema->number()->description('Asset ID to check out to'),
            'note' => $schema->string()->description('Optional checkout note'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the checkout succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'accessory_id' => $schema->number()->description('Numeric ID of the accessory'),
            'accessory_name' => $schema->string()->description('Name of the accessory'),
            'checkout_id' => $schema->number()->description('ID of the checkout record (use this for checkin)'),
            'checked_out_to_type' => $schema->string()->description('Type of target: user, location, or asset'),
            'checked_out_to_id' => $schema->number()->description('ID of the target'),
        ];
    }
}
