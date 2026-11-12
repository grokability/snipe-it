<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Company;
use App\Models\Department;
use App\Models\Group;
use App\Models\Location;
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

#[Name('update_user')]
#[Title('Update User')]
#[Description('Update fields on a Snipe-IT user identified by numeric ID, username, or email address')]
class UpdateUserTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        try {
            $request->validate([
                'id' => 'nullable|integer',
                'username' => 'nullable|string|max:191',
                'email' => 'nullable|string|max:191',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $user = $this->resolveUser($request);

        if (! $user || ! Gate::allows('update', $user)) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.user')])));
        }

        // Gate auth-field edits BEFORE building input, so an attempt to
        // change username/email/password/activated without permission
        // fails cleanly rather than silently dropping the fields.
        $canEditAuthFields = Gate::allows('canEditAuthFields', $user);
        $wantsAuthEdit = $request->filled('new_username') || $request->filled('new_email')
            || $request->filled('password') || $request->has('activated');

        if ($wantsAuthEdit && ! $canEditAuthFields) {
            return Response::make(Response::error(trans('mcp.cannot_edit_auth_fields')));
        }

        $fkMap = [
            ['company_id', 'company_name', Company::class],
            ['department_id', 'department_name', Department::class],
            ['location_id', 'location_name', Location::class],
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

        // Map aliases (new_username -> username, new_email -> email) and
        // strip the lookup fields so they don't get validated as updates.
        $input = array_merge($request->all(), $resolvedFks);
        unset($input['new_username'], $input['new_email'], $input['username'], $input['email']);
        if ($request->filled('new_username')) {
            $input['username'] = $request->get('new_username');
        }
        if ($request->filled('new_email')) {
            $input['email'] = $request->get('new_email');
        }

        // Intersect with model rules so partial updates only validate
        // what the caller actually sent. Watson's getPreparedRules
        // injects the user's id for unique_undeleted so the user can
        // keep their own username / email through the update.
        $rules = array_intersect_key($user->getPreparedRules(), $input);
        try {
            $validated = Validator::make($input, $rules)->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        // Hash the validated plaintext password before fill. Everything
        // else in $validated flows straight through.
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->fill(array_merge($input, $validated));

        if (! empty($input['company_id'])) {
            $user->company_id = Company::getIdForCurrentUser($input['company_id']);
        }

        if ($user->save()) {
            $groupIds = null;
            if ($request->filled('group_ids') && auth()->user()->isSuperUser()) {
                $groupIds = Group::whereIn('id', $request->get('group_ids'))->pluck('id')->all();
                $user->groups()->sync($groupIds);
            } elseif ($request->filled('group_ids')) {
                return Response::make(Response::error(trans('mcp.superadmin_required_for_groups')));
            }

            $content = [
                'success' => true,
                'message' => trans('mcp.user_updated', ['username' => $user->username]),
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ];
            if ($groupIds !== null) {
                $content['group_ids'] = $groupIds;
            }

            return Response::make(
                Response::text(trans('mcp.user_updated', ['username' => $user->username]))
            )->withStructuredContent($content);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $user->getErrors()->first()])));
    }

    private function resolveUser(Request $request): ?User
    {
        if ($request->filled('id')) {
            return User::find($request->get('id'));
        }
        if ($request->filled('username')) {
            return User::where('username', $request->get('username'))->first();
        }
        if ($request->filled('email')) {
            return User::where('email', $request->get('email'))->first();
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric user ID to identify the user'),
            'username' => $schema->string()->description('Username to identify the user'),
            'email' => $schema->string()->description('Email address to identify the user'),
            'new_username' => $schema->string()->description('New username (renames the username itself)'),
            'new_email' => $schema->string()->description('New email address'),
            'first_name' => $schema->string()->description('First name'),
            'last_name' => $schema->string()->description('Last name'),
            'password' => $schema->string()->description('New password (min 8 characters)'),
            'employee_num' => $schema->string()->description('Employee number'),
            'jobtitle' => $schema->string()->description('Job title'),
            'phone' => $schema->string()->description('Phone number'),
            'mobile' => $schema->string()->description('Mobile number'),
            'company_id' => $schema->number()->description('Company ID. Alternative: company_name.'),
            'company_name' => $schema->string()->description('Company name. Resolved to an ID server-side.'),
            'department_id' => $schema->number()->description('Department ID. Alternative: department_name.'),
            'department_name' => $schema->string()->description('Department name. Resolved to an ID server-side.'),
            'location_id' => $schema->number()->description('Location ID. Alternative: location_name.'),
            'location_name' => $schema->string()->description('Location name. Resolved to an ID server-side.'),
            'manager_id' => $schema->number()->description('Manager user ID'),
            'activated' => $schema->boolean()->description('Whether the account is active'),
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
            'group_ids' => $schema->array()->description('Array of permission group IDs to assign (requires superadmin). Replaces all existing group memberships. Example: [1, 3]'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'id' => $schema->number()->description('Numeric ID of the updated user'),
            'username' => $schema->string()->description('Username of the updated user'),
            'email' => $schema->string()->description('Email of the updated user'),
        ];
    }
}
