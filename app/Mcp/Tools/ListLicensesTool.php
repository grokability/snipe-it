<?php

namespace App\Mcp\Tools;

use App\Models\License;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_licenses')]
#[Title('List Licenses')]
#[Description('Search and list Snipe-IT licenses with optional filtering by keyword, company, category, and manufacturer')]
class ListLicensesTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('index', License::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $request->validate([
            'search' => 'nullable|string|max:255',
            'company_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'manufacturer_id' => 'nullable|integer',
            'supplier_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $licenses = License::with('company', 'manufacturer', 'supplier', 'category')
            ->withCount('freeSeats as free_seats_count');

        if ($request->filled('search')) {
            $licenses->TextSearch($request->get('search'));
        }

        if ($request->filled('company_id')) {
            $licenses->where('licenses.company_id', '=', $request->get('company_id'));
        }

        if ($request->filled('category_id')) {
            $licenses->where('category_id', '=', $request->get('category_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $licenses->where('manufacturer_id', '=', $request->get('manufacturer_id'));
        }

        if ($request->filled('supplier_id')) {
            $licenses->where('supplier_id', '=', $request->get('supplier_id'));
        }

        $licenses->orderBy('licenses.created_at', 'desc');

        $total = $licenses->count();
        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $results = $licenses->skip($offset)->take($limit)->get();

        $licensesData = $results->map(fn (License $license) => [
            'id' => $license->id,
            'name' => $license->name,
            'serial' => $license->serial,
            'seats' => $license->seats,
            'free_seats' => $license->free_seats_count,
            'category' => $license->category?->name,
            'manufacturer' => $license->manufacturer?->name,
            'company' => $license->company?->name,
            'supplier' => $license->supplier?->name,
            'expiration_date' => $license->expiration_date?->format('Y-m-d'),
            'purchase_date' => $license->purchase_date?->format('Y-m-d'),
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_licenses', ['total' => $total, 'count' => count($licensesData)]))
        )->withStructuredContent([
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'licenses' => $licensesData,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Keyword to search across name, serial, notes, and order number'),
            'company_id' => $schema->number()->description('Filter by company ID'),
            'category_id' => $schema->number()->description('Filter by category ID'),
            'manufacturer_id' => $schema->number()->description('Filter by manufacturer ID'),
            'supplier_id' => $schema->number()->description('Filter by supplier ID'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'total' => $schema->number()->description('Total number of matching licenses')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
        ];
    }
}
