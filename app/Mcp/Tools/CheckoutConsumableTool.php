<?php

namespace App\Mcp\Tools;

use App\Events\CheckoutableCheckedOut;
use App\Models\Consumable;
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

#[Name('checkout_consumable')]
#[Title('Checkout Consumable')]
#[Description('Check out a Snipe-IT consumable to a user')]
class CheckoutConsumableTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'assigned_to' => 'required|integer',
            'note' => 'nullable|string|max:65535',
        ]);

        $consumable = $this->resolveConsumable($request);

        if (! $consumable) {
            return Response::make(Response::error(trans('mcp.consumable_not_found')));
        }

        if (! Gate::allows('checkout', $consumable)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if ($consumable->numRemaining() <= 0) {
            return Response::make(Response::error(trans('mcp.no_units_remaining')));
        }

        $user = User::find($request->get('assigned_to'));

        if (! $user) {
            return Response::make(Response::error(trans('mcp.user_not_found')));
        }

        $consumable->users()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'created_by' => auth()->id(),
            'assigned_to' => $user->id,
            'note' => $request->get('note'),
        ]);

        event(new CheckoutableCheckedOut(
            $consumable,
            $user,
            auth()->user(),
            $request->get('note'),
            [],
            1,
        ));

        return Response::make(
            Response::text(trans('mcp.consumable_checked_out', ['name' => $consumable->name, 'username' => $user->username]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.consumable_checked_out', ['name' => $consumable->name, 'username' => $user->username]),
            'consumable_id' => $consumable->id,
            'consumable_name' => $consumable->name,
            'assigned_to_id' => $user->id,
            'assigned_to_username' => $user->username,
        ]);
    }

    private function resolveConsumable(Request $request): ?Consumable
    {
        if ($request->filled('id')) {
            return Consumable::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Consumable::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the consumable to check out'),
            'name' => $schema->string()->description('Name of the consumable to check out'),
            'assigned_to' => $schema->number()->description('User ID to check out to (required)'),
            'note' => $schema->string()->description('Optional checkout note'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the checkout succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'consumable_id' => $schema->number()->description('Numeric ID of the consumable'),
            'consumable_name' => $schema->string()->description('Name of the consumable'),
            'assigned_to_id' => $schema->number()->description('ID of the user the consumable was checked out to'),
            'assigned_to_username' => $schema->string()->description('Username of the user the consumable was checked out to'),
        ];
    }
}
