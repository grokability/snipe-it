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

#[Name('show_company')]
#[Title('Show Company Details')]
#[Description('Look up a single Snipe-IT company by numeric ID or name and return its full details')]
class ShowCompanyTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $company = null;

        if ($request->filled('id')) {
            $company = Company::withCount([
                'assets as assets_count' => fn ($q) => $q->AssetsForShow(),
            ])->withCount('users as users_count')->find($request->get('id'));
        } elseif ($request->filled('name')) {
            $company = Company::withCount([
                'assets as assets_count' => fn ($q) => $q->AssetsForShow(),
            ])->withCount('users as users_count')->where('name', $request->get('name'))->first();
        } else {
            return Response::make(Response::error(trans('mcp.id_or_name_required')));
        }

        if (! $company) {
            return Response::make(Response::error(trans('mcp.company_not_found')));
        }

        if (! Gate::allows('view', $company)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        return Response::make(
            Response::text(trans('mcp.company_found', ['name' => $company->name]))
        )->withStructuredContent([
            'id' => $company->id,
            'name' => $company->name,
            'phone' => $company->phone,
            'fax' => $company->fax,
            'email' => $company->email,
            'assets_count' => $company->assets_count,
            'users_count' => $company->users_count,
            'notes' => $company->notes,
            'created_at' => $company->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $company->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the company to look up'),
            'name' => $schema->string()->description('Name of the company to look up'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric company ID'),
            'name' => $schema->string()->description('Company name'),
            'phone' => $schema->string()->description('Company phone number'),
            'fax' => $schema->string()->description('Company fax number'),
            'email' => $schema->string()->description('Company email address'),
            'assets_count' => $schema->number()->description('Number of assets belonging to this company'),
            'users_count' => $schema->number()->description('Number of users belonging to this company'),
            'notes' => $schema->string()->description('Notes'),
            'created_at' => $schema->string()->description('Creation timestamp'),
            'updated_at' => $schema->string()->description('Last update timestamp'),
        ];
    }
}
