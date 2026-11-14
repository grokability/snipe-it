<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Category;
use App\Models\Company;
use App\Models\Depreciation;
use App\Models\License;
use App\Models\Manufacturer;
use App\Models\Supplier;
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
 * 1. Single identifier (id / name). Back-compat with pre-bulk callers.
 * 2. Array of identifiers (ids / names).
 * 3. Filter (same shape as list_licenses).
 *
 * Deleting a license also clears any orphan license_seats rows (same
 * cleanup the HTTP controller runs) so the soft-deleted parent doesn't
 * leave dangling seat rows pointing at deleted users / assets.
 *
 * See DeleteAssetTool for the full pattern commentary.
 */
#[Name('delete_license')]
#[Title('Delete License')]
#[Description('Soft-delete one license by identifier, several by identifier array, or many by filter. Licenses must have no seats currently assigned to users or assets. Filter and array shapes default to dry_run=true for preview-first safety.')]
class DeleteLicenseTool extends Tool
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
                'name' => 'nullable|string|max:255',
                'ids' => 'nullable|array',
                'ids.*' => 'integer',
                'names' => 'nullable|array',
                'names.*' => 'string|max:255',
                'filter' => 'nullable',
                'search' => 'nullable|string|max:255',
                'company_id' => 'nullable|integer',
                'company_name' => 'nullable|string|max:255',
                'category_id' => 'nullable|integer',
                'category_name' => 'nullable|string|max:255',
                'manufacturer_id' => 'nullable|integer',
                'manufacturer_name' => 'nullable|string|max:255',
                'supplier_id' => 'nullable|integer',
                'supplier_name' => 'nullable|string|max:255',
                'depreciation_id' => 'nullable|integer',
                'depreciation_name' => 'nullable|string|max:255',
                'expiring_within_days' => 'nullable|integer|min:1',
                'fully_consumed_only' => 'nullable|boolean',
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
            ['category_id', 'category_name', Category::class],
            ['manufacturer_id', 'manufacturer_name', Manufacturer::class],
            ['supplier_id', 'supplier_name', Supplier::class],
            ['depreciation_id', 'depreciation_name', Depreciation::class],
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

        [$deletable, $undeletable] = $targets->partition(fn (License $l) => $l->isDeletable());

        if ($undeletable->isNotEmpty() && ! $request->boolean('skip_undeletable', false)) {
            return Response::make(Response::error(trans('mcp.bulk_batch_has_undeletable', [
                'count' => $undeletable->count(),
            ])));
        }

        return $this->commitBulkDelete($deletable);
    }

    private function detectShape(Request $request): string
    {
        if ($request->filled('ids') || $request->filled('names')) {
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
            'category_id', 'category_name',
            'manufacturer_id', 'manufacturer_name',
            'supplier_id', 'supplier_name',
            'depreciation_id', 'depreciation_name',
            'expiring_within_days', 'fully_consumed_only',
            'created_before', 'created_after',
        ];
        foreach ($filterParams as $key) {
            if ($request->filled($key) || ($key === 'fully_consumed_only' && $request->has($key))) {
                return true;
            }
        }

        return false;
    }

    private function handleSingle(Request $request): ResponseFactory
    {
        $license = $this->resolveSingle($request);

        if (! $license) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.license')])));
        }

        $dryRun = $request->boolean('dry_run', false);

        if ($dryRun) {
            return $this->buildPreview(License::withCount('freeSeats as free_seats_count')->whereKey($license->id)->get());
        }

        if (! $license->isDeletable()) {
            return Response::make(Response::error(trans('admin/licenses/message.delete.bulk_checkout_warning', ['license_name' => $license->name])));
        }

        $name = $license->name;

        $this->clearSeatsAndDelete($license);

        return Response::make(
            Response::text(trans('mcp.license_deleted', ['name' => $name]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.license_deleted', ['name' => $name]),
            'name' => $name,
        ]);
    }

    private function resolveSingle(Request $request): ?License
    {
        $query = License::withCount('freeSeats as free_seats_count');

        if ($request->filled('id')) {
            return $query->find($request->get('id'));
        }
        if ($request->filled('name')) {
            return $query->where('name', $request->get('name'))->first();
        }

        return null;
    }

    private function resolveByArray(Request $request): Collection
    {
        $query = License::withCount('freeSeats as free_seats_count');

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->get('ids'));
        } elseif ($request->filled('names')) {
            $query->whereIn('name', $request->get('names'));
        }

        return $query->get();
    }

    private function resolveByFilter(Request $request): Collection
    {
        $query = License::withCount('freeSeats as free_seats_count');

        if ($request->filled('filter')) {
            $query->TextSearch($request->get('filter'));
        } elseif ($request->filled('search')) {
            $query->TextSearch($request->get('search'));
        }

        if ($request->filled('company_id')) {
            $query->where('licenses.company_id', '=', $request->get('company_id'));
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', '=', $request->get('category_id'));
        }
        if ($request->filled('manufacturer_id')) {
            $query->where('manufacturer_id', '=', $request->get('manufacturer_id'));
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', '=', $request->get('supplier_id'));
        }
        if ($request->filled('depreciation_id')) {
            $query->where('depreciation_id', '=', $request->get('depreciation_id'));
        }

        if ($request->filled('expiring_within_days')) {
            $days = (int) $request->get('expiring_within_days');
            $query->whereNotNull('expiration_date')
                ->whereBetween('expiration_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
        }

        if ($request->boolean('fully_consumed_only')) {
            $query->having('free_seats_count', '=', 0);
        }

        if ($request->filled('created_before')) {
            $query->where('licenses.created_at', '<=', $request->get('created_before'));
        }
        if ($request->filled('created_after')) {
            $query->where('licenses.created_at', '>=', $request->get('created_after'));
        }

        return $query->get();
    }

    private function buildPreview(Collection $targets): ResponseFactory
    {
        [$deletable, $undeletable] = $targets->partition(fn (License $l) => $l->isDeletable());

        $sampleRows = $targets->take(10)->map(fn (License $l) => [
            'id' => $l->id,
            'display_name' => $l->name,
        ])->values()->all();

        $undeletableMatches = $undeletable->take(10)->map(fn (License $l) => [
            'id' => $l->id,
            'display_name' => $l->name,
            'reason' => trans('admin/licenses/message.delete.bulk_checkout_warning', ['license_name' => $l->name]),
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
                /** @var License $license */
                foreach ($deletable as $license) {
                    try {
                        $this->clearSeatsAndDelete($license);
                        $deletedCount++;
                    } catch (Throwable $e) {
                        $failures[] = $license->name.': '.$e->getMessage();
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

    /**
     * Mirror the HTTP controller's seat-cleanup-then-delete order so a
     * soft-deleted license doesn't leave orphan license_seats rows
     * pointing at live users or assets.
     */
    private function clearSeatsAndDelete(License $license): void
    {
        DB::table('license_seats')
            ->where('license_id', $license->id)
            ->update(['assigned_to' => null, 'asset_id' => null]);

        $license->licenseseats()->delete();
        $license->delete();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            // Single-identifier shape
            'id' => $schema->number()->description('Numeric license ID (single-target shape).'),
            'name' => $schema->string()->description('License name (single-target shape). Alternative: pass names array for multiple.'),

            // Array shape
            'ids' => $schema->array()->description('Array of numeric IDs. Defaults to dry_run=true, pass dry_run=false to commit.'),
            'names' => $schema->array()->description('Array of license names. Defaults to dry_run=true, pass dry_run=false to commit.'),

            // Filter shape (same as list_licenses)
            'filter' => $schema->object()->description('Structured advanced-search payload (same shape as list_licenses.filter).'),
            'search' => $schema->string()->description('Free-text keyword across license name, serial, and related fields.'),
            'company_id' => $schema->number()->description('Filter by company. Alternative: company_name.'),
            'company_name' => $schema->string()->description('Filter by company name.'),
            'category_id' => $schema->number()->description('Filter by category. Alternative: category_name.'),
            'category_name' => $schema->string()->description('Filter by category name.'),
            'manufacturer_id' => $schema->number()->description('Filter by manufacturer. Alternative: manufacturer_name.'),
            'manufacturer_name' => $schema->string()->description('Filter by manufacturer name.'),
            'supplier_id' => $schema->number()->description('Filter by supplier. Alternative: supplier_name.'),
            'supplier_name' => $schema->string()->description('Filter by supplier name.'),
            'depreciation_id' => $schema->number()->description('Filter by depreciation schedule. Alternative: depreciation_name.'),
            'depreciation_name' => $schema->string()->description('Filter by depreciation schedule name.'),
            'expiring_within_days' => $schema->number()->description('Match licenses whose expiration_date falls between today and (today + N days).'),
            'fully_consumed_only' => $schema->boolean()->description('When true, match only licenses with zero free seats remaining.'),
            'created_before' => $schema->string()->description('Only match licenses created on or before this date (YYYY-MM-DD).'),
            'created_after' => $schema->string()->description('Only match licenses created on or after this date (YYYY-MM-DD).'),

            // Bulk-mode controls
            'dry_run' => $schema->boolean()->description('When true, return preview without deleting. Default false for single-identifier, true for array / filter shapes.'),
            'confirm_count' => $schema->number()->description('Required when dry_run=false AND using filter shape. Must equal total_matched from the preview.'),
            'skip_undeletable' => $schema->boolean()->description('When true, drop undeletable rows from the batch instead of aborting. Default false (whole-batch abort).'),
            'reason' => $schema->string()->description('Recorded on each deleted row\'s action log.'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion (or preview) succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'dry_run' => $schema->boolean()->description('True when the response is a preview, false when it is a commit result'),
            'name' => $schema->string()->description('Name of the deleted license (single-target shape only)'),
            'total_matched' => $schema->number()->description('How many rows matched (bulk preview only)'),
            'will_delete' => $schema->number()->description('How many rows would actually delete (bulk preview only)'),
            'undeletable_count' => $schema->number()->description('How many rows match but fail isDeletable (bulk preview only)'),
            'sample_rows' => $schema->array()->description('First 10 matched rows with id + name (bulk preview only)'),
            'undeletable_matches' => $schema->array()->description('First 10 undeletable rows with reason (bulk preview only)'),
            'deleted_count' => $schema->number()->description('How many rows were deleted (bulk commit only)'),
            'failed_count' => $schema->number()->description('How many rows failed at commit (bulk commit only)'),
            'failures' => $schema->array()->description('Per-row failure messages (bulk commit only)'),
        ];
    }
}
