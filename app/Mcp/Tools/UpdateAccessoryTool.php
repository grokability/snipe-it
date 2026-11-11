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

#[Name('update_accessory')]
#[Title('Update Accessory')]
#[Description('Update fields on a Snipe-IT accessory identified by numeric ID or name')]
class UpdateAccessoryTool extends Tool
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

        $accessory = $this->resolveAccessory($request);

        if (! $accessory) {
            return Response::make(Response::error(trans('mcp.accessory_not_found')));
        }

        if (! Gate::allows('update', $accessory)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // supplier_id / purchase_cost map forward to default_*.
        // order_number / purchase_date have moved to the Orders table.
        $updatable = [
            'category_id', 'qty', 'model_number', 'manufacturer_id',
            'location_id', 'min_amt', 'requestable', 'notes',
            'default_supplier_id', 'default_purchase_cost',
        ];

        foreach ($updatable as $field) {
            if ($request->filled($field)) {
                $accessory->{$field} = $request->get($field);
            }
        }

        if ($request->filled('supplier_id') && ! $request->filled('default_supplier_id')) {
            $accessory->default_supplier_id = $request->get('supplier_id');
        }
        if ($request->filled('purchase_cost') && ! $request->filled('default_purchase_cost')) {
            $accessory->default_purchase_cost = $request->get('purchase_cost');
        }

        if ($request->filled('new_name')) {
            $accessory->name = $request->get('new_name');
        }

        if ($request->filled('company_id')) {
            $accessory->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        }

        if ($accessory->save()) {
            return Response::make(
                Response::text(trans('mcp.accessory_updated', ['name' => $accessory->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.accessory_updated', ['name' => $accessory->name]),
                'id' => $accessory->id,
                'name' => $accessory->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $accessory->getErrors()->first()])));
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
