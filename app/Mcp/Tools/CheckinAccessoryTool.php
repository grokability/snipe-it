<?php

namespace App\Mcp\Tools;

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('checkin_accessory')]
#[Title('Checkin Accessory')]
#[Description('Check in a Snipe-IT accessory checkout record by its checkout ID')]
class CheckinAccessoryTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'checkout_id' => 'required|integer',
            'note' => 'nullable|string|max:65535',
        ]);

        $checkout = AccessoryCheckout::find($request->get('checkout_id'));

        if (! $checkout) {
            return Response::make(Response::error('Accessory checkout record not found'));
        }

        $accessory = Accessory::find($checkout->accessory_id);

        if (! $accessory) {
            return Response::make(Response::error('Accessory not found'));
        }

        if (! Gate::allows('checkin', $accessory)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $target = $checkout->assigned_type && $checkout->assigned_to
            ? $checkout->assigned_type::find($checkout->assigned_to)
            : null;

        $accessory->logCheckin($target, $request->get('note'));

        if ($checkout->delete()) {
            return Response::make(
                Response::text('Accessory '.$accessory->name.' checked in successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Accessory checked in successfully',
                'accessory_id' => $accessory->id,
                'accessory_name' => $accessory->name,
            ]);
        }

        return Response::make(Response::error('Checkin failed'));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'checkout_id' => $schema->number()->description('ID of the checkout record to check in (returned by checkout_accessory)'),
            'note' => $schema->string()->description('Optional checkin note'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the checkin succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'accessory_id' => $schema->number()->description('Numeric ID of the accessory'),
            'accessory_name' => $schema->string()->description('Name of the accessory'),
        ];
    }
}
