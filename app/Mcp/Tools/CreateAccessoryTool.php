<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Accessory;
use App\Models\Category;
use App\Models\Company;
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

#[Name('create_accessory')]
#[Title('Create Accessory')]
#[Description('Create a new Snipe-IT accessory')]
class CreateAccessoryTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', Accessory::class)) {
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
        // living on the Orders table. Map legacy input names forward so
        // older callers that still send supplier_id / purchase_cost keep
        // working.
        $input = array_merge($request->all(), $resolvedFks);
        if ($request->filled('supplier_id') && ! $request->filled('default_supplier_id')) {
            $input['default_supplier_id'] = $request->get('supplier_id');
        }
        if ($request->filled('purchase_cost') && ! $request->filled('default_purchase_cost')) {
            $input['default_purchase_cost'] = $request->get('purchase_cost');
        }

        // Model-level rules are the single source of truth (Watson).
        // Not every fillable field has a rule (e.g. notes, model_number
        // carry no constraints), so we overlay validated values on top
        // of the raw input and let the model's $fillable list gate what
        // fill() persists. Validation still catches bad values for
        // every field that does have a rule.
        $accessory = new Accessory;
        try {
            $validated = Validator::make($input, $accessory->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $accessory->fill(array_merge($input, $validated));
        $accessory->company_id = Company::getIdForCurrentUser($input['company_id'] ?? null);
        $accessory->created_by = auth()->id();

        if ($accessory->save()) {
            return Response::make(
                Response::text(trans('mcp.accessory_created', ['name' => $accessory->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.accessory_created', ['name' => $accessory->name]),
                'id' => $accessory->id,
                'name' => $accessory->name,
                'qty' => $accessory->qty,
                'category_id' => $accessory->category_id,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $accessory->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Accessory name (required)'),
            'category_id' => $schema->number()->description('Category ID, must be an accessory category (required). Alternative: category_name.'),
            'category_name' => $schema->string()->description('Category name. Resolved to an ID server-side.'),
            'qty' => $schema->number()->description('Total quantity in stock'),
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
            'requestable' => $schema->boolean()->description('Whether users can request this accessory. Mutually exclusive with archived.'),
            'archived' => $schema->boolean()->description('Whether the accessory is archived. Archived accessories are hidden from the default list, cannot be checked out, and are excluded from low-stock alerts. Mutually exclusive with requestable, and cannot be set while units are still checked out.'),
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
