<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Mcp\Traits\SnipeMcpTool;
use App\Models\Location;
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

#[Name('create_location')]
#[Title('Create Location')]
#[Description('Create a new Snipe-IT location')]
class CreateLocationTool extends Tool
{
    use ResolvesForeignKeyByName;
    use SnipeMcpTool;

    /**
     * @var list<array{0: string, 1: string, 2: class-string<\Illuminate\Database\Eloquent\Model>}>
     */
    protected array $foreignKeys = [
        ['parent_id', 'parent_name', Location::class],
    ];

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', Location::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Resolve name-based FK inputs to numeric IDs before validation
        // so the downstream rules + fill see a consistent shape.
        // Lookups run through the model's normal global scopes, so a
        // non-superuser in a locked-down FMCS install gets a not-found
        // error for a name that only exists in a tenant they cannot
        // see, matching the UI's "that record does not exist" posture.
        $resolvedFks = $this->resolveForeignKeys($request);

        $input = array_merge($request->all(), $resolvedFks);

        $location = new Location;
        try {
            $validated = Validator::make($input, $location->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $location->fill(array_merge($input, $validated));

        if ($location->save()) {
            return $this->structuredResponse(
                trans('mcp.location_created', ['name' => $location->name]),
                [
                    'success' => true,
                    'message' => trans('mcp.location_created', ['name' => $location->name]),
                    'id' => $location->id,
                    'name' => $location->name,
                ]
            );
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $location->getErrors()->first()])));
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
            'parent_id' => $schema->number()->description('Parent location ID. Alternative: parent_name.'),
            'parent_name' => $schema->string()->description('Parent location name. Resolved to an ID server-side.'),
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
