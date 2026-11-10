<?php

namespace App\Mcp\Tools;

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

#[Name('show_user')]
#[Title('Show User')]
#[Description('Look up a single Snipe-IT user by numeric ID, username, or email address')]
class ShowUserTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $request->validate([
            'id' => 'nullable|integer',
            'username' => 'nullable|string|max:191',
            'email' => 'nullable|string|max:191',
        ]);

        $with = ['company', 'department', 'userloc', 'manager', 'groups'];

        if ($request->filled('id')) {
            $user = User::with($with)
                ->withCount(['assets as assets_count', 'licenses as licenses_count', 'accessories as accessories_count', 'consumables as consumables_count'])
                ->find($request->get('id'));
        } elseif ($request->filled('username')) {
            $user = User::where('username', $request->get('username'))->with($with)
                ->withCount(['assets as assets_count', 'licenses as licenses_count', 'accessories as accessories_count', 'consumables as consumables_count'])
                ->first();
        } elseif ($request->filled('email')) {
            $user = User::where('email', $request->get('email'))->with($with)
                ->withCount(['assets as assets_count', 'licenses as licenses_count', 'accessories as accessories_count', 'consumables as consumables_count'])
                ->first();
        } else {
            return Response::make(Response::error('Please provide an id, username, or email'));
        }

        if (! $user) {
            return Response::make(Response::error('User not found'));
        }

        if (! Gate::allows('view', $user)) {
            return Response::make(Response::error('Unauthorized'));
        }

        return Response::make(
            Response::text('User '.$user->username.' found')
        )->withStructuredContent([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'email' => $user->email,
            'employee_num' => $user->employee_num,
            'jobtitle' => $user->jobtitle,
            'phone' => $user->phone,
            'mobile' => $user->mobile,
            'company' => $user->company?->name,
            'company_id' => $user->company_id,
            'department' => $user->department?->name,
            'department_id' => $user->department_id,
            'location' => $user->userloc?->name,
            'location_id' => $user->location_id,
            'manager' => $user->manager ? trim($user->manager->first_name.' '.$user->manager->last_name) : null,
            'manager_id' => $user->manager_id,
            'activated' => (bool) $user->activated,
            'notes' => $user->notes,
            'start_date' => $user->start_date?->toDateString(),
            'end_date' => $user->end_date?->toDateString(),
            'vip' => (bool) $user->vip,
            'remote' => (bool) $user->remote,
            'website' => $user->website,
            'address' => $user->address,
            'city' => $user->city,
            'state' => $user->state,
            'country' => $user->country,
            'zip' => $user->zip,
            'assets_count' => $user->assets_count,
            'licenses_count' => $user->licenses_count,
            'accessories_count' => $user->accessories_count,
            'consumables_count' => $user->consumables_count,
            'last_login' => $user->last_login?->toDateTimeString(),
            'created_at' => $user->created_at?->toDateTimeString(),
            'updated_at' => $user->updated_at?->toDateTimeString(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric user ID'),
            'username' => $schema->string()->description('Username to look up'),
            'email' => $schema->string()->description('Email address to look up'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->number()->description('Numeric user ID')->required(),
            'username' => $schema->string()->description('Username')->required(),
            'email' => $schema->string()->description('Email address'),
            'first_name' => $schema->string()->description('First name'),
            'last_name' => $schema->string()->description('Last name'),
        ];
    }
}
