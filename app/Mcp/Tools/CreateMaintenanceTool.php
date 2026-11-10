<?php

namespace App\Mcp\Tools;

use App\Models\Asset;
use App\Models\Maintenance;
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

#[Name('create_maintenance')]
#[Title('Create Maintenance')]
#[Description('Create a new asset maintenance record')]
class CreateMaintenanceTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('update', Asset::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        try {
            $request->validate([
                'asset_id' => 'required|integer|exists:assets,id',
                'title' => 'required|string|max:255',
                'asset_maintenance_type' => 'nullable|string|max:255',
                'supplier_id' => 'nullable|integer|exists:suppliers,id',
                'is_warranty' => 'nullable|boolean',
                'cost' => 'nullable|numeric|min:0',
                'start_date' => 'nullable|date_format:Y-m-d',
                'completion_date' => 'nullable|date_format:Y-m-d',
                'notes' => 'nullable|string',
                'user_id' => 'nullable|integer|exists:users,id',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $maintenance = new Maintenance;
        $maintenance->asset_id = $request->get('asset_id');
        $maintenance->name = $request->get('title');
        $maintenance->asset_maintenance_type = $request->get('asset_maintenance_type', 'Maintenance');
        $maintenance->start_date = $request->filled('start_date') ? $request->get('start_date') : now()->format('Y-m-d');
        $maintenance->created_by = auth()->id();
        $maintenance->is_warranty = 0;

        foreach (['supplier_id', 'is_warranty', 'cost', 'completion_date', 'notes', 'user_id'] as $field) {
            if ($request->filled($field)) {
                $maintenance->{$field} = $request->get($field);
            }
        }

        if ($maintenance->save()) {
            $maintenance->load('asset');

            return Response::make(
                Response::text('Maintenance '.$maintenance->name.' created successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'Maintenance created successfully',
                'id' => $maintenance->id,
                'title' => $maintenance->name,
                'asset_id' => $maintenance->asset_id,
                'asset_tag' => $maintenance->asset?->asset_tag,
            ]);
        }

        return Response::make(Response::error('Create failed: '.$maintenance->getErrors()->first()));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'asset_id' => $schema->number()->description('Asset ID the maintenance is for (required)'),
            'title' => $schema->string()->description('Maintenance title/name (required)'),
            'asset_maintenance_type' => $schema->string()->description('Type of maintenance (e.g. maintenance, repair, upgrade)'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
            'is_warranty' => $schema->boolean()->description('Whether this is a warranty maintenance'),
            'cost' => $schema->number()->description('Cost of the maintenance'),
            'start_date' => $schema->string()->description('Start date (YYYY-MM-DD, defaults to today)'),
            'completion_date' => $schema->string()->description('Completion date (YYYY-MM-DD)'),
            'notes' => $schema->string()->description('Notes about the maintenance'),
            'user_id' => $schema->number()->description('Technician user ID'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the maintenance was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new maintenance record'),
            'title' => $schema->string()->description('Title of the maintenance'),
            'asset_id' => $schema->number()->description('Asset ID'),
            'asset_tag' => $schema->string()->description('Asset tag'),
        ];
    }
}
