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

#[Name('update_status_label')]
#[Title('Update Status Label')]
#[Description('Update fields on a Snipe-IT status label identified by numeric ID or name')]
class UpdateStatusLabelTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        try {
            $request->validate([
                'id' => 'nullable|integer',
                'name' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $label = $this->resolveStatusLabel($request);

        if (! $label || ! Gate::allows('update', $label)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.status_label')])));
        }

        $input = $request->all();
        if ($request->filled('new_name')) {
            $input['name'] = $request->get('new_name');
        }

        // Translate the semantic `type` to the three deployable /
        // pending / archived boolean columns the model stores. Needs
        // to happen before validation so the model-level rules see
        // the columns populated.
        if ($request->filled('type')) {
            try {
                $typeValidated = Validator::make($request->all(), [
                    'type' => ['required', 'string', Rule::enum(StatusLabelType::class)],
                ])->validate();
            } catch (ValidationException $e) {
                return Response::make(Response::error($e->validator->errors()->first()));
            }
            $input = array_merge($input, StatusLabelType::from($typeValidated['type'])->toDbColumns());
        }

        $rules = array_intersect_key($label->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $label->fill(array_merge($input, $validated));

        if ($request->has('default_label')) {
            $label->default_label = $request->get('default_label');
        }

        if ($request->has('show_in_nav')) {
            $label->show_in_nav = $request->get('show_in_nav');
        }

        if ($label->save()) {
            return Response::make(
                Response::text(trans('mcp.status_label_updated', ['name' => $label->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.status_label_updated', ['name' => $label->name]),
                'id' => $label->id,
                'name' => $label->name,
                'type' => $label->getStatuslabelType(),
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $label->getErrors()->first()])));
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
            'type' => $schema->string()->description('New type. One of: '.implode(', ', StatusLabelType::values())),
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
