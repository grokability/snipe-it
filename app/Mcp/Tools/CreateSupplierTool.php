<?php

namespace App\Mcp\Tools;

use App\Models\Supplier;
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

#[Name('create_supplier')]
#[Title('Create Supplier')]
#[Description('Create a new Snipe-IT supplier')]
class CreateSupplierTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', Supplier::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
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
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $supplier = new Supplier;
        $supplier->fill($request->only([
            'name', 'address', 'address2', 'city', 'state', 'country', 'zip',
            'phone', 'fax', 'email', 'url', 'contact', 'notes',
        ]));

        if ($supplier->save()) {
            return Response::make(
                Response::text('Supplier '.$supplier->name.' created successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Supplier created successfully',
                'id' => $supplier->id,
                'name' => $supplier->name,
            ]);
        }

        return Response::make(Response::error('Create failed: '.$supplier->getErrors()->first()));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Supplier name (required)'),
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
            'success' => $schema->boolean()->description('True if the supplier was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new supplier'),
            'name' => $schema->string()->description('Name of the new supplier'),
        ];
    }
}
