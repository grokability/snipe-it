<?php

namespace App\Mcp\Tools;

use App\Models\Statuslabel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_status_label')]
#[Title('Delete Status Label')]
#[Description('Soft-delete a Snipe-IT status label identified by numeric ID or name')]
class DeleteStatusLabelTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $label = $this->resolveStatusLabel($request);

        if (! $label) {
            return Response::make(Response::error('Status label not found'));
        }

        if (! Gate::allows('delete', $label)) {
            return Response::make(Response::error('Unauthorized'));
        }

        if ($label->assets()->count() > 0) {
            return Response::make(Response::error('Status label has assets assigned and cannot be deleted'));
        }

        $name = $label->name;

        $label->delete();

        return Response::make(
            Response::text('Status label '.$name.' deleted successfully')
        )->withStructuredContent([
            'success' => true,
            'message' => 'Status label deleted successfully',
            'name' => $name,
        ]);
    }

    private function resolveStatusLabel(Request $request): ?Statuslabel
    {
        if ($request->filled('id')) {
            return Statuslabel::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Statuslabel::where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the status label to delete'),
            'name' => $schema->string()->description('Name of the status label to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted status label'),
        ];
    }
}
