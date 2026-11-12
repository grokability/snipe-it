<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
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
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', Component::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Resolve name-based FK inputs to numeric IDs before validation
        // so the downstream rules + fill see a consistent shape.
        // Lookups run through the model's normal global scopes, so a
        // non-superuser in a locked-down FMCS install gets a not-found
        // error for a name that only exists in a tenant they cannot
        // see, matching the UI's "that record does not exist" posture.
        $fkMap = [
            ['category_id', 'category_name', Category::class],
            ['manufacturer_id', 'manufacturer_name', Manufacturer::class],
            ['supplier_id', 'supplier_name', Supplier::class],
            ['location_id', 'location_name', Location::class],
            ['company_id', 'company_name', Company::class],
        ];

        $resolvedFks = [];
        foreach ($fkMap as [$idField, $nameField, $modelClass]) {
            $resolved = $this->resolveFkByName($request, $idField, $nameField, $modelClass);
            if ($this->fkResolutionFailed($resolved)) {
                return Response::make(Response::error($this->fkErrorMessage($resolved)));
            }
            if ($resolved !== null) {
                $resolvedFks[$idField] = $resolved;
            }
        }

        // supplier_id and purchase_cost moved from per-row columns to
        // "default_*" templates on the parent, with per-acquisition data
        // on the Orders table. Map legacy input names forward.
        $input = array_merge($request->all(), $resolvedFks);
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
        $component->company_id = Company::getIdForCurrentUser($input['company_id'] ?? null);
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
            'category_id' => $schema->number()->description('Category ID, must be a component category (required). Alternative: category_name.'),
            'category_name' => $schema->string()->description('Category name. Resolved to an ID server-side.'),
            'qty' => $schema->number()->description('Total quantity in stock (required, min 1)'),
            'serial' => $schema->string()->description('Serial number'),
            'model_number' => $schema->string()->description('Model number'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID. Alternative: manufacturer_name.'),
            'manufacturer_name' => $schema->string()->description('Manufacturer name. Resolved to an ID server-side.'),
            'supplier_id' => $schema->number()->description('Supplier ID. Alternative: supplier_name.'),
            'supplier_name' => $schema->string()->description('Supplier name. Resolved to an ID server-side.'),
            'location_id' => $schema->number()->description('Location ID. Alternative: location_name.'),
            'location_name' => $schema->string()->description('Location name. Resolved to an ID server-side.'),
            'company_id' => $schema->number()->description('Company ID (defaults to the authenticated user\'s company). Alternative: company_name.'),
            'company_name' => $schema->string()->description('Company name. Resolved to an ID server-side.'),
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
