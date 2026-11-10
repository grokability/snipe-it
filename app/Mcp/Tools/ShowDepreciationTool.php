<?php

namespace App\Mcp\Tools;

use App\Models\Depreciation;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('show_depreciation')]
#[Title('Show Depreciation Details')]
#[Description('Look up a single Snipe-IT depreciation schedule by numeric ID or name and return its full details')]
class ShowDepreciationTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $depreciation = $this->resolveDepreciation($request);

        if ($depreciation === false) {
            return Response::make(Response::error('Please provide an id or name'));
        }

        if (! $depreciation) {
            return Response::make(Response::error('Depreciation not found'));
        }

        if (! Gate::allows('view', $depreciation)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $depreciation->loadCount('models as models_count');

        return Response::make(
            Response::text('Depreciation '.$depreciation->name.' found')
        )->withStructuredContent([
            'id' => $depreciation->id,
            'name' => $depreciation->name,
            'months' => $depreciation->months,
            'models_count' => $depreciation->models_count,
            'created_at' => $depreciation->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $depreciation->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }

    private function resolveDepreciation(Request $request): Depreciation|false|null
    {
        if ($request->filled('id')) {
            return Depreciation::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return Depreciation::where('name', $request->get('name'))->first();
        }

        return false;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the depreciation to look up'),
            'name' => $schema->string()->description('Name of the depreciation to look up'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric depreciation ID'),
            'name' => $schema->string()->description('Depreciation name'),
            'months' => $schema->number()->description('Depreciation period in months'),
            'models_count' => $schema->number()->description('Number of asset models using this depreciation'),
            'created_at' => $schema->string()->description('Creation timestamp'),
            'updated_at' => $schema->string()->description('Last update timestamp'),
        ];
    }
}
