<?php

namespace App\Mcp\Tools;

use App\Models\Company;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_company')]
#[Title('Update Company')]
#[Description('Update fields on a Snipe-IT company identified by numeric ID or name')]
class UpdateCompanyTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
            'fax' => 'nullable|string',
            'email' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $company = $this->resolveCompany($request);

        if (! $company) {
            return Response::make(Response::error('Company not found'));
        }

        if (! Gate::allows('update', $company)) {
            return Response::make(Response::error('Unauthorized'));
        }

        if ($request->filled('new_name')) {
            $company->name = $request->get('new_name');
        }

        foreach (['phone', 'fax', 'email', 'notes'] as $field) {
            if ($request->filled($field)) {
                $company->{$field} = $request->get($field);
            }
        }

        if ($company->save()) {
            return Response::make(
                Response::text('Company '.$company->name.' updated successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Company updated successfully',
                'id' => $company->id,
                'name' => $company->name,
            ]);
        }

        return Response::make(Response::error('Update failed: '.$company->getErrors()->first()));
    }

    private function resolveCompany(Request $request): ?Company
    {
        if ($request->filled('id')) {
            return Company::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Company::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID to identify the company'),
            'name' => $schema->string()->description('Name to identify the company'),
            'new_name' => $schema->string()->description('New name (renames the company)'),
            'phone' => $schema->string()->description('Company phone number'),
            'fax' => $schema->string()->description('Company fax number'),
            'email' => $schema->string()->description('Company email address'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the company'),
            'name' => $schema->string()->description('Name of the company'),
        ];
    }
}
