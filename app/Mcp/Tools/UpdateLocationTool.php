<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
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

#[Name('update_location')]
#[Title('Update Location')]
#[Description('Update fields on a Snipe-IT location identified by numeric ID or name')]
class UpdateLocationTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        try {
            $request->validate([
                'id' => 'nullable|integer',
                'name' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $location = $this->resolveLocation($request);

        if (! $location || ! Gate::allows('update', $location)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.location')])));
        }

        $fkMap = [
            ['parent_id', 'parent_name', Location::class],
        ];

        $resolvedFks = [];
        foreach ($fkMap as [$idField, $nameField, $modelClass]) {
            $resolved = $this->resolveFkByName($request, $idField, $nameField, $modelClass);
            if ($this->fkResolutionFailed($resolved)) {
                return Response::make(Response::error($this->fkErrorMessage($resolved)));
            }
            if ($resolved !== null) {
                $resolvedFks[$idField] = $resolved;
            }
        }

        $input = array_merge($request->all(), $resolvedFks);
        if ($request->filled('new_name')) {
            $input['name'] = $request->get('new_name');
        }

        $rules = array_intersect_key($location->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $location->fill(array_merge($input, $validated));

        if ($location->save()) {
            return Response::make(
                Response::text(trans('mcp.location_updated', ['name' => $location->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.location_updated', ['name' => $location->name]),
                'id' => $location->id,
                'name' => $location->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $location->getErrors()->first()])));
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
            'parent_id' => $schema->number()->description('Parent location ID. Alternative: parent_name.'),
            'parent_name' => $schema->string()->description('Parent location name. Resolved to an ID server-side.'),
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
