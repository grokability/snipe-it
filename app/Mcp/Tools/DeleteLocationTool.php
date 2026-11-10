<?php

namespace App\Mcp\Tools;

use App\Models\Location;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_location')]
#[Title('Delete Location')]
#[Description('Soft-delete a Snipe-IT location by numeric ID or name')]
class DeleteLocationTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $location = $this->resolveLocation($request);

        if (! $location) {
            return Response::make(Response::error('Location not found'));
        }

        if (! Gate::allows('delete', $location)) {
            return Response::make(Response::error('Unauthorized'));
        }

        if ($location->users()->count() > 0) {
            return Response::make(Response::error('Location has users assigned and cannot be deleted'));
        }

        if ($location->children()->count() > 0) {
            return Response::make(Response::error('Location has child locations and cannot be deleted'));
        }

        $name = $location->name;

        $location->delete();

        return Response::make(
            Response::text('Location '.$name.' deleted successfully')
        )->withStructuredContent([
            'success' => true,
            'message' => 'Location deleted successfully',
            'name' => $name,
        ]);
    }

    private function resolveLocation(Request $request): ?Location
    {
        if ($request->filled('id')) {
            return Location::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Location::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the location to delete'),
            'name' => $schema->string()->description('Name of the location to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted location'),
        ];
    }
}
