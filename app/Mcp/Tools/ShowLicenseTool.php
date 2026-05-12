<?php

namespace App\Mcp\Tools;

use App\Models\License;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('show_license')]
#[Title('Show License Details')]
#[Description('Look up a single Snipe-IT license by numeric ID or name and return its full details including seat counts')]
class ShowLicenseTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        if ($request->filled('id')) {
            $license = License::with('company', 'manufacturer', 'supplier', 'category')
                ->withCount('freeSeats as free_seats_count')
                ->find($request->get('id'));
        } elseif ($request->filled('name')) {
            $license = License::with('company', 'manufacturer', 'supplier', 'category')
                ->withCount('freeSeats as free_seats_count')
                ->where('name', $request->get('name'))
                ->first();
        } else {
            return Response::make(Response::error(trans('mcp.id_or_name_required')));
        }

        if (! $license) {
            return Response::make(Response::error(trans('mcp.license_not_found')));
        }

        if (! Gate::allows('view', $license)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $assignedCount = $license->assignedCount()->count();

        return Response::make(
            Response::text(trans('mcp.license_found', ['name' => $license->name]))
        )->withStructuredContent([
            'id' => $license->id,
            'name' => $license->name,
            'serial' => $license->serial,
            'seats' => $license->seats,
            'free_seats' => $license->free_seats_count,
            'assigned_seats' => $assignedCount,
            'category' => $license->category?->name,
            'category_id' => $license->category_id,
            'manufacturer' => $license->manufacturer?->name,
            'manufacturer_id' => $license->manufacturer_id,
            'company' => $license->company?->name,
            'company_id' => $license->company_id,
            'supplier' => $license->supplier?->name,
            'supplier_id' => $license->supplier_id,
            'license_name' => $license->license_name,
            'license_email' => $license->license_email,
            'maintained' => (bool) $license->maintained,
            'reassignable' => (bool) $license->reassignable,
            'purchase_date' => $license->purchase_date?->format('Y-m-d'),
            'purchase_cost' => $license->purchase_cost,
            'purchase_order' => $license->purchase_order,
            'order_number' => $license->order_number,
            'expiration_date' => $license->expiration_date?->format('Y-m-d'),
            'termination_date' => $license->termination_date?->format('Y-m-d'),
            'notes' => $license->notes,
            'created_at' => $license->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $license->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric license ID'),
            'name' => $schema->string()->description('License name to look up'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric license ID')->required(),
            'name' => $schema->string()->description('License name')->required(),
            'seats' => $schema->number()->description('Total seat count'),
            'free_seats' => $schema->number()->description('Number of available (unassigned) seats'),
            'assigned_seats' => $schema->number()->description('Number of currently assigned seats'),
        ];
    }
}
