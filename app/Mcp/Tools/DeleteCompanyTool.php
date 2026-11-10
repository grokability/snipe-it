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

#[Name('delete_company')]
#[Title('Delete Company')]
#[Description('Soft-delete a Snipe-IT company by numeric ID or name')]
class DeleteCompanyTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $company = $this->resolveCompany($request);

        if (! $company) {
            return Response::make(Response::error(trans('mcp.company_not_found')));
        }

        if (! Gate::allows('delete', $company)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $name = $company->name;

        $company->delete();

        return Response::make(
            Response::text(trans('mcp.company_deleted', ['name' => $name]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.company_deleted', ['name' => $name]),
            'name' => $name,
        ]);
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
            'id' => $schema->number()->description('Numeric ID of the company to delete'),
            'name' => $schema->string()->description('Name of the company to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted company'),
        ];
    }
}
