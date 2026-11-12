<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
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

#[Name('update_consumable')]
#[Title('Update Consumable')]
#[Description('Update fields on a Snipe-IT consumable identified by numeric ID or name')]
class UpdateConsumableTool extends Tool
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

        $consumable = $this->resolveConsumable($request);

        if (! $consumable || ! Gate::allows('update', $consumable)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.consumable')])));
        }

        $fkMap = [
            ['category_id', 'category_name', Category::class],
            ['company_id', 'company_name', Company::class],
            ['location_id', 'location_name', Location::class],
            ['manufacturer_id', 'manufacturer_name', Manufacturer::class],
            ['supplier_id', 'supplier_name', Supplier::class],
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

        // supplier_id / purchase_cost forward to default_*, new_name
        // renames via name. Intersect with model rules so partial
        // updates only validate fields the caller actually sent.
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

        $rules = array_intersect_key($consumable->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $consumable->fill(array_merge($input, $validated));

        if ($consumable->save()) {
            return Response::make(
                Response::text(trans('mcp.consumable_updated', ['name' => $consumable->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.consumable_updated', ['name' => $consumable->name]),
                'id' => $consumable->id,
                'name' => $consumable->name,
                'qty' => $consumable->qty,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $consumable->getErrors()->first()])));
    }

    private function resolveConsumable(Request $request): ?Consumable
    {
        if ($request->filled('id')) {
            return Consumable::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Consumable::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID to identify the consumable'),
            'name' => $schema->string()->description('Name to identify the consumable'),
            'new_name' => $schema->string()->description('New name (renames the consumable)'),
            'qty' => $schema->number()->description('Total quantity in stock'),
            'category_id' => $schema->number()->description('Category ID. Alternative: category_name.'),
            'category_name' => $schema->string()->description('Category name. Resolved to an ID server-side.'),
            'company_id' => $schema->number()->description('Company ID. Alternative: company_name.'),
            'company_name' => $schema->string()->description('Company name. Resolved to an ID server-side.'),
            'location_id' => $schema->number()->description('Location ID. Alternative: location_name.'),
            'location_name' => $schema->string()->description('Location name. Resolved to an ID server-side.'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID. Alternative: manufacturer_name.'),
            'manufacturer_name' => $schema->string()->description('Manufacturer name. Resolved to an ID server-side.'),
            'supplier_id' => $schema->number()->description('Supplier ID. Alternative: supplier_name.'),
            'supplier_name' => $schema->string()->description('Supplier name. Resolved to an ID server-side.'),
            'purchase_cost' => $schema->number()->description('Purchase cost per unit'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'min_amt' => $schema->number()->description('Minimum quantity alert threshold'),
            'requestable' => $schema->boolean()->description('Whether users can request this consumable. Mutually exclusive with archived.'),
            'archived' => $schema->boolean()->description('Set true to archive, false to unarchive. Archived consumables cannot be checked out and are hidden from the default list. Cannot be archived while outstanding distributions remain.'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the consumable'),
            'name' => $schema->string()->description('Name of the consumable'),
            'qty' => $schema->number()->description('Total quantity'),
        ];
    }
}
