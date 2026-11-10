<?php

namespace App\Mcp\Tools;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
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
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_name' => 'nullable|string|max:255',
            'location_id' => 'nullable|integer|exists:locations,id',
            'company_id' => 'nullable|integer|exists:companies,id',
            'manager_id' => 'nullable|integer|exists:users,id',
            'phone' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
        ]);

        $department = $this->resolveDepartment($request);

        if (! $department) {
            return Response::make(Response::error('Department not found'));
        }

        if (! Gate::allows('update', $department)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $updatable = ['location_id', 'manager_id', 'phone', 'fax', 'notes'];

        foreach ($updatable as $field) {
            if ($request->filled($field)) {
                $department->{$field} = $request->get($field);
            }
        }

        if ($request->filled('new_name')) {
            $department->name = $request->get('new_name');
        }

        if ($request->filled('company_id')) {
            $department->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        }

        if ($department->save()) {
            return Response::make(
                Response::text('Department '.$department->name.' updated successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Department updated successfully',
                'id' => $department->id,
                'name' => $department->name,
            ]);
        }

        return Response::make(Response::error('Update failed: '.$department->getErrors()->first()));
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
