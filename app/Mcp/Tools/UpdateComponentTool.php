<?php

namespace App\Mcp\Tools;

use App\Models\Company;
use App\Models\Component;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_component')]
#[Title('Update Component')]
#[Description('Update fields on a Snipe-IT component identified by numeric ID or name')]
class UpdateComponentTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:191',
            'new_name' => 'nullable|string|max:191',
            'category_id' => 'nullable|integer|exists:categories,id',
            'qty' => 'nullable|integer|min:1',
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

        $component = $this->resolveComponent($request);

        if (! $component) {
            return Response::make(Response::error('Component not found'));
        }

        if (! Gate::allows('update', $component)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $updatable = [
            'category_id', 'qty', 'serial', 'model_number', 'manufacturer_id',
            'supplier_id', 'location_id', 'order_number',
            'purchase_cost', 'purchase_date', 'min_amt', 'notes',
        ];

        foreach ($updatable as $field) {
            if ($request->filled($field)) {
                $component->{$field} = $request->get($field);
            }
        }

        if ($request->filled('new_name')) {
            $component->name = $request->get('new_name');
        }

        if ($request->filled('company_id')) {
            $component->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        }

        if ($component->save()) {
            return Response::make(
                Response::text('Component '.$component->name.' updated successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Component updated successfully',
                'id' => $component->id,
                'name' => $component->name,
            ]);
        }

        return Response::make(Response::error('Update failed: '.$component->getErrors()->first()));
    }

    private function resolveComponent(Request $request): ?Component
    {
        if ($request->filled('id')) {
            return Component::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Component::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID to identify the component'),
            'name' => $schema->string()->description('Name to identify the component'),
            'new_name' => $schema->string()->description('New name (renames the component)'),
            'category_id' => $schema->number()->description('Category ID'),
            'qty' => $schema->number()->description('Total quantity in stock'),
            'serial' => $schema->string()->description('Serial number'),
            'model_number' => $schema->string()->description('Model number'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
            'location_id' => $schema->number()->description('Location ID'),
            'company_id' => $schema->number()->description('Company ID'),
            'order_number' => $schema->string()->description('Order number'),
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
            'id' => $schema->number()->description('Numeric ID of the component'),
            'name' => $schema->string()->description('Name of the component'),
        ];
    }
}
