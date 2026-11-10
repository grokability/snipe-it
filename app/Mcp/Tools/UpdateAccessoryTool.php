<?php

namespace App\Mcp\Tools;

use App\Models\Accessory;
use App\Models\Company;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_accessory')]
#[Title('Update Accessory')]
#[Description('Update fields on a Snipe-IT accessory identified by numeric ID or name')]
class UpdateAccessoryTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_name' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
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

        $accessory = $this->resolveAccessory($request);

        if (! $accessory) {
            return Response::make(Response::error('Accessory not found'));
        }

        $updatable = [
            'category_id', 'qty', 'model_number', 'manufacturer_id',
            'supplier_id', 'location_id', 'order_number', 'purchase_cost',
            'purchase_date', 'min_amt', 'requestable', 'notes',
        ];

        foreach ($updatable as $field) {
            if ($request->filled($field)) {
                $accessory->{$field} = $request->get($field);
            }
        }

        if ($request->filled('new_name')) {
            $accessory->name = $request->get('new_name');
        }

        if ($request->filled('company_id')) {
            $accessory->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        }

        if ($accessory->save()) {
            return Response::make(
                Response::text('Accessory '.$accessory->name.' updated successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Accessory updated successfully',
                'id' => $accessory->id,
                'name' => $accessory->name,
            ]);
        }

        return Response::make(Response::error('Update failed: '.$accessory->getErrors()->first()));
    }

    private function resolveAccessory(Request $request): ?Accessory
    {
        if ($request->filled('id')) {
            return Accessory::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Accessory::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID to identify the accessory'),
            'name' => $schema->string()->description('Name to identify the accessory'),
            'new_name' => $schema->string()->description('New name (renames the accessory)'),
            'category_id' => $schema->number()->description('Category ID'),
            'qty' => $schema->number()->description('Total quantity in stock'),
            'model_number' => $schema->string()->description('Model number'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
            'location_id' => $schema->number()->description('Location ID'),
            'company_id' => $schema->number()->description('Company ID'),
            'order_number' => $schema->string()->description('Order number'),
            'purchase_cost' => $schema->number()->description('Purchase cost per unit'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'min_amt' => $schema->number()->description('Minimum quantity alert threshold'),
            'requestable' => $schema->boolean()->description('Whether users can request this accessory'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the accessory'),
            'name' => $schema->string()->description('Name of the accessory'),
        ];
    }
}
