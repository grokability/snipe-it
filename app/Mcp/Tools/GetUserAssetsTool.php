<?php

namespace App\Mcp\Tools;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_user_assets')]
#[Title('Get User Assets')]
#[Description('Return all assets currently checked out to a Snipe-IT user')]
class GetUserAssetsTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('view', User::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        if (! Gate::allows('view', Asset::class)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $request->validate([
            'id' => 'required|integer',
        ]);

        $user = User::find($request->get('id'));

        if (! $user) {
            return Response::make(Response::error('User not found'));
        }

        $assets = Asset::where('assigned_to', $user->id)
            ->where('assigned_type', User::class)
            ->with('model', 'status', 'location')
            ->get();

        $data = $assets->map(fn ($asset) => [
            'id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
            'name' => $asset->name,
            'serial' => $asset->serial,
            'model' => $asset->model?->name,
            'status' => $asset->status?->name,
        ])->values()->all();

        return Response::make(
            Response::text('Found '.count($data).' assets for user '.$user->username)
        )->withStructuredContent([
            'user_id' => $user->id,
            'username' => $user->username,
            'total' => count($data),
            'assets' => $data,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the user whose assets should be listed (required)'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'user_id' => $schema->number()->description('Numeric ID of the user')->required(),
            'username' => $schema->string()->description('Username of the user')->required(),
            'total' => $schema->number()->description('Total number of assets checked out to the user')->required(),
            'assets' => $schema->array()->description('List of checked-out assets'),
        ];
    }
}
