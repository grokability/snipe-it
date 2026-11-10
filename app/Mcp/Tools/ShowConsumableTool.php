<?php

namespace App\Mcp\Tools;

use App\Models\Consumable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('show_consumable')]
#[Title('Show Consumable Details')]
#[Description('Look up a single Snipe-IT consumable by numeric ID or name and return its full details')]
class ShowConsumableTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $consumable = null;

        if ($request->filled('id')) {
            $consumable = Consumable::with('company', 'category', 'manufacturer', 'supplier', 'location')->find($request->get('id'));
        } elseif ($request->filled('name')) {
            $consumable = Consumable::with('company', 'category', 'manufacturer', 'supplier', 'location')->where('name', $request->get('name'))->first();
        } else {
            return Response::make(Response::error('Either id or name is required'));
        }

        if (! $consumable) {
            return Response::make(Response::error('Consumable not found'));
        }

        if (! Gate::allows('view', $consumable)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $usersCount = $consumable->users()->count();

        return Response::make(
            Response::text('Consumable '.$consumable->name.' found')
        )->withStructuredContent([
            'id' => $consumable->id,
            'name' => $consumable->name,
            'qty' => $consumable->qty,
            'users_count' => $usersCount,
            'min_amt' => $consumable->min_amt,
            'category_id' => $consumable->category_id,
            'category' => $consumable->category?->name,
            'manufacturer_id' => $consumable->manufacturer_id,
            'manufacturer' => $consumable->manufacturer?->name,
            'company_id' => $consumable->company_id,
            'company' => $consumable->company?->name,
            'location_id' => $consumable->location_id,
            'location' => $consumable->location?->name,
            'purchase_cost' => $consumable->purchase_cost,
            'purchase_date' => $consumable->purchase_date?->format('Y-m-d'),
            'order_number' => $consumable->order_number,
            'model_number' => $consumable->model_number,
            'notes' => $consumable->notes,
            'created_at' => $consumable->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $consumable->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the consumable to look up'),
            'name' => $schema->string()->description('Name of the consumable to look up'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric consumable ID'),
            'name' => $schema->string()->description('Consumable name'),
            'qty' => $schema->number()->description('Total quantity in stock'),
            'users_count' => $schema->number()->description('Number of units checked out'),
            'min_amt' => $schema->number()->description('Minimum quantity alert threshold'),
            'category_id' => $schema->number()->description('Category ID'),
            'category' => $schema->string()->description('Category name'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'manufacturer' => $schema->string()->description('Manufacturer name'),
            'company_id' => $schema->number()->description('Company ID'),
            'company' => $schema->string()->description('Company name'),
            'location_id' => $schema->number()->description('Location ID'),
            'location' => $schema->string()->description('Location name'),
            'purchase_cost' => $schema->string()->description('Purchase cost'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'order_number' => $schema->string()->description('Order number'),
            'model_number' => $schema->string()->description('Model number'),
            'notes' => $schema->string()->description('Notes'),
            'created_at' => $schema->string()->description('Creation timestamp'),
            'updated_at' => $schema->string()->description('Last updated timestamp'),
        ];
    }
}
