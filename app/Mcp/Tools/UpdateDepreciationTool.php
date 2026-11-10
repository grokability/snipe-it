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

#[Name('update_depreciation')]
#[Title('Update Depreciation')]
#[Description('Update fields on a Snipe-IT depreciation schedule identified by numeric ID or name')]
class UpdateDepreciationTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_name' => 'nullable|string|max:255',
            'months' => 'nullable|integer|min:1|max:3600',
        ]);

        $dep = $this->resolveDepreciation($request);

        if (! $dep) {
            return Response::make(Response::error('Depreciation not found'));
        }

        if (! Gate::allows('update', $dep)) {
            return Response::make(Response::error('Unauthorized'));
        }

        if ($request->filled('new_name')) {
            $dep->name = $request->get('new_name');
        }

        if ($request->filled('months')) {
            $dep->months = $request->get('months');
        }

        if ($dep->save()) {
            return Response::make(
                Response::text('Depreciation '.$dep->name.' updated successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Depreciation updated successfully',
                'id' => $dep->id,
                'name' => $dep->name,
                'months' => $dep->months,
            ]);
        }

        return Response::make(Response::error('Update failed: '.$dep->getErrors()->first()));
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
            'id' => $schema->number()->description('Numeric ID to identify the depreciation'),
            'name' => $schema->string()->description('Name to identify the depreciation'),
            'new_name' => $schema->string()->description('New name (renames the depreciation)'),
            'months' => $schema->number()->description('Depreciation period in months (1-3600)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the depreciation'),
            'name' => $schema->string()->description('Name of the depreciation'),
            'months' => $schema->number()->description('Depreciation period in months'),
        ];
    }
}
