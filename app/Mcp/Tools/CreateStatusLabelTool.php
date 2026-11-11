<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Enums\StatusLabelType;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Statuslabel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', Statuslabel::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $statuslabel = new Statuslabel;

        // Validate the semantic `type` up front against the
        // StatusLabelType enum, then translate it to the three
        // deployable/pending/archived boolean columns before running
        // the model-level validator (whose rules require all three).
        try {
            $typeValidated = Validator::make($request->all(), [
                'type' => ['required', 'string', Rule::enum(StatusLabelType::class)],
            ])->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $input = array_merge(
            $request->all(),
            StatusLabelType::from($typeValidated['type'])->toDbColumns(),
        );

        try {
            $validated = Validator::make($input, $statuslabel->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $statuslabel->fill(array_merge($input, $validated));
        $statuslabel->default_label = $request->get('default_label', 0);
        $statuslabel->show_in_nav = $request->get('show_in_nav', 0);

        if ($statuslabel->save()) {
            return Response::make(
                Response::text(trans('mcp.status_label_created', ['name' => $statuslabel->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.status_label_created', ['name' => $statuslabel->name]),
                'id' => $statuslabel->id,
                'name' => $statuslabel->name,
                'type' => $statuslabel->getStatuslabelType(),
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $statuslabel->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Status label name (required)'),
            'type' => $schema->string()->description('Status label type (required). One of: '.implode(', ', StatusLabelType::values())),
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
