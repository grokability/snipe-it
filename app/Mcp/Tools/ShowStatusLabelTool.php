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

#[Name('show_status_label')]
#[Title('Show Status Label')]
#[Description('Show details of a Snipe-IT status label identified by numeric ID or name')]
class ShowStatusLabelTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $label = $this->resolveStatusLabel($request);

        if (! $label) {
            if (! $request->filled('id') && ! $request->filled('name')) {
                return Response::make(Response::error('Please provide an id or name'));
            }

            return Response::make(Response::error('Status label not found'));
        }

        if (! Gate::allows('view', $label)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $label->loadCount('assets as assets_count');

        return Response::make(
            Response::text('Status label: '.$label->name)
        )->withStructuredContent([
            'id' => $label->id,
            'name' => $label->name,
            'type' => $label->getStatuslabelType(),
            'color' => $label->color,
            'deployable' => $label->deployable,
            'pending' => $label->pending,
            'archived' => $label->archived,
            'assets_count' => $label->assets_count,
            'default_label' => $label->default_label,
            'show_in_nav' => $label->show_in_nav,
            'notes' => $label->notes,
            'created_at' => $label->created_at?->toISOString(),
            'updated_at' => $label->updated_at?->toISOString(),
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
            'id' => $schema->number()->description('Numeric ID of the status label to show'),
            'name' => $schema->string()->description('Name of the status label to show'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the status label'),
            'name' => $schema->string()->description('Status label name')->required(),
            'type' => $schema->string()->description('Status label type (deployable, pending, archived, undeployable)'),
            'color' => $schema->string()->description('Display color'),
            'deployable' => $schema->boolean()->description('Whether status is deployable'),
            'pending' => $schema->boolean()->description('Whether status is pending'),
            'archived' => $schema->boolean()->description('Whether status is archived'),
            'assets_count' => $schema->number()->description('Number of assets with this status'),
            'default_label' => $schema->boolean()->description('Whether this is the default label'),
            'show_in_nav' => $schema->boolean()->description('Whether to show in navigation'),
            'notes' => $schema->string()->description('Notes'),
            'created_at' => $schema->string()->description('Creation timestamp'),
            'updated_at' => $schema->string()->description('Last update timestamp'),
        ];
    }
}
