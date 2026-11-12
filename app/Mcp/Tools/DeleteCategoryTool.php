<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_category')]
#[Title('Delete Category')]
#[Description('Soft-delete a Snipe-IT category. The category must have no items assigned to it.')]
class DeleteCategoryTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Delete)) {
            return $denied;
        }

        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $category = $this->resolveCategory($request);

        if (! $category) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.category')])));
        }

        if (! $category->isDeletable()) {
            return Response::make(Response::error(trans('admin/categories/message.assoc_models')));
        }

        $name = $category->name;

        try {
            $category->delete();
        } catch (\Exception $e) {
            return Response::make(Response::error(trans('mcp.category_delete_failed', ['error' => $e->getMessage()])));
        }

        return Response::make(
            Response::text(trans('mcp.category_deleted', ['name' => $name]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.category_deleted', ['name' => $name]),
            'name' => $name,
        ]);
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
            'id' => $schema->number()->description('Numeric ID of the category to delete'),
            'name' => $schema->string()->description('Name of the category to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted category'),
        ];
    }
}
