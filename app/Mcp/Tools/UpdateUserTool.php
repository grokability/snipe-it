<?php

namespace App\Mcp\Tools;

use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Gate;
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
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'username' => 'nullable|string|max:191',
            'email' => 'nullable|string|max:191',
            'new_username' => 'nullable|string|max:191',
            'new_email' => 'nullable|email|max:191',
            'first_name' => 'nullable|string|max:191',
            'last_name' => 'nullable|string|max:191',
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
        ]);

        $user = $this->resolveUser($request);

        if (! $user) {
            return Response::make(Response::error('User not found'));
        }

        if (! Gate::allows('update', $user)) {
            return Response::make(Response::error('Unauthorized'));
        }

        $updatable = [
            'first_name', 'last_name', 'employee_num', 'jobtitle',
            'phone', 'mobile', 'department_id', 'location_id', 'manager_id',
            'notes', 'start_date', 'end_date',
            'vip', 'remote', 'website', 'address', 'city', 'state', 'country', 'zip',
        ];

        foreach ($updatable as $field) {
            if ($request->filled($field)) {
                $user->{$field} = $request->get($field);
            }
        }

        // Sensitive auth fields require elevated permission over the target user
        $canEditAuthFields = Gate::allows('canEditAuthFields', $user);

        if ($request->filled('new_username') || $request->filled('new_email') ||
            $request->filled('password') || $request->has('activated')) {
            if (! $canEditAuthFields) {
                return Response::make(Response::error('You do not have permission to edit auth fields (username, email, password, activated) for this user'));
            }

            if ($request->filled('new_username')) {
                $user->username = $request->get('new_username');
            }

            if ($request->filled('new_email')) {
                $user->email = $request->get('new_email');
            }

            if ($request->filled('password')) {
                $user->password = bcrypt($request->get('password'));
            }

            if ($request->has('activated')) {
                $user->activated = $request->get('activated');
            }
        }

        if ($request->filled('company_id')) {
            $user->company_id = Company::getIdForCurrentUser($request->get('company_id'));
        }

        if ($user->save()) {
            return Response::make(
                Response::text('User '.$user->username.' updated successfully')
            )->withStructuredContent([
                'success' => true,
                'message' => 'User updated successfully',
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ]);
        }

        return Response::make(Response::error('Update failed: '.$user->getErrors()->first()));
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
            'company_id' => $schema->number()->description('Company ID'),
            'department_id' => $schema->number()->description('Department ID'),
            'location_id' => $schema->number()->description('Location ID'),
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
