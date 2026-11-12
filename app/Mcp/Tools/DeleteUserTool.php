<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Throwable;

/**
 * Three input shapes:
 *
 * 1. Single identifier (id / username / email). One target, dry_run
 *    defaults false. Back-compat with pre-bulk callers.
 * 2. Array of identifiers (ids / usernames / emails). Caller knows
 *    what they want deleted, dry_run defaults true but caller can
 *    pass false explicitly.
 * 3. Filter (same shape as list_users). dry_run defaults true,
 *    confirm_count required at commit.
 *
 * The self-delete guard applies to all three shapes: the currently
 * authenticated admin can never land in the batch.
 */
#[Name('delete_user')]
#[Title('Delete User')]
#[Description('Soft-delete one user by identifier, several by identifier array, or many by filter. User(s) must have no assigned assets, licenses, accessories, or consumables. Filter and array shapes default to dry_run=true for preview-first safety.')]
class DeleteUserTool extends Tool
{
    use HasMcpScope;
    use ResolvesForeignKeyByName;

    public function handle(Request $request): ResponseFactory
    {
        if ($denied = $this->scopeDenied(McpScope::Delete)) {
            return $denied;
        }

        try {
            $request->validate([
                'id' => 'nullable|integer',
                'username' => 'nullable|string|max:191',
                'email' => 'nullable|string|max:191',
                'ids' => 'nullable|array',
                'ids.*' => 'integer',
                'usernames' => 'nullable|array',
                'usernames.*' => 'string|max:191',
                'emails' => 'nullable|array',
                'emails.*' => 'string|max:191',
                'filter' => 'nullable',
                'search' => 'nullable|string|max:255',
                'company_id' => 'nullable|integer',
                'company_name' => 'nullable|string|max:255',
                'department_id' => 'nullable|integer',
                'department_name' => 'nullable|string|max:255',
                'location_id' => 'nullable|integer',
                'location_name' => 'nullable|string|max:255',
                'activated' => 'nullable|boolean',
                'include_deleted' => 'nullable|boolean',
                'created_before' => 'nullable|date',
                'created_after' => 'nullable|date',
                'dry_run' => 'nullable|boolean',
                'confirm_count' => 'nullable|integer',
                'skip_undeletable' => 'nullable|boolean',
                'reason' => 'nullable|string|max:500',
            ]);
        } catch (ValidationException $e) {
            return Response::make(Response::error($e->validator->errors()->first()));
        }

        $shape = $this->detectShape($request);

        if ($shape === 'single') {
            return $this->handleSingle($request);
        }

        $fkMap = [
            ['company_id', 'company_name', Company::class],
            ['department_id', 'department_name', Department::class],
            ['location_id', 'location_name', Location::class],
        ];
        foreach ($fkMap as [$idField, $nameField, $modelClass]) {
            $resolved = $this->resolveFkByName($request, $idField, $nameField, $modelClass);
            if ($this->fkResolutionFailed($resolved)) {
                return Response::make(Response::error($this->fkErrorMessage($resolved)));
            }
            if ($resolved !== null) {
                $request->merge([$idField => $resolved]);
            }
        }

        $targets = $shape === 'array'
            ? $this->resolveByArray($request)
            : $this->resolveByFilter($request);

        // Self-delete guard: never let the signed-in admin land in a
        // bulk batch, same reason the single-identifier path blocks it.
        $targets = $targets->reject(fn (User $u) => $u->id === auth()->id())->values();

        $dryRun = $request->boolean('dry_run', true);

        if ($dryRun) {
            return $this->buildPreview($targets);
        }

        if ($shape === 'filter') {
            if (! $request->filled('confirm_count')) {
                return Response::make(Response::error(trans('mcp.bulk_confirm_count_required')));
            }
            if ((int) $request->get('confirm_count') !== $targets->count()) {
                return Response::make(Response::error(trans('mcp.bulk_confirm_count_mismatch', [
                    'expected' => $request->get('confirm_count'),
                    'actual' => $targets->count(),
                ])));
            }
        }

        [$deletable, $undeletable] = $targets->partition(fn (User $u) => $u->isDeletable());

        if ($undeletable->isNotEmpty() && ! $request->boolean('skip_undeletable', false)) {
            return Response::make(Response::error(trans('mcp.bulk_batch_has_undeletable', [
                'count' => $undeletable->count(),
            ])));
        }

        return $this->commitBulkDelete($deletable);
    }

