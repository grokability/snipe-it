<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\License;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('delete_license')]
#[Title('Delete License')]
#[Description('Soft-delete a Snipe-IT license. The license must have no seats currently assigned to users or assets.')]
class DeleteLicenseTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Delete)) {
            return $denied;
        }

        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $license = $this->resolveLicense($request);

        if (! $license) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.license')])));
        }

        if (! $license->isDeletable()) {
            return Response::make(Response::error(trans('admin/licenses/message.delete.bulk_checkout_warning', ['license_name' => $license->name])));
        }

        $name = $license->name;

        DB::table('license_seats')
            ->where('license_id', $license->id)
            ->update(['assigned_to' => null, 'asset_id' => null]);

        $license->licenseseats()->delete();
        $license->delete();

        return Response::make(
            Response::text(trans('mcp.license_deleted', ['name' => $name]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.license_deleted', ['name' => $name]),
            'name' => $name,
        ]);
    }

    private function resolveLicense(Request $request): ?License
    {
        $query = License::withCount('freeSeats as free_seats_count');

        if ($request->filled('id')) {
            return $query->find($request->get('id'));
        }
        if ($request->filled('name')) {
            return $query->where('name', $request->get('name'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric ID of the license to delete'),
            'name' => $schema->string()->description('Name of the license to delete'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'name' => $schema->string()->description('Name of the deleted license'),
        ];
    }
}
