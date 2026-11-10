<?php

namespace App\Mcp\Tools;

use App\Models\Asset;
use App\Models\Component;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('checkout_component')]
#[Title('Checkout Component')]
#[Description('Check out one or more units of a Snipe-IT component to an asset')]
class CheckoutComponentTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        try {
            $request->validate([
                'id' => 'nullable|integer',
                'name' => 'nullable|string|max:191',
                'asset_id' => 'required|integer|exists:assets,id',
                'assigned_qty' => 'nullable|integer|min:1',
                'note' => 'nullable|string|max:65535',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $component = $this->resolveComponent($request);

        if (! $component) {
            return Response::make(Response::error('Component not found'));
        }

        if (! Gate::allows('checkout', $component)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $qty = (int) $request->get('assigned_qty', 1);

        if ($component->numRemaining() < $qty) {
            return Response::make(Response::error(
                'Not enough units available. Requested: '.$qty.', remaining: '.$component->numRemaining()
            ));
        }

        $asset = Asset::find($request->get('asset_id'));

        $component->assets()->attach($component->id, [
            'component_id' => $component->id,
            'created_at' => Carbon::now(),
            'assigned_qty' => $qty,
            'created_by' => auth()->id(),
            'asset_id' => $asset->id,
            'note' => $request->get('note'),
        ]);

        $pivotId = $component->assets()->wherePivot('asset_id', $asset->id)->latest('components_assets.created_at')->first()?->pivot->id;

        $component->logCheckout($request->get('note'), $asset, null, [], $qty);

        return Response::make(
            Response::text('Component '.$component->name.' checked out to asset '.$asset->asset_tag)
        )->withStructuredContent([
            'success' => true,
            'message' => 'Component checked out successfully',
            'component_id' => $component->id,
            'component_name' => $component->name,
            'asset_id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
            'assigned_qty' => $qty,
            'component_asset_id' => $pivotId,
        ]);
    }

    private function resolveComponent(Request $request): ?Component
    {
        if ($request->filled('id')) {
            return Component::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Component::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the component to check out'),
            'name' => $schema->string()->description('Name of the component to check out'),
            'asset_id' => $schema->number()->description('Asset ID to check the component out to (required)'),
            'assigned_qty' => $schema->number()->description('Number of units to check out (default: 1)'),
            'note' => $schema->string()->description('Optional checkout note'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the checkout succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'component_id' => $schema->number()->description('Numeric ID of the component'),
            'component_name' => $schema->string()->description('Name of the component'),
            'asset_id' => $schema->number()->description('ID of the asset checked out to'),
            'asset_tag' => $schema->string()->description('Asset tag of the asset checked out to'),
            'assigned_qty' => $schema->number()->description('Number of units checked out'),
            'component_asset_id' => $schema->number()->description('ID of the checkout record (use this for checkin)'),
        ];
    }
}
