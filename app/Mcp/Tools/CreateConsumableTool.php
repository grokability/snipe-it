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

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'qty' => 'required|integer|min:0',
                'category_id' => 'required|integer|exists:categories,id',
                'company_id' => 'nullable|integer',
                'location_id' => 'nullable|integer|exists:locations,id',
                'manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
                'supplier_id' => 'nullable|integer|exists:suppliers,id',
                'item_no' => 'nullable|string|max:255',
                'order_number' => 'nullable|string|max:255',
                'model_number' => 'nullable|string|max:255',
                'purchase_cost' => 'nullable|numeric|min:0',
                'purchase_date' => 'nullable|date_format:Y-m-d',
                'min_amt' => 'nullable|integer|min:0',
                'requestable' => 'nullable|boolean',
                'notes' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $consumable = new Consumable;
        $consumable->fill($request->only([
            'name', 'qty', 'category_id', 'company_id', 'location_id', 'manufacturer_id',
            'supplier_id', 'item_no', 'order_number', 'model_number', 'purchase_cost',
            'purchase_date', 'min_amt', 'requestable', 'notes',
        ]));
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
            'category_id' => $schema->number()->description('Category ID — must be a consumable category (required)'),
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
