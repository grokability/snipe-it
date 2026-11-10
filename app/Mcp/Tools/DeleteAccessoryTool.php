<?php

namespace App\Mcp\Tools;

use App\Models\Accessory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_accessory')]
#[Title('Delete Accessory')]
#[Description('Soft-delete a Snipe-IT accessory. The accessory must have no units currently checked out.')]
class DeleteAccessoryTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $accessory = $this->resolveAccessory($request);

        if (! $accessory) {
            return Response::make(Response::error(trans('mcp.accessory_not_found')));
        }

        if (! Gate::allows('delete', $accessory)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if ($accessory->numCheckedOut() > 0) {
            return Response::make(Response::error(trans('mcp.accessory_has_checkouts')));
        }

        $name = $accessory->name;

        $accessory->delete();

        return Response::make(
            Response::text(trans('mcp.accessory_deleted', ['name' => $name]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.accessory_deleted', ['name' => $name]),
            'name' => $name,
        ]);
    }

    private function resolveAccessory(Request $request): ?Accessory
    {
        if ($request->filled('id')) {
            return Accessory::withCount('checkouts as checkouts_count')->find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Accessory::withCount('checkouts as checkouts_count')->where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the accessory to delete'),
            'name' => $schema->string()->description('Name of the accessory to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted accessory'),
        ];
    }
}
