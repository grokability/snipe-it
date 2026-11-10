<?php

namespace App\Mcp\Tools;

use App\Models\Company;
use App\Models\Group;
use App\Models\User;
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

#[Name('create_user')]
#[Title('Create User')]
#[Description('Create a new Snipe-IT user account')]
class CreateUserTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        if (! Gate::allows('create', User::class)) {
            return Response::make(Response::error(trans('mcp.unauthorized')));
        }

        try {
            $request->validate([
                'first_name' => 'required|string|max:191',
                'last_name' => 'nullable|string|max:191',
                'username' => 'required|string|max:191',
                'email' => 'nullable|email|max:191',
                'password' => 'nullable|string|min:8',
                'employee_num' => 'nullable|string|max:191',
                'jobtitle' => 'nullable|string|max:191',
                'phone' => 'nullable|string|max:35',
                'mobile' => 'nullable|string|max:35',
                'company_id' => 'nullable|integer|exists:companies,id',
                'department_id' => 'nullable|integer|exists:departments,id',
                'location_id' => 'nullable|integer|exists:locations,id',
                'manager_id' => 'nullable|integer|exists:users,id',
                'activated' => 'nullable|boolean',
                'notes' => 'nullable|string',
                'start_date' => 'nullable|date_format:Y-m-d',
                'end_date' => 'nullable|date_format:Y-m-d',
                'vip' => 'nullable|boolean',
                'remote' => 'nullable|boolean',
                'website' => 'nullable|url|max:191',
                'address' => 'nullable|string|max:191',
                'city' => 'nullable|string|max:191',
                'state' => 'nullable|string|max:191',
                'country' => 'nullable|string|max:191',
                'zip' => 'nullable|string|max:10',
                'group_ids' => 'nullable|array',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        if (User::where('username', $request->get('username'))->exists()) {
            return Response::make(Response::error(trans('mcp.username_taken', ['username' => $request->get('username')])));
        }

        $user = new User;
        $user->fill($request->only([
            'first_name', 'last_name', 'username', 'email', 'employee_num',
            'jobtitle', 'phone', 'mobile', 'department_id', 'location_id',
            'manager_id', 'notes', 'start_date', 'end_date', 'vip', 'remote',
            'website', 'address', 'city', 'state', 'country', 'zip',
        ]));

        $user->activated = $request->filled('activated') ? (bool) $request->get('activated') : true;
        $user->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        $user->created_by = auth()->id();

        if ($request->filled('password')) {
            $user->password = bcrypt($request->get('password'));
        } else {
            $user->password = $user->noPassword();
        }

        if ($user->save()) {
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
