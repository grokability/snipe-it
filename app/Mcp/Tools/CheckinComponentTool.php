<?php

namespace App\Mcp\Tools;

use App\Events\CheckoutableCheckedIn;
use App\Models\Asset;
use App\Models\Component;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('checkin_component')]
#[Title('Checkin Component')]
#[Description('Check in one or more units of a Snipe-IT component from an asset using the checkout record ID')]
class CheckinComponentTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'component_asset_id' => 'required|integer',
            'checkin_qty' => 'nullable|integer|min:1',
            'note' => 'nullable|string|max:65535',
        ]);

        $componentAsset = DB::table('components_assets')->find($request->get('component_asset_id'));

        if (! $componentAsset) {
            return Response::make(Response::error(trans('mcp.component_checkout_not_found')));
        }

        $component = Component::find($componentAsset->component_id);

        if (! $component) {
            return Response::make(Response::error(trans('mcp.component_not_found')));
        }

        if (! Gate::allows('checkin', $component)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $maxCheckin = $componentAsset->assigned_qty ?? 1;
        $checkinQty = (int) $request->get('checkin_qty', $maxCheckin);

        if ($checkinQty > $maxCheckin) {
            return Response::make(Response::error(
                'Checkin quantity ('.$checkinQty.') exceeds assigned quantity ('.$maxCheckin.')'
            ));
        }

        $remaining = $maxCheckin - $checkinQty;

        if ($remaining === 0) {
            DB::table('components_assets')->where('id', $componentAsset->id)->delete();
        } else {
            DB::table('components_assets')->where('id', $componentAsset->id)->update(['assigned_qty' => $remaining]);
        }

        $asset = Asset::find($componentAsset->asset_id);

        event(new CheckoutableCheckedIn($component, $asset, auth()->user(), $request->get('note'), Carbon::now()));

        return Response::make(
            Response::text(trans('mcp.component_checked_in', ['name' => $component->name]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.component_checked_in', ['name' => $component->name]),
            'component_id' => $component->id,
            'component_name' => $component->name,
            'checkin_qty' => $checkinQty,
            'qty_still_checked_out' => $remaining,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'component_asset_id' => $schema->number()->description('ID of the checkout record to check in (returned by checkout_component)'),
            'checkin_qty' => $schema->number()->description('Number of units to check in (default: all assigned units)'),
            'note' => $schema->string()->description('Optional checkin note'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the checkin succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'component_id' => $schema->number()->description('Numeric ID of the component'),
            'component_name' => $schema->string()->description('Name of the component'),
            'checkin_qty' => $schema->number()->description('Number of units checked in'),
            'qty_still_checked_out' => $schema->number()->description('Units remaining checked out on this record (0 means fully returned)'),
        ];
    }
}
