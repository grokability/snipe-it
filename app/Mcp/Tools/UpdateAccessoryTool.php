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

#[Name('update_accessory')]
#[Title('Update Accessory')]
#[Description('Update fields on a Snipe-IT accessory identified by numeric ID or name')]
class UpdateAccessoryTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

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

        $accessory = $this->resolveAccessory($request);

        if (! $accessory || ! Gate::allows('update', $accessory)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.accessory')])));
        }

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

        // Build input with aliases mapped: supplier_id / purchase_cost
        // forward to default_*, new_name renames via name. Then intersect
        // with model rules so partial updates only validate fields the
        // caller actually sent (no spurious "required" failures).
        $input = array_merge($request->all(), $resolvedFks);
        if (! empty($input['supplier_id']) && ! $request->filled('default_supplier_id')) {
            $input['default_supplier_id'] = $input['supplier_id'];
        }
        if ($request->filled('purchase_cost') && ! $request->filled('default_purchase_cost')) {
            $input['default_purchase_cost'] = $request->get('purchase_cost');
        }
        if ($request->filled('new_name')) {
            $input['name'] = $request->get('new_name');
        }

        $rules = array_intersect_key($accessory->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $accessory->fill(array_merge($input, $validated));

        if (! empty($input['company_id'])) {
            $accessory->company_id = Company::getIdForCurrentUser($input['company_id']);
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
            'category_id' => $schema->number()->description('Category ID. Alternative: category_name.'),
            'category_name' => $schema->string()->description('Category name. Resolved to an ID server-side.'),
            'qty' => $schema->number()->description('Total quantity in stock'),
            'model_number' => $schema->string()->description('Model number'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID. Alternative: manufacturer_name.'),
            'manufacturer_name' => $schema->string()->description('Manufacturer name. Resolved to an ID server-side.'),
            'supplier_id' => $schema->number()->description('Supplier ID. Alternative: supplier_name.'),
            'supplier_name' => $schema->string()->description('Supplier name. Resolved to an ID server-side.'),
            'location_id' => $schema->number()->description('Location ID. Alternative: location_name.'),
            'location_name' => $schema->string()->description('Location name. Resolved to an ID server-side.'),
            'company_id' => $schema->number()->description('Company ID. Alternative: company_name.'),
            'company_name' => $schema->string()->description('Company name. Resolved to an ID server-side.'),
            'order_number' => $schema->string()->description('Order number'),
            'purchase_cost' => $schema->number()->description('Purchase cost per unit'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'min_amt' => $schema->number()->description('Minimum quantity alert threshold'),
            'requestable' => $schema->boolean()->description('Whether users can request this accessory. Mutually exclusive with archived.'),
            'archived' => $schema->boolean()->description('Set true to archive, false to unarchive. Archived accessories cannot be checked out and are hidden from the default list. Cannot be archived while units are still checked out.'),
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
