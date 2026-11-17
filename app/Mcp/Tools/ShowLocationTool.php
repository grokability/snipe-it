<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\SnipeMcpTool;
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

#[Name('show_location')]
#[Title('Show Location Details')]
#[Description('Look up a single Snipe-IT location by numeric ID or name and return its full details')]
class ShowLocationTool extends Tool
{
    use SnipeMcpTool;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Read)) {
            return $denied;
        }

        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $location = $this->resolveLocation($request);

        if ($location === false) {
            return Response::make(Response::error(trans('mcp.id_or_name_required')));
        }

        if (! $location || ! Gate::allows('view', $location)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.location')])));
        }

        $location->loadCount('assets as assets_count', 'users as users_count', 'children as children_count');

        return $this->structuredResponse(
            trans('mcp.location_found', ['name' => $location->name]),
            [
                'id' => $location->id,
                'name' => $location->name,
                'address' => $location->address,
                'address2' => $location->address2,
                'city' => $location->city,
                'state' => $location->state,
                'country' => $location->country,
                'zip' => $location->zip,
                'phone' => $location->phone,
                'fax' => $location->fax,
                'parent_id' => $location->parent_id,
                'parent' => $location->parent?->name,
                'assets_count' => $location->assets_count,
                'users_count' => $location->users_count,
                'children_count' => $location->children_count,
                'created_at' => $location->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $location->updated_at?->format('Y-m-d H:i:s'),
            ]
        );
    }

    private function resolveLocation(Request $request): Location|false|null
    {
        if ($request->filled('id')) {
            return Location::with('parent')->find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Location::with('parent')->where('name', $request->get('name'))->first();
        }

        return false;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the location to look up'),
            'name' => $schema->string()->description('Name of the location to look up'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric location ID'),
            'name' => $schema->string()->description('Location name'),
            'address' => $schema->string()->description('Street address')->nullable(),
            'address2' => $schema->string()->description('Address line 2')->nullable(),
            'city' => $schema->string()->description('City')->nullable(),
            'state' => $schema->string()->description('State')->nullable(),
            'country' => $schema->string()->description('Country')->nullable(),
            'zip' => $schema->string()->description('Zip code')->nullable(),
            'phone' => $schema->string()->description('Phone number')->nullable(),
            'fax' => $schema->string()->description('Fax number')->nullable(),
            'parent_id' => $schema->number()->description('Parent location ID')->nullable(),
            'parent' => $schema->string()->description('Parent location name')->nullable(),
            'assets_count' => $schema->number()->description('Number of assets at this location'),
            'users_count' => $schema->number()->description('Number of users at this location'),
            'children_count' => $schema->number()->description('Number of child locations'),
            'created_at' => $schema->string()->description('Creation timestamp')->nullable(),
            'updated_at' => $schema->string()->description('Last update timestamp')->nullable(),
        ];
    }
}
