<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Manufacturer;
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

#[Name('create_manufacturer')]
#[Title('Create Manufacturer')]
#[Description('Create a new Snipe-IT manufacturer')]
class CreateManufacturerTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', Manufacturer::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $manufacturer = new Manufacturer;
        try {
            $validated = Validator::make($request->all(), $manufacturer->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $manufacturer->fill(array_merge($request->all(), $validated));

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
