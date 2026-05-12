<?php

namespace App\Mcp\Tools;

use App\Models\Depreciation;
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

#[Name('create_depreciation')]
#[Title('Create Depreciation')]
#[Description('Create a new Snipe-IT depreciation schedule')]
class CreateDepreciationTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', Depreciation::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'months' => 'required|integer|min:1|max:3600',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $depreciation = new Depreciation;
        $depreciation->name = $request->get('name');
        $depreciation->months = $request->get('months');

        if ($depreciation->save()) {
            return Response::make(
                Response::text(trans('mcp.depreciation_created', ['name' => $depreciation->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.depreciation_created', ['name' => $depreciation->name]),
                'id' => $depreciation->id,
                'name' => $depreciation->name,
                'months' => $depreciation->months,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $depreciation->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Depreciation name (required)'),
            'months' => $schema->number()->description('Depreciation period in months (required, 1-3600)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the depreciation was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new depreciation'),
            'name' => $schema->string()->description('Name of the new depreciation'),
            'months' => $schema->number()->description('Depreciation period in months'),
        ];
    }
}
