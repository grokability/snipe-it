<?php

namespace App\Mcp\Tools;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_department')]
#[Title('Create Department')]
#[Description('Create a new Snipe-IT department')]
class CreateDepartmentTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', Department::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'location_id' => 'nullable|integer|exists:locations,id',
                'company_id' => 'nullable|integer|exists:companies,id',
                'manager_id' => 'nullable|integer|exists:users,id',
                'phone' => 'nullable|string|max:255',
                'fax' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $department = new Department;
        $department->fill($request->only([
            'name', 'location_id', 'manager_id', 'phone', 'fax', 'notes',
        ]));

        $department->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        $department->created_by = auth()->id();

        if ($department->save()) {
            return Response::make(
                Response::text(trans('mcp.department_created', ['name' => $department->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.department_created', ['name' => $department->name]),
                'id' => $department->id,
                'name' => $department->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $department->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Department name (required)'),
            'location_id' => $schema->number()->description('Location ID'),
            'company_id' => $schema->number()->description('Company ID (defaults to the authenticated user\'s company)'),
            'manager_id' => $schema->number()->description('User ID of the department manager'),
            'phone' => $schema->string()->description('Department phone number'),
            'fax' => $schema->string()->description('Department fax number'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the department was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new department'),
            'name' => $schema->string()->description('Name of the new department'),
        ];
    }
}
