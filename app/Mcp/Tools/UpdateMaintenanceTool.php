<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Mcp\Traits\SnipeMcpTool;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use App\Models\Supplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_maintenance')]
#[Title('Update Maintenance')]
#[Description('Update a maintenance record by ID. Only supplied fields change.')]
class UpdateMaintenanceTool extends Tool
{
    use ResolvesForeignKeyByName;
    use SnipeMcpTool;

    /**
     * @var list<array{0: string, 1: string, 2: class-string<\Illuminate\Database\Eloquent\Model>}>
     */
    protected array $foreignKeys = [
        ['maintenance_type_id', 'maintenance_type_name', MaintenanceType::class],
        ['supplier_id', 'supplier_name', Supplier::class],
    ];

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        $request->validate([
            'id' => 'required|integer',
        ]);

        $maintenance = Maintenance::find($request->get('id'));

        if (! $maintenance) {
            return Response::make(Response::error(trans('admin/maintenances/message.not_found')));
        }

        if (! Gate::allows('update', $maintenance)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $resolvedFks = $this->resolveForeignKeys($request);

        // Only pull in the fields the caller actually supplied so an
        // update to just `cost` or `notes` doesn't null out everything
        // else. title is the MCP-facing alias for the model's name
        // column. completed_at is intentionally not here: use
        // complete_maintenance to close out an open maintenance so
        // completed_by gets stamped and the already-completed guard
        // fires.
        $allowedKeys = [
            'title', 'name',
            'cost',
            'is_warranty',
            'start_date',
            'expected_completion_date',
            'notes',
            'url',
            'responsible_party_id',
        ];
        $input = array_intersect_key($request->all(), array_flip($allowedKeys));

        if ($request->filled('title')) {
            $input['name'] = $request->get('title');
            unset($input['title']);
        }
        if ($request->has('is_warranty')) {
            $input['is_warranty'] = $request->boolean('is_warranty');
        }

        $input = array_merge($input, $resolvedFks);

        if (empty($input)) {
            return Response::make(Response::error(trans('mcp.update_no_fields')));
        }

        $rules = array_intersect_key($maintenance->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $maintenance->fill(array_merge($input, $validated));

        if ($maintenance->save()) {
            $maintenance->refresh();

            return $this->structuredResponse(
                trans('admin/maintenances/message.edit.success'),
                [
                    'success' => true,
                    'message' => trans('admin/maintenances/message.edit.success'),
                    'id' => $maintenance->id,
                    'name' => $maintenance->name,
                    'item_type' => class_basename($maintenance->item_type),
                    'item_id' => $maintenance->item_id,
                    'asset_tag' => $maintenance->asset?->asset_tag,
                    'completed_at' => $maintenance->completed_at?->toDateTimeString(),
                ]
            );
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $maintenance->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the maintenance record')->required(),
            'title' => $schema->string()->description('New title/name'),
            'maintenance_type_id' => $schema->number()->description('New maintenance type id (or supply maintenance_type_name)'),
            'maintenance_type_name' => $schema->string()->description('New maintenance type name, resolved to an id server-side'),
            'supplier_id' => $schema->number()->description('New supplier id (or supply supplier_name)'),
            'supplier_name' => $schema->string()->description('New supplier name, resolved to an id server-side'),
            'is_warranty' => $schema->boolean()->description('Warranty coverage'),
            'cost' => $schema->number()->description('Cost in the install currency'),
            'start_date' => $schema->string()->description('Work start date (YYYY-MM-DD)'),
            'expected_completion_date' => $schema->string()->description('Planned completion date (YYYY-MM-DD). This is the PLANNED date, not the actual close-out. Use complete_maintenance to close one out.'),
            'notes' => $schema->string()->description('Free-form notes'),
            'url' => $schema->string()->description('Reference URL'),
            'responsible_party_id' => $schema->number()->description('Technician user ID'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the maintenance record'),
            'name' => $schema->string()->description('Name/title of the maintenance'),
            'item_type' => $schema->string()->description('Basename of the polymorphic parent model (usually Asset)'),
            'item_id' => $schema->number()->description('Numeric ID of the polymorphic parent'),
            'asset_tag' => $schema->string()->description('Asset tag of the parent asset')->nullable(),
            'completed_at' => $schema->string()->description('Completion timestamp if set')->nullable(),
        ];
    }
}
