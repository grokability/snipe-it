<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Events\CheckoutableCheckedIn;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
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

#[Name('checkin_license')]
#[Title('Checkin License')]
#[Description('Check in a Snipe-IT license seat by its seat ID, returning it to the available pool')]
class CheckinLicenseTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Checkin)) {
            return $denied;
        }

        $request->validate([
            'seat_id' => 'required|integer',
            'note' => 'nullable|string|max:65535',
        ]);

        $seat = LicenseSeat::with('license')->find($request->get('seat_id'));
        $license = $seat?->license;

        // License checkin reuses the checkout gate. Collapse the three
        // "no" cases so seat state can't be probed by an unauthorized
        // caller.
        if (! $seat || ! $license || ! Gate::allows('checkout', $license)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if (is_null($seat->assigned_to) && is_null($seat->asset_id)) {
            return Response::make(Response::error(trans('mcp.seat_not_checked_out')));
        }

        $returnTo = null;
        if ($seat->assigned_to) {
            $returnTo = User::withTrashed()->find($seat->assigned_to);
        } elseif ($seat->asset_id) {
            $returnTo = Asset::find($seat->asset_id);
        }

        $note = $request->get('note');

        $seat->assigned_to = null;
        $seat->asset_id = null;
        $seat->notes = $note;

        if (! $license->reassignable) {
            $seat->unreassignable_seat = true;
        }

        if ($seat->save()) {
            event(new CheckoutableCheckedIn($seat, $returnTo, auth()->user(), $note));

            return Response::make(
                Response::text(trans('mcp.license_seat_checked_in', ['id' => $seat->id]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.license_seat_checked_in', ['id' => $seat->id]),
                'seat_id' => $seat->id,
                'license_id' => $license->id,
                'license_name' => $license->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.checkin_failed')));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'seat_id' => $schema->number()->description('ID of the license seat to check in (returned by checkout_license)'),
            'note' => $schema->string()->description('Optional checkin note'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the checkin succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'seat_id' => $schema->number()->description('ID of the seat that was checked in'),
            'license_id' => $schema->number()->description('Numeric ID of the license'),
            'license_name' => $schema->string()->description('Name of the license'),
        ];
    }
}
