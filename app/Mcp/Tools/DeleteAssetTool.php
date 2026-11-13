<?php

namespace App\Mcp\Tools;

use App\Enums\McpScope;
use App\Events\CheckoutableCheckedIn;
use App\Mcp\Traits\HasMcpScope;
use App\Mcp\Traits\ResolvesForeignKeyByName;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
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
 * 1. Single identifier (asset_tag / serial / id / name). One target,
 *    dry_run defaults false. Back-compat with pre-bulk callers.
 * 2. Array of identifiers (ids / asset_tags / serials). Caller knows
 *    what they want deleted, dry_run defaults true but caller can
 *    pass false explicitly to skip the preview since the array is
 *    self-confirming.
 * 3. Filter (same shape as list_assets). dry_run defaults true,
 *    confirm_count required at commit to guard against the filter
 *    matching more / fewer rows than the admin expected.
 *
 * All multi-target commits run inside one DB transaction and record
 * a separate action log entry per row so restore + audit work
 * cleanly.
 */
#[Name('delete_asset')]
#[Title('Delete Asset')]
#[Description('Soft-delete one asset by identifier, several by identifier array, or many by filter. Filter and array shapes default to dry_run=true for preview-first safety. If an asset is checked out it will be checked in first.')]
class DeleteAssetTool extends Tool
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
                'asset_tag' => 'nullable|string|max:100',
                'serial' => 'nullable|string|max:255',
                'ids' => 'nullable|array',
                'ids.*' => 'integer',
                'asset_tags' => 'nullable|array',
                'asset_tags.*' => 'string|max:100',
                'serials' => 'nullable|array',
                'serials.*' => 'string|max:255',
                'filter' => 'nullable',
                'search' => 'nullable|string|max:255',
                'status_type' => 'nullable|string|in:RTD,Deployed,Archived,Pending,Undeployable',
                'company_id' => 'nullable|integer',
                'company_name' => 'nullable|string|max:255',
                'location_id' => 'nullable|integer',
                'location_name' => 'nullable|string|max:255',
                'rtd_location_id' => 'nullable|integer',
                'rtd_location_name' => 'nullable|string|max:255',
                'category_id' => 'nullable|integer',
                'category_name' => 'nullable|string|max:255',
                'model_id' => 'nullable|integer',
                'model_name' => 'nullable|string|max:255',
                'manufacturer_id' => 'nullable|integer',
                'manufacturer_name' => 'nullable|string|max:255',
                'supplier_id' => 'nullable|integer',
                'supplier_name' => 'nullable|string|max:255',
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
            ['location_id', 'location_name', Location::class],
            ['rtd_location_id', 'rtd_location_name', Location::class],
            ['category_id', 'category_name', Category::class],
            ['model_id', 'model_name', AssetModel::class],
            ['manufacturer_id', 'manufacturer_name', Manufacturer::class],
            ['supplier_id', 'supplier_name', Supplier::class],
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

        [$deletable, $undeletable] = $targets->partition(fn (Asset $a) => $a->isDeletable());

        if ($undeletable->isNotEmpty() && ! $request->boolean('skip_undeletable', false)) {
            return Response::make(Response::error(trans('mcp.bulk_batch_has_undeletable', [
                'count' => $undeletable->count(),
            ])));
        }

        return $this->commitBulkDelete($deletable, $request->get('reason'));
    }

    private function detectShape(Request $request): string
    {
        if ($request->filled('ids') || $request->filled('asset_tags') || $request->filled('serials')) {
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
            'filter', 'search', 'status_type',
            'company_id', 'company_name',
            'location_id', 'location_name',
            'rtd_location_id', 'rtd_location_name',
            'category_id', 'category_name',
            'model_id', 'model_name',
            'manufacturer_id', 'manufacturer_name',
            'supplier_id', 'supplier_name',
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
        $asset = $this->resolveSingle($request);

        if (! $asset) {
            return Response::make(Response::error(trans('general.generic_model_not_found', ['model' => trans('general.asset')])));
        }

        $dryRun = $request->boolean('dry_run', false);

        if ($dryRun) {
            return $this->buildPreview(Asset::whereKey($asset->id)->get());
        }

        if (! $asset->isDeletable()) {
            return Response::make(Response::error(trans('admin/hardware/message.assoc_users')));
        }

        $assetTag = $asset->asset_tag;

        $this->checkInIfAssigned($asset, $request->get('reason'));
        $asset->delete();

        return Response::make(
            Response::text(trans('mcp.asset_deleted', ['asset_tag' => $assetTag]))
        )->withStructuredContent([
            'success' => true,
            'message' => trans('mcp.asset_deleted', ['asset_tag' => $assetTag]),
            'asset_tag' => $assetTag,
        ]);
    }

    private function resolveSingle(Request $request): ?Asset
    {
        if ($request->filled('asset_tag')) {
            return Asset::where('asset_tag', $request->get('asset_tag'))->first();
        }
        if ($request->filled('serial')) {
            return Asset::where('serial', $request->get('serial'))->first();
        }
        if ($request->filled('id')) {
            return Asset::find($request->get('id'));
        }

        return null;
    }

    private function resolveByArray(Request $request): Collection
    {
        $query = Asset::query();

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->get('ids'));
        } elseif ($request->filled('asset_tags')) {
            $query->whereIn('asset_tag', $request->get('asset_tags'));
        } elseif ($request->filled('serials')) {
            $query->whereIn('serial', $request->get('serials'));
        }

        return $query->get();
    }

    private function resolveByFilter(Request $request): Collection
    {
        $query = Asset::select('assets.*');

        match ($request->filled('status_type') ? $request->get('status_type') : null) {
            'RTD' => $query->rtd(),
            'Deployed' => $query->deployed(),
            'Archived' => $query->archived(),
            'Pending' => $query->pending(),
            'Undeployable' => $query->undeployable(),
            default => null,
        };

        if ($request->filled('filter')) {
            $query->TextSearch($request->get('filter'));
        } elseif ($request->filled('search')) {
            $query->TextSearch($request->get('search'));
        }

        if ($request->filled('company_id')) {
            $query->where('assets.company_id', '=', $request->get('company_id'));
        }
        if ($request->filled('location_id')) {
            $query->where('assets.location_id', '=', $request->get('location_id'));
        }
        if ($request->filled('rtd_location_id')) {
            $query->where('assets.rtd_location_id', '=', $request->get('rtd_location_id'));
        }
        if ($request->filled('category_id')) {
            $query->inCategory($request->get('category_id'));
        }
        if ($request->filled('model_id')) {
            $query->where('assets.model_id', '=', $request->get('model_id'));
        }
        if ($request->filled('manufacturer_id')) {
            $query->byManufacturer($request->get('manufacturer_id'));
        }
        if ($request->filled('supplier_id')) {
            $query->where('assets.supplier_id', '=', $request->get('supplier_id'));
        }
        if ($request->filled('created_before')) {
            $query->where('assets.created_at', '<=', $request->get('created_before'));
        }
        if ($request->filled('created_after')) {
            $query->where('assets.created_at', '>=', $request->get('created_after'));
        }

        return $query->get();
    }

    private function buildPreview(Collection $targets): ResponseFactory
    {
        [$deletable, $undeletable] = $targets->partition(fn (Asset $a) => $a->isDeletable());

        $sampleRows = $targets->take(10)->map(fn (Asset $a) => [
            'id' => $a->id,
            'display_name' => $a->asset_tag,
        ])->values()->all();

        $undeletableMatches = $undeletable->take(10)->map(fn (Asset $a) => [
            'id' => $a->id,
            'display_name' => $a->asset_tag,
            'reason' => trans('admin/hardware/message.assoc_users'),
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

    private function commitBulkDelete(Collection $deletable, ?string $reason): ResponseFactory
    {
        $deletedCount = 0;
        $failures = [];

        try {
            DB::transaction(function () use ($deletable, $reason, &$deletedCount, &$failures) {
                /** @var Asset $asset */
                foreach ($deletable as $asset) {
                    try {
                        $this->checkInIfAssigned($asset, $reason);
                        if ($asset->delete()) {
                            $deletedCount++;
                        } else {
                            $failures[] = $asset->asset_tag.': delete returned false';
                        }
                    } catch (Throwable $e) {
                        $failures[] = $asset->asset_tag.': '.$e->getMessage();
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
            'reason' => $reason,
        ]);
    }

    private function checkInIfAssigned(Asset $asset, ?string $reason): void
    {
        if (! $asset->assignedTo) {
            return;
        }

        $target = $asset->assignedTo;
        $originalValues = $asset->getRawOriginal();
        $note = $reason ?: 'Checked in on delete';
        event(new CheckoutableCheckedIn($asset, $target, auth()->user(), $note, date('Y-m-d H:i:s'), $originalValues));
        DB::table('assets')->where('id', $asset->id)->update(['assigned_to' => null]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            // Single-identifier shape
            'asset_tag' => $schema->string()->description('Asset tag (single-target shape). Alternative: pass asset_tags array for multiple.'),
            'serial' => $schema->string()->description('Serial number (single-target shape).'),
            'id' => $schema->number()->description('Numeric ID (single-target shape).'),

            // Array shape
            'ids' => $schema->array()->description('Array of numeric IDs. Defaults to dry_run=true, pass dry_run=false to commit.'),
            'asset_tags' => $schema->array()->description('Array of asset tags. Defaults to dry_run=true, pass dry_run=false to commit.'),
            'serials' => $schema->array()->description('Array of serial numbers. Defaults to dry_run=true, pass dry_run=false to commit.'),

            // Filter shape (same as list_assets)
            'filter' => $schema->object()->description('Structured advanced-search payload (same shape as list_assets.filter).'),
            'search' => $schema->string()->description('Free-text keyword across tag, serial, name, model.'),
            'status_type' => $schema->string()->description('`RTD`, `Deployed`, `Archived`, `Pending`, or `Undeployable`.'),
            'company_id' => $schema->number()->description('Filter by company. Alternative: company_name.'),
            'company_name' => $schema->string()->description('Filter by company name.'),
            'location_id' => $schema->number()->description('Filter by current location. Alternative: location_name.'),
            'location_name' => $schema->string()->description('Filter by current location name.'),
            'rtd_location_id' => $schema->number()->description('Filter by default (RTD) location. Alternative: rtd_location_name.'),
            'rtd_location_name' => $schema->string()->description('Filter by default (RTD) location name.'),
            'category_id' => $schema->number()->description('Filter by category. Alternative: category_name.'),
            'category_name' => $schema->string()->description('Filter by category name.'),
            'model_id' => $schema->number()->description('Filter by model. Alternative: model_name.'),
            'model_name' => $schema->string()->description('Filter by model name.'),
            'manufacturer_id' => $schema->number()->description('Filter by manufacturer. Alternative: manufacturer_name.'),
            'manufacturer_name' => $schema->string()->description('Filter by manufacturer name.'),
            'supplier_id' => $schema->number()->description('Filter by supplier. Alternative: supplier_name.'),
            'supplier_name' => $schema->string()->description('Filter by supplier name.'),
            'created_before' => $schema->string()->description('Only match assets created on or before this date (YYYY-MM-DD).'),
            'created_after' => $schema->string()->description('Only match assets created on or after this date (YYYY-MM-DD).'),

            // Bulk-mode controls
            'dry_run' => $schema->boolean()->description('When true, return preview without deleting. Default false for single-identifier, true for array / filter shapes.'),
            'confirm_count' => $schema->number()->description('Required when dry_run=false AND using filter shape. Must equal total_matched from the preview. Guards against concurrent writes between preview and commit.'),
            'skip_undeletable' => $schema->boolean()->description('When true, drop undeletable rows from the batch instead of aborting. Default false (whole-batch abort).'),
            'reason' => $schema->string()->description('Recorded on each deleted row\'s action log. Example: "closing 2019 FY inventory".'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'success' => $schema->boolean()->description('True if the deletion (or preview) succeeded'),
            'message' => $schema->string()->description('Human-readable result message')->required(),
            'dry_run' => $schema->boolean()->description('True when the response is a preview, false when it is a commit result'),
            'asset_tag' => $schema->string()->description('Asset tag of the deleted asset (single-target shape only)'),
            'total_matched' => $schema->number()->description('How many rows matched the filter / array (bulk preview only)'),
            'will_delete' => $schema->number()->description('How many rows would actually delete (bulk preview only)'),
            'undeletable_count' => $schema->number()->description('How many rows match but fail isDeletable (bulk preview only)'),
            'sample_rows' => $schema->array()->description('First 10 matched rows with id + asset_tag (bulk preview only)'),
            'undeletable_matches' => $schema->array()->description('First 10 undeletable rows with reason (bulk preview only)'),
            'deleted_count' => $schema->number()->description('How many rows were deleted (bulk commit only)'),
            'failed_count' => $schema->number()->description('How many rows failed at commit (bulk commit only)'),
            'failures' => $schema->array()->description('Per-row failure messages (bulk commit only)'),
            'reason' => $schema->string()->description('The reason string passed on this call'),
        ];
    }
}
