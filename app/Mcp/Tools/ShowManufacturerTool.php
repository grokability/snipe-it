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

#[Name('show_manufacturer')]
#[Title('Show Manufacturer')]
#[Description('Show details of a Snipe-IT manufacturer identified by numeric ID or name')]
class ShowManufacturerTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $manufacturer = $this->resolveManufacturer($request);

        if (! $manufacturer) {
            if (! $request->filled('id') && ! $request->filled('name')) {
                return Response::make(Response::error('Please provide an id or name'));
            }

            return Response::make(Response::error('Manufacturer not found'));
        }

        if (! Gate::allows('view', $manufacturer)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $manufacturer->loadCount('assets as assets_count');

        return Response::make(
            Response::text('Manufacturer: '.$manufacturer->name)
        )->withStructuredContent([
            'id' => $manufacturer->id,
            'name' => $manufacturer->name,
            'url' => $manufacturer->url,
            'support_url' => $manufacturer->support_url,
            'support_email' => $manufacturer->support_email,
            'support_phone' => $manufacturer->support_phone,
            'warranty_lookup_url' => $manufacturer->warranty_lookup_url,
            'assets_count' => $manufacturer->assets_count,
            'notes' => $manufacturer->notes,
            'created_at' => $manufacturer->created_at?->toISOString(),
            'updated_at' => $manufacturer->updated_at?->toISOString(),
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
            'id' => $schema->number()->description('Numeric ID of the manufacturer to show'),
            'name' => $schema->string()->description('Name of the manufacturer to show'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the manufacturer'),
            'name' => $schema->string()->description('Manufacturer name')->required(),
            'url' => $schema->string()->description('Manufacturer website URL'),
            'support_url' => $schema->string()->description('Support website URL'),
            'support_email' => $schema->string()->description('Support email address'),
            'support_phone' => $schema->string()->description('Support phone number'),
            'warranty_lookup_url' => $schema->string()->description('Warranty lookup URL'),
            'assets_count' => $schema->number()->description('Number of assets from this manufacturer'),
            'notes' => $schema->string()->description('Notes'),
            'created_at' => $schema->string()->description('Creation timestamp'),
            'updated_at' => $schema->string()->description('Last update timestamp'),
        ];
    }
}
