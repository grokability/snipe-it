<?php

namespace App\Mcp\Tools;

use App\Models\Location;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_location')]
#[Title('Update Location')]
#[Description('Update fields on a Snipe-IT location identified by numeric ID or name')]
class UpdateLocationTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'zip' => 'nullable|string',
            'phone' => 'nullable|string',
            'fax' => 'nullable|string',
            'currency' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:locations,id',
            'manager_id' => 'nullable|integer|exists:users,id',
        ]);

        $location = $this->resolveLocation($request);

        if (! $location) {
            return Response::make(Response::error('Location not found'));
        }

        if (! Gate::allows('update', $location)) {
            return Response::make(Response::error('Unauthorized'));
        }

        if ($request->filled('new_name')) {
            $location->name = $request->get('new_name');
        }

        foreach (['address', 'city', 'state', 'country', 'zip', 'phone', 'fax', 'currency', 'parent_id', 'manager_id'] as $field) {
            if ($request->filled($field)) {
                $location->{$field} = $request->get($field);
            }
        }

        if ($location->save()) {
            return Response::make(
                Response::text('Location '.$location->name.' updated successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Location updated successfully',
                'id' => $location->id,
                'name' => $location->name,
            ]);
        }

        return Response::make(Response::error('Update failed: '.$location->getErrors()->first()));
    }

    private function resolveLocation(Request $request): ?Location
    {
        if ($request->filled('id')) {
            return Location::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Location::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID to identify the location'),
            'name' => $schema->string()->description('Name to identify the location'),
            'new_name' => $schema->string()->description('New name (renames the location)'),
            'address' => $schema->string()->description('Street address'),
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
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the location'),
            'name' => $schema->string()->description('Name of the location'),
        ];
    }
}
