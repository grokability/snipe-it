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

#[Name('delete_supplier')]
#[Title('Delete Supplier')]
#[Description('Soft-delete a Snipe-IT supplier identified by numeric ID or name')]
class DeleteSupplierTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $supplier = $this->resolveSupplier($request);

        if (! $supplier) {
            return Response::make(Response::error('Supplier not found'));
        }

        if (! Gate::allows('delete', $supplier)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $name = $supplier->name;

        $supplier->delete();

        return Response::make(
            Response::text('Supplier '.$name.' deleted successfully')
        )->withStructuredContent([
            'success' => true,
            'message' => 'Supplier deleted successfully',
            'name' => $name,
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
            'id' => $schema->number()->description('Numeric ID of the supplier to delete'),
            'name' => $schema->string()->description('Name of the supplier to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted supplier'),
        ];
    }
}
