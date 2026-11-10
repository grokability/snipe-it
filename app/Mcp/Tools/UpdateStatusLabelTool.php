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

#[Name('update_status_label')]
#[Title('Update Status Label')]
#[Description('Update fields on a Snipe-IT status label identified by numeric ID or name')]
class UpdateStatusLabelTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_name' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:deployable,pending,archived,undeployable',
            'color' => 'nullable|string',
            'notes' => 'nullable|string',
            'default_label' => 'nullable|boolean',
            'show_in_nav' => 'nullable|boolean',
        ]);

        $label = $this->resolveStatusLabel($request);

        if (! $label) {
            return Response::make(Response::error('Status label not found'));
        }

        if (! Gate::allows('update', $label)) {
            return Response::make(Response::error('Unauthorized'));
        }

        if ($request->filled('new_name')) {
            $label->name = $request->get('new_name');
        }

        if ($request->filled('type')) {
            $statusType = Statuslabel::getStatuslabelTypesForDB($request->get('type'));
            $label->deployable = $statusType['deployable'];
            $label->pending = $statusType['pending'];
            $label->archived = $statusType['archived'];
        }

        if ($request->filled('color')) {
            $label->color = $request->get('color');
        }

        if ($request->filled('notes')) {
            $label->notes = $request->get('notes');
        }

        if ($request->has('default_label')) {
            $label->default_label = $request->get('default_label');
        }

        if ($request->has('show_in_nav')) {
            $label->show_in_nav = $request->get('show_in_nav');
        }

        if ($label->save()) {
            return Response::make(
                Response::text('Status label '.$label->name.' updated successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Status label updated successfully',
                'id' => $label->id,
                'name' => $label->name,
                'type' => $label->getStatuslabelType(),
            ]);
        }

        return Response::make(Response::error('Update failed: '.$label->getErrors()->first()));
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
            'id' => $schema->number()->description('Numeric ID to identify the status label'),
            'name' => $schema->string()->description('Name to identify the status label'),
            'new_name' => $schema->string()->description('New name (renames the status label)'),
            'type' => $schema->string()->description('New type: deployable, pending, archived, or undeployable'),
            'color' => $schema->string()->description('Display color in #RRGGBB format'),
            'notes' => $schema->string()->description('Notes'),
            'default_label' => $schema->boolean()->description('Whether this is the default label'),
            'show_in_nav' => $schema->boolean()->description('Whether to show in navigation'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the status label'),
            'name' => $schema->string()->description('Name of the status label'),
            'type' => $schema->string()->description('Type of the status label'),
        ];
    }
}
