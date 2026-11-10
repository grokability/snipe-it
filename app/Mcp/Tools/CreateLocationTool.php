<?php

namespace App\Mcp\Tools;

use App\Models\Location;
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

#[Name('create_location')]
#[Title('Create Location')]
#[Description('Create a new Snipe-IT location')]
class CreateLocationTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', Location::class)) {
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
                'phone' => 'nullable|string|max:255',
                'fax' => 'nullable|string|max:255',
                'currency' => 'nullable|string',
                'parent_id' => 'nullable|integer|exists:locations,id',
                'manager_id' => 'nullable|integer|exists:users,id',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $location = new Location;
        $location->name = $request->get('name');

        foreach (['address', 'address2', 'city', 'state', 'country', 'zip', 'phone', 'fax', 'currency', 'parent_id', 'manager_id'] as $field) {
            if ($request->filled($field)) {
                $location->{$field} = $request->get($field);
            }
        }

        if ($location->save()) {
            return Response::make(
                Response::text('Location '.$location->name.' created successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Location created successfully',
                'id' => $location->id,
                'name' => $location->name,
            ]);
        }

        return Response::make(Response::error('Create failed: '.$location->getErrors()->first()));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Location name (required)'),
            'address' => $schema->string()->description('Street address'),
            'address2' => $schema->string()->description('Address line 2'),
            'city' => $schema->string()->description('City'),
            'state' => $schema->string()->description('State'),
            'country' => $schema->string()->description('Country'),
            'zip' => $schema->string()->description('Zip code'),
            'phone' => $schema->string()->description('Phone number'),
            'fax' => $schema->string()->description('Fax number'),
            'currency' => $schema->string()->description('Currency code'),
            'parent_id' => $schema->number()->description('Parent location ID'),
            'manager_id' => $schema->number()->description('Manager user ID'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the location was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new location'),
            'name' => $schema->string()->description('Name of the new location'),
        ];
    }
}
