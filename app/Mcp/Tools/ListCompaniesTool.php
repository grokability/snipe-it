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

#[Name('list_companies')]
#[Title('List Companies')]
#[Description('Search and list Snipe-IT companies with optional filtering and pagination')]
class ListCompaniesTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('view', Company::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $companies = Company::withCount([
            'assets as assets_count' => fn ($q) => $q->AssetsForShow(),
        ])->withCount('licenses as licenses_count', 'users as users_count');

        if ($request->filled('search')) {
            $companies->TextSearch($request->get('search'));
        }

        $companies->orderBy('created_at', 'desc');

        $total = $companies->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $companies->skip($offset)->take($limit)->get();

        $companiesData = $results->map(fn (Company $company) => [
            'id' => $company->id,
            'name' => $company->name,
            'phone' => $company->phone,
            'fax' => $company->fax,
            'email' => $company->email,
            'assets_count' => $company->assets_count,
            'licenses_count' => $company->licenses_count,
            'users_count' => $company->users_count,
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_companies', ['total' => $total, 'count' => count($companiesData)]))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'companies' => $companiesData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword to search across company name, phone, fax, email'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching companies')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'companies' => $schema->array()->description('List of companies')->required(),
        ];
    }
}
