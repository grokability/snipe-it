<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Depreciation;
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

#[Name('update_depreciation')]
#[Title('Update Depreciation')]
#[Description('Update fields on a Snipe-IT depreciation schedule identified by numeric ID or name')]
class UpdateDepreciationTool extends Tool
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

        $dep = $this->resolveDepreciation($request);

        if (! $dep || ! Gate::allows('update', $dep)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.depreciation')])));
        }

        $input = $request->all();
        if ($request->filled('new_name')) {
            $input['name'] = $request->get('new_name');
        }

        $rules = array_intersect_key($dep->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $dep->fill(array_merge($input, $validated));

        if ($dep->save()) {
            return Response::make(
                Response::text(trans('mcp.depreciation_updated', ['name' => $dep->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.depreciation_updated', ['name' => $dep->name]),
                'id' => $dep->id,
                'name' => $dep->name,
                'months' => $dep->months,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $dep->getErrors()->first()])));
    }

    private function resolveDepreciation(Request $request): ?Depreciation
    {
        if ($request->filled('id')) {
            return Depreciation::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Depreciation::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID to identify the depreciation'),
            'name' => $schema->string()->description('Name to identify the depreciation'),
            'new_name' => $schema->string()->description('New name (renames the depreciation)'),
            'months' => $schema->number()->description('Depreciation period in months (1-3600)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the depreciation'),
            'name' => $schema->string()->description('Name of the depreciation'),
            'months' => $schema->number()->description('Depreciation period in months'),
        ];
    }
}
