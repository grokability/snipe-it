<?php

namespace App\Mcp\Tools;

use App\Models\Statuslabel;
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

#[Name('create_status_label')]
#[Title('Create Status Label')]
#[Description('Create a new Snipe-IT status label')]
class CreateStatusLabelTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', Statuslabel::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:deployable,pending,archived,undeployable',
                'color' => 'nullable|string',
                'notes' => 'nullable|string',
                'default_label' => 'nullable|boolean',
                'show_in_nav' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $statuslabel = new Statuslabel;
        $statuslabel->name = $request->get('name');

        $statusType = Statuslabel::getStatuslabelTypesForDB($request->get('type'));
        $statuslabel->deployable = $statusType['deployable'];
        $statuslabel->pending = $statusType['pending'];
        $statuslabel->archived = $statusType['archived'];

        if ($request->filled('color')) {
            $statuslabel->color = $request->get('color');
        }
        if ($request->filled('notes')) {
            $statuslabel->notes = $request->get('notes');
        }
        $statuslabel->default_label = $request->get('default_label', 0);
        $statuslabel->show_in_nav = $request->get('show_in_nav', 0);

        if ($statuslabel->save()) {
            return Response::make(
                Response::text('Status label '.$statuslabel->name.' created successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Status label created successfully',
                'id' => $statuslabel->id,
                'name' => $statuslabel->name,
                'type' => $statuslabel->getStatuslabelType(),
            ]);
        }

        return Response::make(Response::error('Create failed: '.$statuslabel->getErrors()->first()));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Status label name (required)'),
            'type' => $schema->string()->description('Status label type: deployable, pending, archived, or undeployable (required)'),
            'color' => $schema->string()->description('Display color in #RRGGBB format'),
            'notes' => $schema->string()->description('Notes'),
            'default_label' => $schema->boolean()->description('Whether this is the default label'),
            'show_in_nav' => $schema->boolean()->description('Whether to show in navigation'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the status label was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new status label'),
            'name' => $schema->string()->description('Name of the new status label'),
            'type' => $schema->string()->description('Type of the new status label'),
        ];
    }
}
