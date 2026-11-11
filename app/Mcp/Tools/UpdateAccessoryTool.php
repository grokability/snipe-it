<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Accessory;
use App\Models\Company;
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

        // Build input with aliases mapped: supplier_id / purchase_cost
        // forward to default_*, new_name renames via name. Then intersect
        // with model rules so partial updates only validate fields the
        // caller actually sent (no spurious "required" failures).
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

        $rules = array_intersect_key($accessory->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $accessory->fill(array_merge($input, $validated));

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
