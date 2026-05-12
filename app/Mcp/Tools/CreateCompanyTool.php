<?php

namespace App\Mcp\Tools;

use App\Models\Company;
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

#[Name('create_company')]
#[Title('Create Company')]
#[Description('Create a new Snipe-IT company')]
class CreateCompanyTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', Company::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',
                'email' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $company = new Company;
        $company->name = $request->get('name');
        if ($request->filled('phone')) {
            $company->phone = $request->get('phone');
        }
        if ($request->filled('fax')) {
            $company->fax = $request->get('fax');
        }
        if ($request->filled('email')) {
            $company->email = $request->get('email');
        }
        if ($request->filled('notes')) {
            $company->notes = $request->get('notes');
        }
        $company->created_by = auth()->id();

        if ($company->save()) {
            return Response::make(
                Response::text(trans('mcp.company_created', ['name' => $company->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.company_created', ['name' => $company->name]),
                'id' => $company->id,
                'name' => $company->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $company->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Company name (required)'),
            'phone' => $schema->string()->description('Company phone number'),
            'fax' => $schema->string()->description('Company fax number'),
            'email' => $schema->string()->description('Company email address'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the company was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new company'),
            'name' => $schema->string()->description('Name of the new company'),
        ];
    }
}
