<?php

namespace App\Mcp\Tools;

use App\Models\License;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $license = $this->resolveLicense($request);

        if (! $license) {
            return Response::make(Response::error(trans('mcp.license_not_found')));
        }

        if (! Gate::allows('delete', $license)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if ($license->assignedCount()->count() > 0) {
            return Response::make(Response::error(trans('mcp.license_has_seats_assigned')));
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
        if ($request->filled('id')) {
            return License::find($request->get('id'));
        }
        if ($request->filled('name')) {
            return License::where('name', $request->get('name'))->first();
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
