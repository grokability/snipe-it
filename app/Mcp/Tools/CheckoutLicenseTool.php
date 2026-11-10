<?php

namespace App\Mcp\Tools;

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\License;
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

#[Name('checkout_license')]
#[Title('Checkout License')]
#[Description('Check out an available license seat to a user or asset')]
class CheckoutLicenseTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|integer',
            'asset_id' => 'nullable|integer',
            'note' => 'nullable|string|max:65535',
        ]);

        $license = $this->resolveLicense($request);

        if (! $license) {
            return Response::make(Response::error('License not found'));
        }

        if (! Gate::allows('checkout', $license)) {
            return Response::make(Response::error('Unauthorized'));
        }

        if ($license->numRemaining() < 1) {
            return Response::make(Response::error('No available seats for this license'));
        }

        if (! $request->filled('assigned_to') && ! $request->filled('asset_id')) {
            return Response::make(Response::error('Please provide either assigned_to (user ID) or asset_id'));
        }

        $seat = $license->freeSeat();

        if (! $seat) {
            return Response::make(Response::error('No free seat found for this license'));
        }

        $note = $request->get('note');

        if ($request->filled('assigned_to')) {
            $target = User::find($request->get('assigned_to'));
            if (! $target) {
                return Response::make(Response::error('User not found'));
            }
            $seat->assigned_to = $target->id;
            $seat->notes = $note;

            if ($seat->save()) {
                event(new CheckoutableCheckedOut($seat, $target, auth()->user(), $note, [], 1));

                return Response::make(
                    Response::text('License seat checked out to user '.$target->username)
                )->withStructuredContent([
                    'success' => true,
                    'message' => 'License seat checked out successfully',
                    'license_id' => $license->id,
                    'license_name' => $license->name,
                    'seat_id' => $seat->id,
                    'assigned_to_type' => 'user',
                    'assigned_to_id' => $target->id,
                ]);
            }
        } elseif ($request->filled('asset_id')) {
            $target = Asset::find($request->get('asset_id'));
            if (! $target) {
                return Response::make(Response::error('Asset not found'));
            }
            $seat->asset_id = $target->id;
            if ($target->checkedOutToUser()) {
                $seat->assigned_to = $target->assigned_to;
            }
            $seat->notes = $note;

            if ($seat->save()) {
                event(new CheckoutableCheckedOut($seat, $target, auth()->user(), $note, [], 1));

                return Response::make(
                    Response::text('License seat checked out to asset '.$target->asset_tag)
                )->withStructuredContent([
                    'success' => true,
                    'message' => 'License seat checked out successfully',
                    'license_id' => $license->id,
                    'license_name' => $license->name,
                    'seat_id' => $seat->id,
                    'assigned_to_type' => 'asset',
                    'assigned_to_id' => $target->id,
                ]);
            }
        }

        return Response::make(Response::error('Checkout failed'));
    }

    private function resolveLicense(Request $request): ?License
    {
        if ($request->filled('id')) {
            return License::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return License::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the license to check out'),
            'name' => $schema->string()->description('Name of the license to check out'),
            'assigned_to' => $schema->number()->description('User ID to assign the seat to'),
            'asset_id' => $schema->number()->description('Asset ID to assign the seat to'),
            'note' => $schema->string()->description('Optional checkout note'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the checkout succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'license_id' => $schema->number()->description('Numeric ID of the license'),
            'license_name' => $schema->string()->description('Name of the license'),
            'seat_id' => $schema->number()->description('ID of the seat record (use this for checkin)'),
            'assigned_to_type' => $schema->string()->description('Type of entity checked out to: user or asset'),
            'assigned_to_id' => $schema->number()->description('ID of the entity checked out to'),
        ];
    }
}
