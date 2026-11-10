<?php

namespace App\Mcp\Tools;

use App\Models\Manufacturer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_manufacturer')]
#[Title('Create Manufacturer')]
#[Description('Create a new Snipe-IT manufacturer')]
class CreateManufacturerTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', Manufacturer::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'url' => 'nullable|string|max:255',
                'support_url' => 'nullable|string|max:255',
                'support_email' => 'nullable|email|max:191',
                'support_phone' => 'nullable|string|max:191',
                'warranty_lookup_url' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $manufacturer = new Manufacturer;
        $manufacturer->fill($request->only([
            'name', 'url', 'support_url', 'support_email', 'support_phone', 'warranty_lookup_url', 'notes',
        ]));

        if ($manufacturer->save()) {
            return Response::make(
                Response::text(trans('mcp.manufacturer_created', ['name' => $manufacturer->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.manufacturer_created', ['name' => $manufacturer->name]),
                'id' => $manufacturer->id,
                'name' => $manufacturer->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $manufacturer->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Manufacturer name (required)'),
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
            'success' => $schema->boolean()->description('True if the manufacturer was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new manufacturer'),
            'name' => $schema->string()->description('Name of the new manufacturer'),
        ];
    }
}
