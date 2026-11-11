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

#[Name('create_component')]
#[Title('Create Component')]
#[Description('Create a new Snipe-IT component')]
class CreateComponentTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', Component::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // supplier_id and purchase_cost moved from per-row columns to
        // "default_*" templates on the parent, with per-acquisition data
        // on the Orders table. Map legacy input names forward.
        $input = $request->all();
        if ($request->filled('supplier_id') && ! $request->filled('default_supplier_id')) {
            $input['default_supplier_id'] = $request->get('supplier_id');
        }
        if ($request->filled('purchase_cost') && ! $request->filled('default_purchase_cost')) {
            $input['default_purchase_cost'] = $request->get('purchase_cost');
        }

        // Model-level rules (Watson) are the single source of truth.
        // Fields in $fillable without an explicit rule (notes,
        // model_number, etc.) pass through via the array_merge, and
        // $fillable gates what fill() persists.
        $component = new Component;
        try {
            $validated = Validator::make($input, $component->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $component->fill(array_merge($input, $validated));
        $component->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        $component->created_by = auth()->id();

        if ($component->save()) {
            return Response::make(
                Response::text(trans('mcp.component_created', ['name' => $component->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.component_created', ['name' => $component->name]),
                'id' => $component->id,
                'name' => $component->name,
                'qty' => $component->qty,
                'category_id' => $component->category_id,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $component->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Component name (required)'),
            'category_id' => $schema->number()->description('Category ID, must be a component category (required)'),
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
            'archived' => $schema->boolean()->description('Whether the component is archived. Archived components are hidden from the default list, cannot be checked out, and are excluded from low-stock alerts. Cannot be set while units are still checked out.'),
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
