<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Company;
use App\Models\Department;
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

        $input = $request->all();
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

        if ($request->filled('company_id')) {
            $department->company_id = Company::getIdForCurrentUser($request->get('company_id'));
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
            'location_id' => $schema->number()->description('Location ID'),
            'company_id' => $schema->number()->description('Company ID'),
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
