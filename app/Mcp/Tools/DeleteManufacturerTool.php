<?php

namespace App\Mcp\Tools;

use App\Models\Manufacturer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_manufacturer')]
#[Title('Delete Manufacturer')]
#[Description('Soft-delete a Snipe-IT manufacturer identified by numeric ID or name')]
class DeleteManufacturerTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $manufacturer = $this->resolveManufacturer($request);

        if (! $manufacturer) {
            return Response::make(Response::error('Manufacturer not found'));
        }

        if (! Gate::allows('delete', $manufacturer)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $name = $manufacturer->name;

        $manufacturer->delete();

        return Response::make(
            Response::text('Manufacturer '.$name.' deleted successfully')
        )->withStructuredContent([
            'success' => true,
            'message' => 'Manufacturer deleted successfully',
            'name' => $name,
        ]);
    }

    private function resolveManufacturer(Request $request): ?Manufacturer
    {
        if ($request->filled('id')) {
            return Manufacturer::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Manufacturer::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the manufacturer to delete'),
            'name' => $schema->string()->description('Name of the manufacturer to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted manufacturer'),
        ];
    }
}
