<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Depreciation;
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

#[Name('update_asset_model')]
#[Title('Update Asset Model')]
#[Description('Update fields on a Snipe-IT asset model identified by numeric ID or name')]
class UpdateAssetModelTool extends Tool
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

        $model = $this->resolveModel($request);

        if (! $model || ! Gate::allows('update', $model)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.asset_model')])));
        }

        $fkMap = [
            ['category_id', 'category_name', Category::class],
            ['manufacturer_id', 'manufacturer_name', Manufacturer::class],
            ['depreciation_id', 'depreciation_name', Depreciation::class],
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

        $rules = array_intersect_key($model->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $model->fill(array_merge($input, $validated));

        if ($model->save()) {
            return Response::make(
                Response::text(trans('mcp.asset_model_updated', ['name' => $model->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.asset_model_updated', ['name' => $model->name]),
                'id' => $model->id,
                'name' => $model->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $model->getErrors()->first()])));
    }

    private function resolveModel(Request $request): ?AssetModel
    {
        if ($request->filled('id')) {
            return AssetModel::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return AssetModel::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID to identify the asset model'),
            'name' => $schema->string()->description('Name to identify the asset model'),
            'new_name' => $schema->string()->description('New name (renames the asset model)'),
            'category_id' => $schema->number()->description('Category ID. Alternative: category_name.'),
            'category_name' => $schema->string()->description('Category name. Resolved to an ID server-side.'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID. Alternative: manufacturer_name.'),
            'manufacturer_name' => $schema->string()->description('Manufacturer name. Resolved to an ID server-side.'),
            'depreciation_id' => $schema->number()->description('Depreciation schedule ID. Alternative: depreciation_name.'),
            'depreciation_name' => $schema->string()->description('Depreciation schedule name. Resolved to an ID server-side.'),
            'model_number' => $schema->string()->description('Model number'),
            'eol' => $schema->number()->description('End of life in months (0-240)'),
            'min_amt' => $schema->number()->description('Minimum quantity alert threshold'),
            'notes' => $schema->string()->description('Notes'),
            'requestable' => $schema->boolean()->description('Whether the model can be requested'),
            'require_serial' => $schema->boolean()->description('Whether serial numbers are required'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the asset model'),
            'name' => $schema->string()->description('Name of the asset model'),
        ];
    }
}
