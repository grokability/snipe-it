<?php

namespace App\Mcp\Tools;

use App\Models\Accessory;
use App\Models\Company;
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

#[Name('create_accessory')]
#[Title('Create Accessory')]
#[Description('Create a new Snipe-IT accessory')]
class CreateAccessoryTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', Accessory::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|integer|exists:categories,id',
                'qty' => 'nullable|integer|min:0',
                'model_number' => 'nullable|string|max:255',
                'manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
                'supplier_id' => 'nullable|integer|exists:suppliers,id',
                'location_id' => 'nullable|integer|exists:locations,id',
                'company_id' => 'nullable|integer|exists:companies,id',
                'order_number' => 'nullable|string|max:255',
                'purchase_cost' => 'nullable|numeric|min:0',
                'purchase_date' => 'nullable|date_format:Y-m-d',
                'min_amt' => 'nullable|integer|min:0',
                'requestable' => 'nullable|boolean',
                'notes' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $accessory = new Accessory;
        $accessory->fill($request->only([
            'name', 'category_id', 'qty', 'model_number', 'manufacturer_id',
            'supplier_id', 'location_id', 'order_number', 'purchase_cost',
            'purchase_date', 'min_amt', 'requestable', 'notes',
        ]));

        $accessory->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        $accessory->created_by = auth()->id();

        if ($accessory->save()) {
            return Response::make(
                Response::text('Accessory '.$accessory->name.' created successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Accessory created successfully',
                'id' => $accessory->id,
                'name' => $accessory->name,
                'qty' => $accessory->qty,
                'category_id' => $accessory->category_id,
            ]);
        }

        return Response::make(Response::error('Create failed: '.$accessory->getErrors()->first()));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Accessory name (required)'),
            'category_id' => $schema->number()->description('Category ID — must be an accessory category (required)'),
            'qty' => $schema->number()->description('Total quantity in stock'),
            'model_number' => $schema->string()->description('Model number'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
            'location_id' => $schema->number()->description('Location ID'),
            'company_id' => $schema->number()->description('Company ID (defaults to the authenticated user\'s company)'),
            'order_number' => $schema->string()->description('Order number'),
            'purchase_cost' => $schema->number()->description('Purchase cost per unit'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'min_amt' => $schema->number()->description('Minimum quantity threshold for alerts'),
            'requestable' => $schema->boolean()->description('Whether users can request this accessory'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the accessory was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new accessory'),
            'name' => $schema->string()->description('Name of the new accessory'),
            'qty' => $schema->number()->description('Total quantity'),
            'category_id' => $schema->number()->description('Category ID'),
        ];
    }
}
