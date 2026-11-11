<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Company;
use App\Models\Component;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        try {
            $request->validate([
                'id' => 'nullable|integer',
                'name' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $component = $this->resolveComponent($request);

        if (! $component || ! Gate::allows('update', $component)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.component')])));
        }

        // supplier_id / purchase_cost forward to default_*, new_name
        // renames via name. Intersect with model rules so partial
        // updates only validate fields the caller actually sent.
        $input = $request->all();
        if ($request->filled('supplier_id') && ! $request->filled('default_supplier_id')) {
            $input['default_supplier_id'] = $request->get('supplier_id');
        }
        if ($request->filled('purchase_cost') && ! $request->filled('default_purchase_cost')) {
            $input['default_purchase_cost'] = $request->get('purchase_cost');
        }
        if ($request->filled('new_name')) {
            $input['name'] = $request->get('new_name');
        }

        $rules = array_intersect_key($component->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $component->fill(array_merge($input, $validated));

        if ($request->filled('company_id')) {
            $component->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        }

        if ($component->save()) {
            return Response::make(
                Response::text(trans('mcp.component_updated', ['name' => $component->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.component_updated', ['name' => $component->name]),
                'id' => $component->id,
                'name' => $component->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $component->getErrors()->first()])));
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
            'archived' => $schema->boolean()->description('Set true to archive, false to unarchive. Archived components cannot be checked out and are hidden from the default list. Cannot be archived while units are still checked out.'),
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
