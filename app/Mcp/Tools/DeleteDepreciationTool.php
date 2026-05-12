<?php

namespace App\Mcp\Tools;

use App\Models\Depreciation;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_depreciation')]
#[Title('Delete Depreciation')]
#[Description('Soft-delete a Snipe-IT depreciation schedule by numeric ID or name')]
class DeleteDepreciationTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $dep = $this->resolveDepreciation($request);

        if (! $dep) {
            return Response::make(Response::error(trans('mcp.depreciation_not_found')));
        }

        if (! Gate::allows('delete', $dep)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $name = $dep->name;

        $dep->delete();

        return Response::make(
            Response::text(trans('mcp.depreciation_deleted', ['name' => $name]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.depreciation_deleted', ['name' => $name]),
            'name' => $name,
        ]);
    }

    private function resolveDepreciation(Request $request): ?Depreciation
    {
        if ($request->filled('id')) {
            return Depreciation::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Depreciation::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the depreciation to delete'),
            'name' => $schema->string()->description('Name of the depreciation to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted depreciation'),
        ];
    }
}
