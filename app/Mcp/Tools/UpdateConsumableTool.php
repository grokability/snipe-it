<?php

namespace App\Mcp\Tools;

use App\Models\Consumable;
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

#[Name('update_consumable')]
#[Title('Update Consumable')]
#[Description('Update fields on a Snipe-IT consumable identified by numeric ID or name')]
class UpdateConsumableTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        try {
            $request->validate([
                'id' => 'nullable|integer',
                'name' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $consumable = $this->resolveConsumable($request);

        if (! $consumable) {
            return Response::make(Response::error(trans('mcp.consumable_not_found')));
        }

        if (! Gate::allows('update', $consumable)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // supplier_id / purchase_cost map forward to default_*.
        // purchase_date moved to the Orders table.
        $updatable = [
            'qty', 'category_id', 'company_id', 'location_id', 'manufacturer_id',
            'min_amt', 'notes',
            'default_supplier_id', 'default_purchase_cost',
        ];

        foreach ($updatable as $field) {
            if ($request->filled($field)) {
                $consumable->{$field} = $request->get($field);
            }
        }

        if ($request->filled('supplier_id') && ! $request->filled('default_supplier_id')) {
            $consumable->default_supplier_id = $request->get('supplier_id');
        }
        if ($request->filled('purchase_cost') && ! $request->filled('default_purchase_cost')) {
            $consumable->default_purchase_cost = $request->get('purchase_cost');
        }

        if ($request->filled('new_name')) {
            $consumable->name = $request->get('new_name');
        }

        if ($consumable->save()) {
            return Response::make(
                Response::text(trans('mcp.consumable_updated', ['name' => $consumable->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.consumable_updated', ['name' => $consumable->name]),
                'id' => $consumable->id,
                'name' => $consumable->name,
                'qty' => $consumable->qty,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $consumable->getErrors()->first()])));
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
            'id' => $schema->number()->description('Numeric ID to identify the consumable'),
            'name' => $schema->string()->description('Name to identify the consumable'),
            'new_name' => $schema->string()->description('New name (renames the consumable)'),
            'qty' => $schema->number()->description('Total quantity in stock'),
            'category_id' => $schema->number()->description('Category ID'),
            'company_id' => $schema->number()->description('Company ID'),
            'location_id' => $schema->number()->description('Location ID'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
            'purchase_cost' => $schema->number()->description('Purchase cost per unit'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'min_amt' => $schema->number()->description('Minimum quantity alert threshold'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the consumable'),
            'name' => $schema->string()->description('Name of the consumable'),
            'qty' => $schema->number()->description('Total quantity'),
        ];
    }
}
