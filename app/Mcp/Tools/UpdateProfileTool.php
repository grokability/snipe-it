<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Location;
use App\Models\Setting;
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

#[Name('update_profile')]
#[Title('Update Profile')]
#[Description('Update the authenticated user\'s own profile information')]
class UpdateProfileTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Write)) {
            return $denied;
        }

        $fkMap = [
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

        $input = array_merge($request->all(), $resolvedFks);

        // Profile has a narrow allow-list that does NOT share shape with
        // User::getRules() (no password, no username, no email, etc.).
        // Keep the rules local here so the tool's attack surface is
        // visible in one place. Per-field permission gates below decide
        // which validated values actually get applied.
        try {
            $data = Validator::make($input, [
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:35',
                'website' => 'nullable|url|max:255',
                'gravatar' => 'nullable|string|max:255',
                'locale' => 'nullable|string|max:10',
                'two_factor_optin' => 'nullable|boolean',
                'location_id' => 'nullable|integer|exists:locations,id',
            ])->validate();
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $user = auth()->user();

        if (Gate::allows('self.profile') && ! config('app.lock_passwords')) {
            foreach (['first_name', 'last_name', 'phone', 'website', 'gravatar'] as $field) {
                if (isset($data[$field])) {
                    $user->{$field} = $data[$field];
                }
            }
        }

        if (isset($data['locale'])) {
            $user->locale = $data['locale'];
        }

        if (
            isset($data['two_factor_optin']) &&
            Gate::allows('self.two_factor') &&
            Setting::getSettings()->two_factor_enabled == '1' &&
            ! config('app.lock_passwords')
        ) {
            $user->two_factor_optin = $data['two_factor_optin'];
        }

        if (isset($data['location_id']) && Gate::allows('self.edit_location') && ! config('app.lock_passwords')) {
            $user->location_id = $data['location_id'];
        }

        if ($user->save()) {
            return Response::make(
                Response::text(trans('mcp.profile_updated'))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.profile_updated'),
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'website' => $user->website,
                'locale' => $user->locale,
                'location_id' => $user->location_id,
            ]);
        }

        return Response::make(Response::error(trans('mcp.update_failed', ['error' => $user->getErrors()->first()])));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'first_name' => $schema->string()->description('First name'),
            'last_name' => $schema->string()->description('Last name'),
            'phone' => $schema->string()->description('Phone number'),
            'website' => $schema->string()->description('Personal website URL'),
            'gravatar' => $schema->string()->description('Gravatar email or hash'),
            'locale' => $schema->string()->description('Locale/language code (e.g. en-US)'),
            'two_factor_optin' => $schema->boolean()->description('Opt in to two-factor authentication (requires self.two_factor permission and 2FA enabled in settings)'),
            'location_id' => $schema->number()->description('Default location ID (requires self.edit_location permission). Alternative: location_name.'),
            'location_name' => $schema->string()->description('Default location name. Resolved to an ID server-side.'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the update succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'first_name' => $schema->string()->description('Updated first name'),
            'last_name' => $schema->string()->description('Updated last name'),
            'phone' => $schema->string()->description('Updated phone number'),
            'website' => $schema->string()->description('Updated website URL'),
            'locale' => $schema->string()->description('Updated locale'),
            'location_id' => $schema->number()->description('Updated location ID'),
        ];
    }
}