    private function detectShape(Request $request): string
    {
        if ($request->filled('ids') || $request->filled('usernames') || $request->filled('emails')) {
            return 'array';
        }
        if ($this->hasFilterParams($request)) {
            return 'filter';
        }

        return 'single';
    }

    private function hasFilterParams(Request $request): bool
    {
        $filterParams = [
            'filter', 'search',
            'company_id', 'company_name',
            'department_id', 'department_name',
            'location_id', 'location_name',
            'activated', 'include_deleted',
            'created_before', 'created_after',
        ];
        foreach ($filterParams as $key) {
            if ($request->filled($key)) {
                return true;
            }
        }

        return false;
    }

    private function handleSingle(Request $request): ResponseFactory
    {
        $user = $this->resolveSingle($request);

        if (! $user) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.user')])));
        }

        if ($user->id === auth()->id()) {
            return Response::make(Response::error(trans('mcp.user_cannot_delete_self')));
        }

        $dryRun = $request->boolean('dry_run', false);

        if ($dryRun) {
            return $this->buildPreview(User::whereKey($user->id)->get());
        }

        if (! $user->isDeletable()) {
            return Response::make(Response::error(trans('mcp.cannot_delete_has_dependents', [
                'model' => trans('general.user'),
                'name' => $user->username,
            ])));
        }

        $username = $user->username;

        if ($user->delete()) {
            return Response::make(
                Response::text(trans('mcp.user_deleted', ['username' => $username]))
            )->withStructuredContent([
                'success' => true,
                'message' => trans('mcp.user_deleted', ['username' => $username]),
                'username' => $username,
            ]);
        }

