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

#[Name('update_supplier')]
#[Title('Update Supplier')]
#[Description('Update fields on a Snipe-IT supplier identified by numeric ID or name')]
class UpdateSupplierTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'zip' => 'nullable|string',
            'phone' => 'nullable|string',
            'fax' => 'nullable|string',
            'email' => 'nullable|email',
            'url' => 'nullable|string',
            'contact' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $supplier = $this->resolveSupplier($request);

        if (! $supplier) {
            return Response::make(Response::error(trans('mcp.supplier_not_found')));
        }

        if (! Gate::allows('update', $supplier)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if ($request->filled('new_name')) {
            $supplier->name = $request->get('new_name');
        }

        $updatable = ['address', 'address2', 'city', 'state', 'country', 'zip', 'phone', 'fax', 'email', 'url', 'contact', 'notes'];

        foreach ($updatable as $field) {
            if ($request->filled($field)) {
                $supplier->{$field} = $request->get($field);
            }
        }

        if ($supplier->save()) {
            return Response::make(
                Response::text(trans('mcp.supplier_updated', ['name' => $supplier->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.supplier_updated', ['name' => $supplier->name]),
                'id' => $supplier->id,
                'name' => $supplier->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $supplier->getErrors()->first()])));
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
            'id' => $schema->number()->description('Numeric ID to identify the supplier'),
            'name' => $schema->string()->description('Name to identify the supplier'),
            'new_name' => $schema->string()->description('New name (renames the supplier)'),
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
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the supplier'),
            'name' => $schema->string()->description('Name of the supplier'),
        ];
    }
}
