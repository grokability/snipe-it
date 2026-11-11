<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Models\Company;
use App\Models\Group;
use App\Models\User;
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

#[Name('create_user')]
#[Title('Create User')]
#[Description('Create a new Snipe-IT user account')]
class CreateUserTool extends Tool
{
    use HasMcpScope;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        if (! Gate::allows('create', User::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        if (User::where('username', $request->get('username'))->exists()) {
            return Response::make(Response::error(trans('mcp.username_taken', ['username' => $request->get('username')])));
        }

        $user = new User;

        // User's $rules marks password as required|min:8. The MCP tool
        // allows callers to omit password (account gets the noPassword
        // sentinel so it still passes model validation on save). Pre-
        // populate password in $input so Validator::make catches short
        // plaintexts before we hash, then overwrite the validated value
        // with the bcrypt hash after validation passes.
        $input = $request->all();
        if (! $request->filled('password')) {
            $input['password'] = $user->noPassword();
        }

        try {
            $validated = Validator::make($input, $user->getPreparedRules())->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->fill(array_merge($input, $validated));
        $user->activated = $request->filled('activated') ? (int) $request->boolean('activated') : 1;
        $user->created_by = auth()->id();

        if ($user->save()) {
            // Company membership is pivot-based (company_user) after the
            // parent-company feature landed. users.company_id was renamed
            // to legacy_company_id as a non-authoritative mirror. Attach
            // the resolved company via the pivot, then sync the legacy
            // mirror so FMCS checks that still read either can agree.
            $companyId = Company::getIdForCurrentUser($request->get('company_id'));
            if ($companyId !== null) {
                $user->companies()->syncWithoutDetaching([$companyId]);
                $user->syncLegacyCompanyIdMirror();
            }

            $groupIds = [];
            if ($request->filled('group_ids') && auth()->user()->isSuperUser()) {
                $groupIds = Group::whereIn('id', $request->get('group_ids'))->pluck('id')->all();
                $user->groups()->sync($groupIds);
            } elseif ($request->filled('group_ids')) {
                return Response::make(Response::error(trans('mcp.superadmin_required_for_groups')));
            }

            return Response::make(
                Response::text(trans('mcp.user_created', ['username' => $user->username]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.user_created', ['username' => $user->username]),
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'group_ids' => $groupIds,
            ]);
        }

        return Response::make(Response::error(trans('mcp.create_failed', ['error' => $user->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'first_name' => $schema->string()->description('First name (required)'),
            'last_name' => $schema->string()->description('Last name'),
            'username' => $schema->string()->description('Username (required, must be unique)'),
            'email' => $schema->string()->description('Email address'),
            'password' => $schema->string()->description('Password (min 8 characters; if omitted, account will have no password set)'),
            'employee_num' => $schema->string()->description('Employee number'),
            'jobtitle' => $schema->string()->description('Job title'),
            'phone' => $schema->string()->description('Phone number'),
            'mobile' => $schema->string()->description('Mobile number'),
            'company_id' => $schema->number()->description('Company ID (defaults to the authenticated user\'s company)'),
            'department_id' => $schema->number()->description('Department ID'),
            'location_id' => $schema->number()->description('Location ID'),
            'manager_id' => $schema->number()->description('Manager user ID'),
            'activated' => $schema->boolean()->description('Whether the account is active (default: true)'),
            'notes' => $schema->string()->description('Notes'),
            'start_date' => $schema->string()->description('Employment start date (YYYY-MM-DD)'),
            'end_date' => $schema->string()->description('Employment end date (YYYY-MM-DD)'),
            'vip' => $schema->boolean()->description('Mark user as VIP'),
            'remote' => $schema->boolean()->description('Mark user as remote'),
            'website' => $schema->string()->description('Website URL'),
            'address' => $schema->string()->description('Street address'),
            'city' => $schema->string()->description('City'),
            'state' => $schema->string()->description('State/province'),
            'country' => $schema->string()->description('Country'),
            'zip' => $schema->string()->description('Postal/ZIP code'),
            'group_ids' => $schema->array()->description('Array of permission group IDs to assign (requires superadmin). Example: [1, 3]'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the user was created'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the new user'),
            'username' => $schema->string()->description('Username of the new user'),
            'email' => $schema->string()->description('Email of the new user'),
            'first_name' => $schema->string()->description('First name'),
            'last_name' => $schema->string()->description('Last name'),
        ];
    }
}
