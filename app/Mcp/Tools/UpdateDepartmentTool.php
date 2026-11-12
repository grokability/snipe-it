<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Company;
use App\Models\Department;
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

#[Name('update_department')]
#[Title('Update Department')]
#[Description('Update fields on a Snipe-IT department identified by numeric ID or name')]
class UpdateDepartmentTool extends Tool
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

        $department = $this->resolveDepartment($request);

        if (! $department || ! Gate::allows('update', $department)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.department')])));
        }

        $fkMap = [
            ['location_id', 'location_name', Location::class],
            ['company_id', 'company_name', Company::class],
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

        $rules = array_intersect_key($department->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $department->fill(array_merge($input, $validated));

        if (! empty($input['company_id'])) {
            $department->company_id = Company::getIdForCurrentUser($input['company_id']);
        }

        if ($department->save()) {
            return Response::make(
                Response::text(trans('mcp.department_updated', ['name' => $department->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.department_updated', ['name' => $department->name]),
                'id' => $department->id,
                'name' => $department->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $department->getErrors()->first()])));
    }

    private function resolveDepartment(Request $request): ?Department
    {
        if ($request->filled('id')) {
            return Department::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Department::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID to identify the department'),
            'name' => $schema->string()->description('Name to identify the department'),
            'new_name' => $schema->string()->description('New name (renames the department)'),
            'location_id' => $schema->number()->description('Location ID. Alternative: location_name.'),
            'location_name' => $schema->string()->description('Location name. Resolved to an ID server-side.'),
            'company_id' => $schema->number()->description('Company ID. Alternative: company_name.'),
            'company_name' => $schema->string()->description('Company name. Resolved to an ID server-side.'),
            'manager_id' => $schema->number()->description('User ID of the department manager'),
            'phone' => $schema->string()->description('Department phone number'),
            'fax' => $schema->string()->description('Department fax number'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the department'),
            'name' => $schema->string()->description('Name of the department'),
        ];
    }
}
