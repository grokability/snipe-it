<?php

namespace App\Mcp\Tools;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_category')]
#[Title('Create Category')]
#[Description('Create a new Snipe-IT category')]
class CreateCategoryTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', Category::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'category_type' => 'required|string|in:asset,accessory,consumable,component,license',
                'checkin_email' => 'nullable|boolean',
                'require_acceptance' => 'nullable|boolean',
                'use_default_eula' => 'nullable|boolean',
                'notes' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $category = new Category;
        $category->name = $request->get('name');
        $category->category_type = $request->get('category_type');
        $category->created_by = auth()->id();

        foreach (['checkin_email', 'require_acceptance', 'use_default_eula', 'notes'] as $field) {
            if ($request->filled($field)) {
                $category->{$field} = $request->get($field);
            }
        }

        if ($category->save()) {
            return Response::make(
                Response::text('Category '.$category->name.' created successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Category created successfully',
                'id' => $category->id,
                'name' => $category->name,
                'category_type' => $category->category_type,
            ]);
        }

        return Response::make(Response::error('Create failed: '.$category->getErrors()->first()));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Category name (required)'),
            'category_type' => $schema->string()->description('Category type (required): asset, accessory, consumable, component, or license'),
            'checkin_email' => $schema->boolean()->description('Send checkin email when items are checked in'),
            'require_acceptance' => $schema->boolean()->description('Require user acceptance when checking out'),
            'use_default_eula' => $schema->boolean()->description('Use the default EULA'),
            'notes' => $schema->string()->description('Notes'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the category was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new category'),
            'name' => $schema->string()->description('Name of the new category'),
            'category_type' => $schema->string()->description('Type of the new category'),
        ];
    }
}
