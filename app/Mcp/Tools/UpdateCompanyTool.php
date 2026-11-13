<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Company;
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

#[Name('update_company')]
#[Title('Update Company')]
#[Description('Update fields on a Snipe-IT company identified by numeric ID or name')]
class UpdateCompanyTool extends Tool
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

        $company = $this->resolveCompany($request);

        if (! $company || ! Gate::allows('update', $company)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.company')])));
        }

        $input = $request->all();
        if ($request->filled('new_name')) {
            $input['name'] = $request->get('new_name');
        }

        $rules = array_intersect_key($company->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $company->fill(array_merge($input, $validated));

        if ($company->save()) {
            return Response::make(
                Response::text(trans('mcp.company_updated', ['name' => $company->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.company_updated', ['name' => $company->name]),
                'id' => $company->id,
                'name' => $company->name,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $company->getErrors()->first()])));
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
