<?php

namespace App\Mcp\Tools;

use App\Models\Company;
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

#[Name('update_license')]
#[Title('Update License')]
#[Description('Update fields on a Snipe-IT license identified by numeric ID or name')]
class UpdateLicenseTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'new_name' => 'nullable|string|max:255',
            'seats' => 'nullable|integer|min:1',
            'serial' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'company_id' => 'nullable|integer|exists:companies,id',
            'purchase_date' => 'nullable|date_format:Y-m-d',
            'purchase_cost' => 'nullable|numeric|min:0',
            'purchase_order' => 'nullable|string|max:255',
            'order_number' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date_format:Y-m-d',
            'termination_date' => 'nullable|date_format:Y-m-d',
            'license_name' => 'nullable|string|max:255',
            'license_email' => 'nullable|email|max:255',
            'maintained' => 'nullable|boolean',
            'reassignable' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'min_amt' => 'nullable|integer|min:0',
        ]);

        $license = $this->resolveLicense($request);

        if (! $license) {
            return Response::make(Response::error(trans('mcp.license_not_found')));
        }

        if (! Gate::allows('update', $license)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        $updatable = [
            'seats', 'serial', 'category_id', 'manufacturer_id', 'supplier_id',
            'purchase_date', 'purchase_cost', 'purchase_order', 'order_number',
            'expiration_date', 'termination_date', 'license_name', 'license_email',
            'maintained', 'reassignable', 'notes', 'min_amt',
        ];

        foreach ($updatable as $field) {
            if ($request->filled($field)) {
                $license->{$field} = $request->get($field);
            }
        }

        if ($request->filled('new_name')) {
            $license->name = $request->get('new_name');
        }

        if ($request->filled('company_id')) {
            $license->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        }

        if ($license->save()) {
            return Response::make(
                Response::text(trans('mcp.license_updated', ['name' => $license->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.license_updated', ['name' => $license->name]),
                'id' => $license->id,
                'name' => $license->name,
                'seats' => $license->seats,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $license->getErrors()->first()])));
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
            'id' => $schema->number()->description('Numeric ID to identify the license'),
            'name' => $schema->string()->description('Name to identify the license'),
            'new_name' => $schema->string()->description('New name (renames the license)'),
            'seats' => $schema->number()->description('Total seat count'),
            'serial' => $schema->string()->description('Product key / serial number'),
            'category_id' => $schema->number()->description('Category ID'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
            'company_id' => $schema->number()->description('Company ID'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'purchase_cost' => $schema->number()->description('Purchase cost'),
            'purchase_order' => $schema->string()->description('Purchase order number'),
            'order_number' => $schema->string()->description('Order number'),
            'expiration_date' => $schema->string()->description('Expiration date (YYYY-MM-DD)'),
            'termination_date' => $schema->string()->description('Termination date (YYYY-MM-DD)'),
            'license_name' => $schema->string()->description('Name of the licensed user/organization'),
            'license_email' => $schema->string()->description('Email of the licensed user/organization'),
            'maintained' => $schema->boolean()->description('Whether the license is under maintenance'),
            'reassignable' => $schema->boolean()->description('Whether seats can be reassigned after checkin'),
            'notes' => $schema->string()->description('Notes'),
            'min_amt' => $schema->number()->description('Minimum seat threshold for alerts'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the license'),
            'name' => $schema->string()->description('Name of the license'),
            'seats' => $schema->number()->description('Total seat count'),
        ];
    }
}
