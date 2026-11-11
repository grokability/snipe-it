<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Consumable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_consumable')]
#[Title('Delete Consumable')]
#[Description('Soft-delete a Snipe-IT consumable. The consumable must have no units currently checked out.')]
class DeleteConsumableTool extends Tool
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

        $consumable = $this->resolveConsumable($request);

        if (! $consumable || ! Gate::allows('delete', $consumable)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.consumable')])));
        }

        if ($consumable->users()->count() > 0) {
            return Response::make(Response::error(trans('mcp.consumable_has_checkouts')));
        }

        $name = $consumable->name;

        $consumable->delete();

        return Response::make(
            Response::text(trans('mcp.consumable_deleted', ['name' => $name]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.consumable_deleted', ['name' => $name]),
            'name' => $name,
        ]);
    }

    private function resolveConsumable(Request $request): ?Consumable
    {
        if ($request->filled('id')) {
            return Consumable::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Consumable::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the consumable to delete'),
            'name' => $schema->string()->description('Name of the consumable to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted consumable'),
        ];
    }
}
