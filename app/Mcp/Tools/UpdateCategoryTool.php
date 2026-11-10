<?php

namespace App\Mcp\Tools;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_category')]
#[Title('Update Category')]
#[Description('Update fields on a Snipe-IT category identified by numeric ID or name')]
class UpdateCategoryTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_name' => 'nullable|string|max:255',
            'category_type' => 'nullable|string|in:asset,accessory,consumable,component,license',
            'notes' => 'nullable|string',
            'checkin_email' => 'nullable|boolean',
            'require_acceptance' => 'nullable|boolean',
            'use_default_eula' => 'nullable|boolean',
        ]);

        $category = $this->resolveCategory($request);

        if (! $category) {
            return Response::make(Response::error(trans('mcp.category_not_found')));
        }

        if (! Gate::allows('update', $category)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if ($request->filled('new_name')) {
            $category->name = $request->get('new_name');
        }

        foreach (['category_type', 'notes', 'checkin_email', 'require_acceptance', 'use_default_eula'] as $field) {
            if ($request->filled($field)) {
                $category->{$field} = $request->get($field);
            }
        }

        if ($category->save()) {
            return Response::make(
                Response::text(trans('mcp.category_updated', ['name' => $category->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.category_updated', ['name' => $category->name]),
                'id' => $category->id,
                'name' => $category->name,
                'category_type' => $category->category_type,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $category->getErrors()->first()])));
    }

    private function resolveCategory(Request $request): ?Category
    {
        if ($request->filled('id')) {
            return Category::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Category::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID to identify the category'),
            'name' => $schema->string()->description('Name to identify the category'),
            'new_name' => $schema->string()->description('New name (renames the category)'),
            'category_type' => $schema->string()->description('Category type: asset, accessory, consumable, component, or license'),
            'checkin_email' => $schema->boolean()->description('Send checkin email when items are checked in'),
            'require_acceptance' => $schema->boolean()->description('Require user acceptance when checking out'),
            'use_default_eula' => $schema->boolean()->description('Use the default EULA'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the category'),
            'name' => $schema->string()->description('Name of the category'),
            'category_type' => $schema->string()->description('Type of the category'),
        ];
    }
}
