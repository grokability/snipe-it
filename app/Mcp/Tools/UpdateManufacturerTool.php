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

#[Name('update_manufacturer')]
#[Title('Update Manufacturer')]
#[Description('Update fields on a Snipe-IT manufacturer identified by numeric ID or name')]
class UpdateManufacturerTool extends Tool
{
    use HasMcpScope;

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

        $manufacturer = $this->resolveManufacturer($request);

        if (! $manufacturer || ! Gate::allows('update', $manufacturer)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.manufacturer')])));
        }

        $input = $request->all();
        if ($request->filled('new_name')) {
            $input['name'] = $request->get('new_name');
        }

        $rules = array_intersect_key($manufacturer->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $manufacturer->fill(array_merge($input, $validated));

        if ($manufacturer->save()) {
            return Response::make(
                Response::text(trans('mcp.manufacturer_updated', ['name' => $manufacturer->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.manufacturer_updated', ['name' => $manufacturer->name]),
                'id' => $manufacturer->id,
                'name' => $manufacturer->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $manufacturer->getErrors()->first()])));
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
