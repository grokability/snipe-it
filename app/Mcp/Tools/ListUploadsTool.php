<?php

namespace App\Mcp\Tools;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\License;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_uploads')]
#[Title('List Uploads')]
#[Description('List files uploaded to a Snipe-IT object. Supported types: accessories, assets, companies, components, consumables, departments, licenses, locations, maintenances, models, suppliers, users')]
class ListUploadsTool extends Tool
{
    private const TYPE_MAP = [
        'accessories' => Accessory::class,
        'assets' => Asset::class,
        'companies' => Company::class,
        'components' => Component::class,
        'consumables' => Consumable::class,
        'departments' => Department::class,
        'licenses' => License::class,
        'locations' => Location::class,
        'maintenances' => Maintenance::class,
        'models' => AssetModel::class,
        'suppliers' => Supplier::class,
        'users' => User::class,
    ];

    public function handle(Request $request): ResponseFactory
    {
        $validTypes = implode(',', array_keys(self::TYPE_MAP));

        $request->validate([
            'object_type' => 'required|string|in:'.$validTypes,
            'id' => 'required|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:500',
            'offset' => 'nullable|integer|min:0',
        ]);

        $objectType = $request->get('object_type');
        $modelClass = self::TYPE_MAP[$objectType];

        $object = $modelClass::withTrashed()->find($request->get('id'));

        if (! $object) {
            return Response::make(Response::error(trans('mcp.object_not_found', ['type' => $objectType])));
        }

        if (! Gate::allows('files', $object)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $limit = $request->filled('limit') ? (int) $request->get('limit') : 25;
        $offset = $request->filled('offset') ? (int) $request->get('offset') : 0;

        $query = $object->uploads()->with('adminuser');

        $total = (clone $query)->count();
        $uploads = $query->skip($offset)->take($limit)->orderBy('created_at', 'desc')->get();

        $files = $uploads->map(fn ($file) => [
            'id' => $file->id,
            'filename' => $file->filename,
            'url' => $file->uploads_file_url(),
            'note' => $file->note,
            'created_by' => $file->adminuser ? [
                'id' => $file->adminuser->id,
                'username' => $file->adminuser->username,
            ] : null,
            'created_at' => $file->created_at?->toISOString(),
            'exists_on_disk' => Storage::exists($file->uploads_file_path()),
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.list_uploads', [
                'total' => $total,
                'count' => count($files),
                'type' => $objectType,
            ]))
        )->withStructuredContent([
            'object_type' => $objectType,
            'object_id' => $object->id,
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'files' => $files,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'object_type' => $schema->string()->description('Type of object: accessories, assets, companies, components, consumables, departments, licenses, locations, maintenances, models, suppliers, users'),
            'id' => $schema->number()->description('Numeric ID of the object'),
            'limit' => $schema->number()->description('Number of results to return (default: 25, max: 500)'),
            'offset' => $schema->number()->description('Number of results to skip for pagination (default: 0)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'object_type' => $schema->string()->description('Type of object queried')->required(),
            'object_id' => $schema->number()->description('ID of the object queried')->required(),
            'total' => $schema->number()->description('Total number of uploaded files')->required(),
            'offset' => $schema->number()->description('Current pagination offset')->required(),
            'limit' => $schema->number()->description('Results per page')->required(),
            'files' => $schema->array()->description('List of uploaded files'),
        ];
    }
}
