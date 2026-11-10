<?php

namespace App\Mcp\Tools;

use App\Models\Component;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_component')]
#[Title('Delete Component')]
#[Description('Soft-delete a Snipe-IT component. The component must have no units currently checked out to assets.')]
class DeleteComponentTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:191',
        ]);

        $component = $this->resolveComponent($request);

        if (! $component) {
            return Response::make(Response::error('Component not found'));
        }

        if ($component->numCheckedOut() > 0) {
            return Response::make(Response::error('Component has units checked out and cannot be deleted. Check them in first.'));
        }

        $name = $component->name;

        $component->delete();

        return Response::make(
            Response::text('Component '.$name.' deleted successfully')
        )->withStructuredContent([
            'success' => true,
            'message' => 'Component deleted successfully',
            'name' => $name,
        ]);
    }

    private function resolveComponent(Request $request): ?Component
    {
        if ($request->filled('id')) {
            return Component::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Component::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the component to delete'),
            'name' => $schema->string()->description('Name of the component to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted component'),
        ];
    }
}
