<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Consumable;
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

#[Name('create_consumable')]
#[Title('Create Consumable')]
#[Description('Create a new Snipe-IT consumable')]
class CreateConsumableTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', Consumable::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // supplier_id and purchase_cost moved to default_* templates.
        // Per-acquisition data (order_number, purchase_date) lives on
        // the Orders table. Legacy input names map forward.
        $input = $request->all();
        if ($request->filled('supplier_id') && ! $request->filled('default_supplier_id')) {
            $input['default_supplier_id'] = $request->get('supplier_id');
        }
        if ($request->filled('purchase_cost') && ! $request->filled('default_purchase_cost')) {
            $input['default_purchase_cost'] = $request->get('purchase_cost');
        }

        // Model-level rules (Watson) are the single source of truth.
        // Unruled fillable fields pass through via array_merge and are
        // gated by the model's $fillable list at fill() time.
        $consumable = new Consumable;
        try {
            $validated = Validator::make($input, $consumable->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $consumable->fill(array_merge($input, $validated));
        $consumable->created_by = auth()->id();

        if ($consumable->save()) {
            return Response::make(
                Response::text(trans('mcp.consumable_created', ['name' => $consumable->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.consumable_created', ['name' => $consumable->name]),
                'id' => $consumable->id,
                'name' => $consumable->name,
                'qty' => $consumable->qty,
                'category_id' => $consumable->category_id,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $consumable->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Consumable name (required)'),
            'qty' => $schema->number()->description('Total quantity in stock (required)'),
            'category_id' => $schema->number()->description('Category ID, must be a consumable category (required)'),
            'company_id' => $schema->number()->description('Company ID'),
            'location_id' => $schema->number()->description('Location ID'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
            'item_no' => $schema->string()->description('Item number'),
            'order_number' => $schema->string()->description('Order number'),
            'model_number' => $schema->string()->description('Model number'),
            'purchase_cost' => $schema->number()->description('Purchase cost per unit'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'min_amt' => $schema->number()->description('Minimum quantity threshold for alerts'),
            'requestable' => $schema->boolean()->description('Whether users can request this consumable. Mutually exclusive with archived.'),
            'archived' => $schema->boolean()->description('Whether the consumable is archived. Archived consumables are hidden from the default list, cannot be checked out, and are excluded from low-stock alerts. Mutually exclusive with requestable, and cannot be set while outstanding distributions remain.'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the consumable was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new consumable'),
            'name' => $schema->string()->description('Name of the new consumable'),
            'qty' => $schema->number()->description('Total quantity'),
            'category_id' => $schema->number()->description('Category ID'),
        ];
    }
}
