<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Company;
use App\Models\License;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        try {
            $request->validate([
                'id' => 'nullable|integer',
                'name' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $license = $this->resolveLicense($request);

        if (! $license || ! Gate::allows('update', $license)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.license')])));
        }

        $input = $request->all();
        if ($request->filled('new_name')) {
            $input['name'] = $request->get('new_name');
        }

        $rules = array_intersect_key($license->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $license->fill(array_merge($input, $validated));

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
