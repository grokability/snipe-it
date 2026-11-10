<?php

namespace App\Mcp\Tools;

use App\Models\Company;
use App\Models\Component;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_component')]
#[Title('Create Component')]
#[Description('Create a new Snipe-IT component')]
class CreateComponentTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        try {
            $request->validate([
                'name' => 'required|string|max:191',
                'category_id' => 'required|integer|exists:categories,id',
                'qty' => 'required|integer|min:1',
                'serial' => 'nullable|string|max:255',
                'model_number' => 'nullable|string|max:255',
                'manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
                'supplier_id' => 'nullable|integer|exists:suppliers,id',
                'location_id' => 'nullable|integer|exists:locations,id',
                'company_id' => 'nullable|integer|exists:companies,id',
                'order_number' => 'nullable|string|max:255',
                'purchase_cost' => 'nullable|numeric|min:0',
                'purchase_date' => 'nullable|date_format:Y-m-d',
                'min_amt' => 'nullable|integer|min:0',
                'notes' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $component = new Component;
        $component->fill($request->only([
            'name', 'category_id', 'qty', 'serial', 'model_number',
            'manufacturer_id', 'supplier_id', 'location_id',
            'order_number', 'purchase_cost', 'purchase_date', 'min_amt', 'notes',
        ]));

        $component->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        $component->created_by = auth()->id();

        if ($component->save()) {
            return Response::make(
                Response::text('Component '.$component->name.' created successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Component created successfully',
                'id' => $component->id,
                'name' => $component->name,
                'qty' => $component->qty,
                'category_id' => $component->category_id,
            ]);
        }

        return Response::make(Response::error('Create failed: '.$component->getErrors()->first()));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Component name (required)'),
            'category_id' => $schema->number()->description('Category ID — must be a component category (required)'),
            'qty' => $schema->number()->description('Total quantity in stock (required, min 1)'),
            'serial' => $schema->string()->description('Serial number'),
            'model_number' => $schema->string()->description('Model number'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
            'location_id' => $schema->number()->description('Location ID'),
            'company_id' => $schema->number()->description('Company ID (defaults to the authenticated user\'s company)'),
            'order_number' => $schema->string()->description('Order number'),
            'purchase_cost' => $schema->number()->description('Purchase cost per unit'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'min_amt' => $schema->number()->description('Minimum quantity threshold for alerts'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the component was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new component'),
            'name' => $schema->string()->description('Name of the new component'),
            'qty' => $schema->number()->description('Total quantity'),
            'category_id' => $schema->number()->description('Category ID'),
        ];
    }
}
