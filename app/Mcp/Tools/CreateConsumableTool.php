<?php

namespace App\Mcp\Tools;

use App\Models\Consumable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_consumable')]
#[Title('Create Consumable')]
#[Description('Create a new Snipe-IT consumable')]
class CreateConsumableTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', Consumable::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // supplier_id and purchase_cost moved to default_* templates.
        // Per-acquisition data (order_number, purchase_date) lives on
        // the Orders table. Legacy input names map forward.
        $input = $request->only([
            'name', 'qty', 'category_id', 'company_id', 'location_id', 'manufacturer_id',
            'item_no', 'model_number', 'min_amt', 'requestable', 'notes',
            'default_supplier_id', 'default_purchase_cost',
        ]);
        if ($request->filled('supplier_id') && ! isset($input['default_supplier_id'])) {
            $input['default_supplier_id'] = $request->get('supplier_id');
        }
        if ($request->filled('purchase_cost') && ! isset($input['default_purchase_cost'])) {
            $input['default_purchase_cost'] = $request->get('purchase_cost');
        }

        $consumable = new Consumable;
        $consumable->fill($input);
        $consumable->created_by = auth()->id();

        if ($consumable->save()) {
            return Response::make(
                Response::text(trans('mcp.consumable_created', ['name' => $consumable->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.consumable_created', ['name' => $consumable->name]),
                'id' => $consumable->id,
                'name' => $consumable->name,
                'qty' => $consumable->qty,
                'category_id' => $consumable->category_id,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $consumable->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Consumable name (required)'),
            'qty' => $schema->number()->description('Total quantity in stock (required)'),
            'category_id' => $schema->number()->description('Category ID, must be a consumable category (required)'),
            'company_id' => $schema->number()->description('Company ID'),
            'location_id' => $schema->number()->description('Location ID'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
            'item_no' => $schema->string()->description('Item number'),
            'order_number' => $schema->string()->description('Order number'),
            'model_number' => $schema->string()->description('Model number'),
            'purchase_cost' => $schema->number()->description('Purchase cost per unit'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'min_amt' => $schema->number()->description('Minimum quantity threshold for alerts'),
            'requestable' => $schema->boolean()->description('Whether users can request this consumable'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the consumable was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new consumable'),
            'name' => $schema->string()->description('Name of the new consumable'),
            'qty' => $schema->number()->description('Total quantity'),
            'category_id' => $schema->number()->description('Category ID'),
        ];
    }
}
