<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Category;
use App\Models\Company;
use App\Models\License;
use App\Models\Manufacturer;
use App\Models\Supplier;
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

#[Name('create_license')]
#[Title('Create License')]
#[Description('Create a new Snipe-IT software license')]
class CreateLicenseTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', License::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        // Resolve name-based FK inputs to numeric IDs before validation
        // so the downstream rules + fill see a consistent shape.
        // Lookups run through the model's normal global scopes, so a
        // non-superuser in a locked-down FMCS install gets a not-found
        // error for a name that only exists in a tenant they cannot
        // see, matching the UI's "that record does not exist" posture.
        $fkMap = [
            ['category_id', 'category_name', Category::class],
            ['manufacturer_id', 'manufacturer_name', Manufacturer::class],
            ['supplier_id', 'supplier_name', Supplier::class],
            ['company_id', 'company_name', Company::class],
        ];

        $resolvedFks = [];
        foreach ($fkMap as [$idField, $nameField, $modelClass]) {
            $resolved = $this->resolveFkByName($request, $idField, $nameField, $modelClass);
            if ($this->fkResolutionFailed($resolved)) {
                return Response::make(Response::error($this->fkErrorMessage($resolved)));
            }
            if ($resolved !== null) {
                $resolvedFks[$idField] = $resolved;
            }
        }

        $input = array_merge($request->all(), $resolvedFks);

        $license = new License;
        try {
            $validated = Validator::make($input, $license->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $license->fill(array_merge($input, $validated));
        $license->company_id = Company::getIdForCurrentUser($input['company_id'] ?? null);
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
            'category_id' => $schema->number()->description('Category ID, must be a license category (required). Alternative: category_name.'),
            'category_name' => $schema->string()->description('Category name. Resolved to an ID server-side.'),
            'serial' => $schema->string()->description('Product key / serial number'),
            'manufacturer_id' => $schema->number()->description('Manufacturer ID. Alternative: manufacturer_name.'),
            'manufacturer_name' => $schema->string()->description('Manufacturer name. Resolved to an ID server-side.'),
            'supplier_id' => $schema->number()->description('Supplier ID. Alternative: supplier_name.'),
            'supplier_name' => $schema->string()->description('Supplier name. Resolved to an ID server-side.'),
            'company_id' => $schema->number()->description('Company ID (defaults to the authenticated user\'s company). Alternative: company_name.'),
            'company_name' => $schema->string()->description('Company name. Resolved to an ID server-side.'),
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
