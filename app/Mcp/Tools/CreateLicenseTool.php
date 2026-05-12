<?php

namespace App\Mcp\Tools;

use App\Models\Company;
use App\Models\License;
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

#[Name('create_license')]
#[Title('Create License')]
#[Description('Create a new Snipe-IT software license')]
class CreateLicenseTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', License::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'seats' => 'required|integer|min:1',
                'category_id' => 'required|integer|exists:categories,id',
                'serial' => 'nullable|string|max:255',
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
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $license = new License;
        $license->fill($request->only([
            'name', 'seats', 'category_id', 'serial', 'manufacturer_id',
            'supplier_id', 'purchase_date', 'purchase_cost', 'purchase_order',
            'order_number', 'expiration_date', 'termination_date',
            'license_name', 'license_email', 'maintained', 'reassignable',
            'notes', 'min_amt',
        ]));

        $license->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        $license->created_by = auth()->id();

        if ($license->save()) {
            return Response::make(
                Response::text(trans('mcp.license_created', ['name' => $license->name]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.license_created', ['name' => $license->name]),
                'id' => $license->id,
                'name' => $license->name,
                'seats' => $license->seats,
                'category_id' => $license->category_id,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $license->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('License name (required)'),
            'seats' => $schema->number()->description('Number of seats (required, min 1)'),
            'category_id' => $schema->number()->description('Category ID — must be a license category (required)'),
            'serial' => $schema->string()->description('Product key / serial number'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID'),
            'supplier_id' => $schema->number()->description('Supplier ID'),
            'company_id' => $schema->number()->description('Company ID (defaults to the authenticated user\'s company)'),
            'purchase_date' => $schema->string()->description('Purchase date (YYYY-MM-DD)'),
            'purchase_cost' => $schema->number()->description('Purchase cost'),
            'purchase_order' => $schema->string()->description('Purchase order number'),
            'order_number' => $schema->string()->description('Order number'),
            'expiration_date' => $schema->string()->description('License expiration date (YYYY-MM-DD)'),
            'termination_date' => $schema->string()->description('License termination date (YYYY-MM-DD)'),
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
            'success' => $schema->boolean()->description('True if the license was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new license'),
            'name' => $schema->string()->description('Name of the new license'),
            'seats' => $schema->number()->description('Total seat count'),
            'category_id' => $schema->number()->description('Category ID'),
        ];
    }
}
