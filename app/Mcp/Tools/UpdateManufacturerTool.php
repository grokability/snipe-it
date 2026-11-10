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

#[Name('update_manufacturer')]
#[Title('Update Manufacturer')]
#[Description('Update fields on a Snipe-IT manufacturer identified by numeric ID or name')]
class UpdateManufacturerTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_name' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'support_url' => 'nullable|string|max:255',
            'support_email' => 'nullable|email|max:191',
            'support_phone' => 'nullable|string|max:191',
            'warranty_lookup_url' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $manufacturer = $this->resolveManufacturer($request);

        if (! $manufacturer) {
            return Response::make(Response::error('Manufacturer not found'));
        }

        if (! Gate::allows('update', $manufacturer)) {
            return Response::make(Response::error('Unauthorized'));
        }

        if ($request->filled('new_name')) {
            $manufacturer->name = $request->get('new_name');
        }

        $updatable = ['url', 'support_url', 'support_email', 'support_phone', 'warranty_lookup_url', 'notes'];

        foreach ($updatable as $field) {
            if ($request->filled($field)) {
                $manufacturer->{$field} = $request->get($field);
            }
        }

        if ($manufacturer->save()) {
            return Response::make(
                Response::text('Manufacturer '.$manufacturer->name.' updated successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Manufacturer updated successfully',
                'id' => $manufacturer->id,
                'name' => $manufacturer->name,
            ]);
        }

        return Response::make(Response::error('Update failed: '.$manufacturer->getErrors()->first()));
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
            'id' => $schema->number()->description('Numeric ID to identify the manufacturer'),
            'name' => $schema->string()->description('Name to identify the manufacturer'),
            'new_name' => $schema->string()->description('New name (renames the manufacturer)'),
            'url' => $schema->string()->description('Manufacturer website URL'),
            'support_url' => $schema->string()->description('Support website URL'),
            'support_email' => $schema->string()->description('Support email address'),
            'support_phone' => $schema->string()->description('Support phone number'),
            'warranty_lookup_url' => $schema->string()->description('Warranty lookup URL'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the manufacturer'),
            'name' => $schema->string()->description('Name of the manufacturer'),
        ];
    }
}
