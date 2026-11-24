<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\SnipeMcpTool;
use App\Models\Actionlog;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('mark_consumable_outstanding_consumed')]
#[Title('Mark Consumable Outstanding Consumed')]
#[Description('Mark every outstanding distribution of a Snipe-IT consumable as consumed in one call. Mirrors the show-page bulk callout and the bulk-edit checkbox.')]
class MarkConsumableOutstandingConsumedTool extends Tool
{
    use SnipeMcpTool;

    public function handle(Request $request): ResponseFactory
    {
        // Same scope as checkout and mark-consumed row actions.
        // Closing an outstanding distribution is a checkout-lifecycle
        // operation, not an edit on the parent consumable.
        if ($denied = $this->scopeDenied(McpScope::Checkout)) {
            return $denied;
        }

        try {
            $request->validate([
                'id' => 'nullable|integer',
                'name' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $consumable = $this->resolveConsumable($request);

        if (!$consumable || !Gate::allows('checkout', $consumable)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.consumable')])));
        }
        
        $stamped = ConsumableAssignment::where('consumable_id', $consumable->id)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        if ($stamped === 0) {
            return $this->structuredResponse(
                trans('mcp.consumable_no_outstanding_to_consume', ['name' => $consumable->name]),
                [
                    'success' => true,
                    'message' => trans('mcp.consumable_no_outstanding_to_consume', ['name' => $consumable->name]),
                    'consumable_id' => $consumable->id,
                    'consumable_name' => $consumable->name,
                    'consumed_count' => 0,
                ]
            );
        }

        $log = new Actionlog;
        $log->item_type = Consumable::class;
        $log->item_id = $consumable->id;
        $log->created_by = auth()->id();
        $log->quantity = $stamped;
        $log->logaction('consumed');

        return $this->structuredResponse(
            trans('mcp.consumable_outstanding_consumed', ['name' => $consumable->name, 'count' => $stamped]),
            [
                'success' => true,
                'message' => trans('mcp.consumable_outstanding_consumed', ['name' => $consumable->name, 'count' => $stamped]),
                'consumable_id' => $consumable->id,
                'consumable_name' => $consumable->name,
                'consumed_count' => $stamped,
            ]
        );
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
            'id' => $schema->number()->description('Numeric ID of the consumable (either id or name required)'),
            'name' => $schema->string()->description('Name of the consumable (either id or name required)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the operation succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'consumable_id' => $schema->number()->description('Numeric ID of the consumable'),
            'consumable_name' => $schema->string()->description('Name of the consumable'),
            'consumed_count' => $schema->number()->description('Number of distributions that were marked consumed by this call'),
        ];
    }
}
