<?php

namespace App\Mcp\Tools;

use App\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('show_supplier')]
#[Title('Show Supplier')]
#[Description('Show details of a Snipe-IT supplier identified by numeric ID or name')]
class ShowSupplierTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $supplier = $this->resolveSupplier($request);

        if (! $supplier) {
            if (! $request->filled('id') && ! $request->filled('name')) {
                return Response::make(Response::error(trans('mcp.id_or_name_required')));
            }

            return Response::make(Response::error(trans('mcp.supplier_not_found')));
        }

        if (! Gate::allows('view', $supplier)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $supplier->loadCount('assets as assets_count', 'licenses as licenses_count');

        return Response::make(
            Response::text(trans('mcp.supplier_found', ['name' => $supplier->name]))
        )->withStructuredContent([
            'id' => $supplier->id,
            'name' => $supplier->name,
            'address' => $supplier->address,
            'address2' => $supplier->address2,
            'city' => $supplier->city,
            'state' => $supplier->state,
            'country' => $supplier->country,
            'zip' => $supplier->zip,
            'phone' => $supplier->phone,
            'fax' => $supplier->fax,
            'email' => $supplier->email,
            'url' => $supplier->url,
            'contact' => $supplier->contact,
            'notes' => $supplier->notes,
            'assets_count' => $supplier->assets_count,
            'licenses_count' => $supplier->licenses_count,
            'created_at' => $supplier->created_at?->toISOString(),
            'updated_at' => $supplier->updated_at?->toISOString(),
        ]);
    }

    private function resolveSupplier(Request $request): ?Supplier
    {
        if ($request->filled('id')) {
            return Supplier::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Supplier::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the supplier to show'),
            'name' => $schema->string()->description('Name of the supplier to show'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the supplier'),
            'name' => $schema->string()->description('Supplier name')->required(),
            'address' => $schema->string()->description('Address line 1'),
            'address2' => $schema->string()->description('Address line 2'),
            'city' => $schema->string()->description('City'),
            'state' => $schema->string()->description('State'),
            'country' => $schema->string()->description('Country'),
            'zip' => $schema->string()->description('Postal code'),
            'phone' => $schema->string()->description('Phone number'),
            'fax' => $schema->string()->description('Fax number'),
            'email' => $schema->string()->description('Email address'),
            'url' => $schema->string()->description('Website URL'),
            'contact' => $schema->string()->description('Contact name'),
            'notes' => $schema->string()->description('Notes'),
            'assets_count' => $schema->number()->description('Number of assets from this supplier'),
            'licenses_count' => $schema->number()->description('Number of licenses from this supplier'),
            'created_at' => $schema->string()->description('Creation timestamp'),
            'updated_at' => $schema->string()->description('Last update timestamp'),
        ];
    }
}