        return Response::make(Response::error(trans('mcp.delete_failed_error', ['error' => $user->getErrors()->first()])));
    }

    private function resolveSingle(Request $request): ?User
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

    private function resolveByArray(Request $request): Collection
    {
        $query = User::query();

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->get('ids'));
        } elseif ($request->filled('usernames')) {
            $query->whereIn('username', $request->get('usernames'));
        } elseif ($request->filled('emails')) {
            $query->whereIn('email', $request->get('emails'));
        }

        return $query->get();
    }

    private function resolveByFilter(Request $request): Collection
    {
        $query = User::query();

        if ($request->boolean('include_deleted')) {
            $query->withTrashed();
        }

        if ($request->filled('filter')) {
            $query->TextSearch($request->get('filter'));
        } elseif ($request->filled('search')) {
            $query->TextSearch($request->get('search'));
        }

        if ($request->filled('company_id')) {
            // Users use the company_user pivot after the parent-company
            // feature landed. See ListUsersTool for the same pattern.
            $companyId = $request->get('company_id');
            $query->whereHas('companies', fn ($q) => $q->where('companies.id', $companyId));
        }

        if ($request->filled('department_id')) {
            $query->where('users.department_id', '=', $request->get('department_id'));
        }

        if ($request->filled('location_id')) {
            $query->where('users.location_id', '=', $request->get('location_id'));
        }

        if ($request->has('activated')) {
            $query->where('users.activated', '=', $request->get('activated'));
        }

        if ($request->filled('created_before')) {
            $query->where('users.created_at', '<=', $request->get('created_before'));
        }
        if ($request->filled('created_after')) {
            $query->where('users.created_at', '>=', $request->get('created_after'));
        }

        return $query->get();
    }

    private function buildPreview(Collection $targets): ResponseFactory
    {
        [$deletable, $undeletable] = $targets->partition(fn (User $u) => $u->isDeletable());

        $sampleRows = $targets->take(10)->map(fn (User $u) => [
            'id' => $u->id,
            'display_name' => $u->username,
        ])->values()->all();

        $undeletableMatches = $undeletable->take(10)->map(fn (User $u) => [
            'id' => $u->id,
            'display_name' => $u->username,
            'reason' => trans('mcp.cannot_delete_has_dependents', [
                'model' => trans('general.user'),
                'name' => $u->username,
            ]),
        ])->values()->all();

        return Response::make(
            Response::text(trans('mcp.bulk_dry_run_summary', [
                'total' => $targets->count(),
                'deletable' => $deletable->count(),
            ]))
        )->withStructuredContent([
            'success' => true,
            'dry_run' => true,
            'total_matched' => $targets->count(),
            'will_delete' => $deletable->count(),
            'undeletable_count' => $undeletable->count(),
            'sample_rows' => $sampleRows,
            'undeletable_matches' => $undeletableMatches,
        ]);
    }

    private function commitBulkDelete(Collection $deletable): ResponseFactory
    {
        $deletedCount = 0;
        $failures = [];

        try {
            DB::transaction(function () use ($deletable, &$deletedCount, &$failures) {
                /** @var User $user */
                foreach ($deletable as $user) {
                    try {
                        if ($user->delete()) {
                            $deletedCount++;
                        } else {
                            $failures[] = $user->username.': delete returned false';
                        }
                    } catch (Throwable $e) {
                        $failures[] = $user->username.': '.$e->getMessage();
                    }
                }

                if (! empty($failures)) {
                    throw new \RuntimeException('bulk delete partial failure');
                }
            });
        } catch (Throwable $e) {
            return Response::make(Response::error(trans('mcp.bulk_delete_failed', [
                'failures' => implode('; ', $failures ?: [$e->getMessage()]),
            ])));
        }

        return Response::make(
            Response::text(trans('mcp.bulk_delete_completed', [
                'count' => $deletedCount,
            ]))
        )->withStructuredContent([
            'success' => true,
            'dry_run' => false,
            'deleted_count' => $deletedCount,
            'failed_count' => count($failures),
            'failures' => $failures,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            // Single-identifier shape
            'id' => $schema->number()->description('Numeric user ID (single-target shape).'),
            'username' => $schema->string()->description('Username (single-target shape). Alternative: pass usernames array for multiple.'),
            'email' => $schema->string()->description('Email address (single-target shape).'),

            // Array shape
            'ids' => $schema->array()->description('Array of numeric user IDs. Defaults to dry_run=true, pass dry_run=false to commit.'),
            'usernames' => $schema->array()->description('Array of usernames. Defaults to dry_run=true, pass dry_run=false to commit.'),
            'emails' => $schema->array()->description('Array of email addresses. Defaults to dry_run=true, pass dry_run=false to commit.'),

            // Filter shape (same as list_users)
            'filter' => $schema->object()->description('Structured advanced-search payload (same shape as list_users.filter).'),
            'search' => $schema->string()->description('Free-text keyword across name, username, email, employee number.'),
            'company_id' => $schema->number()->description('Filter by company. Alternative: company_name.'),
            'company_name' => $schema->string()->description('Filter by company name.'),
            'department_id' => $schema->number()->description('Filter by department. Alternative: department_name.'),
            'department_name' => $schema->string()->description('Filter by department name.'),
            'location_id' => $schema->number()->description('Filter by location. Alternative: location_name.'),
            'location_name' => $schema->string()->description('Filter by location name.'),
            'activated' => $schema->boolean()->description('Filter by the activated login-access flag.'),
            'include_deleted' => $schema->boolean()->description('When true, include already soft-deleted users in the result set.'),
            'created_before' => $schema->string()->description('Only match users created on or before this date (YYYY-MM-DD).'),
            'created_after' => $schema->string()->description('Only match users created on or after this date (YYYY-MM-DD).'),

            // Bulk-mode controls
            'dry_run' => $schema->boolean()->description('When true, return preview without deleting. Default false for single-identifier, true for array / filter shapes.'),
            'confirm_count' => $schema->number()->description('Required when dry_run=false AND using filter shape. Must equal total_matched from the preview.'),
            'skip_undeletable' => $schema->boolean()->description('When true, drop undeletable rows from the batch instead of aborting. Default false (whole-batch abort).'),
            'reason' => $schema->string()->description('Recorded on each deleted row\'s action log. Example: "annual stale account cleanup".'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion (or preview) succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'dry_run' => $schema->boolean()->description('True when the response is a preview, false when it is a commit result'),
            'username' => $schema->string()->description('Username of the deleted user (single-target shape only)'),
            'total_matched' => $schema->number()->description('How many rows matched the filter / array (bulk preview only)'),
            'will_delete' => $schema->number()->description('How many rows would actually delete (bulk preview only)'),
            'undeletable_count' => $schema->number()->description('How many rows match but fail isDeletable (bulk preview only)'),
            'sample_rows' => $schema->array()->description('First 10 matched rows with id + username (bulk preview only)'),
            'undeletable_matches' => $schema->array()->description('First 10 undeletable rows with reason (bulk preview only)'),
            'deleted_count' => $schema->number()->description('How many rows were deleted (bulk commit only)'),
            'failed_count' => $schema->number()->description('How many rows failed at commit (bulk commit only)'),
            'failures' => $schema->array()->description('Per-row failure messages (bulk commit only)'),
        ];
    }
}
