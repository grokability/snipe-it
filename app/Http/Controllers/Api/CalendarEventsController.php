<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\CalendarEventsTransformer;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Unified read endpoint for the calendar page. Queries the
 * calendar_events index table (populated by the HasCalendarEvents
 * trait's observer) and hydrates each row from its live source
 * model, so titles / urls / colors reflect current source state
 * rather than a denormalized snapshot.
 *
 * Filtering:
 *   - `start`, `end` (ISO-8601) - FullCalendar's visible range.
 *     Defaults to a wide six-month window centered on today.
 *   - `event_type[]` - array of allowed event_type values. Empty
 *     means "all types". Frontend filter buttons flip these on and
 *     off.
 *   - `limit` - hard cap on returned events. Defaults to 500 for the
 *     full calendar page and gets overridden downward for the
 *     dashboard "Today" widget. Guards the browser from a 10k-row
 *     JSON blob when a big install's calendar range straddles a
 *     dense date. Response is `{ events, truncated, total }` so the
 *     frontend can render a "narrow filters" banner when truncated.
 *
 * Access control:
 *   - Base gate: the viewer must be able to view AT LEAST ONE of the
 *     source types (Maintenance / Asset / License / User). A user
 *     with only license-view permission still legitimately wants the
 *     calendar to render their license expirations, so the endpoint
 *     shouldn't 403 them just because they can't see assets.
 *   - Source-specific access is enforced per-row via each source's
 *     own view policy - if the actor can't view the underlying model
 *     (FMCS mismatch, location scoping, etc.), the event is filtered
 *     out before it hits the response. That per-row gate does the
 *     actual scoping work.
 */
class CalendarEventsController extends Controller
{
    /**
     * Hard cap on returned rows. Two calendar contexts hit this
     * endpoint: the full calendar page (default 500) and the
     * dashboard's Today widget (much smaller, sends ?limit=25). A
     * request that asks for more than the ceiling silently gets
     * capped so the browser can't be exhausted.
     */
    private const LIMIT_CEILING = 500;

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAnySource();

        [$rangeStart, $rangeEnd] = $this->resolveRange($request);
        $eventTypes = $this->resolveEventTypes($request);
        $limit = $this->resolveLimit($request);

        // Pre-filter the raw query to source types the caller can
        // view at all. This keeps `total` (and therefore the widget's
        // "+N more" label) from counting rows the per-row policy
        // check below would strip anyway. Without this cap a scoped
        // viewer sees inflated remaining-count labels (e.g. "+26 more"
        // when only 2 events in the window are actually viewable to
        // them). Per-row FMCS / location checks still run inside
        // filterAuthorizedRows so this is only a coarse first pass.
        $viewableSourceTypes = array_values(array_filter(
            CalendarEvent::sourceModels(),
            fn (string $sourceClass) => Gate::allows('view', $sourceClass),
        ));

        // CompanyableTrait on CalendarEvent installs the global
        // CompanyableScope, so count / order / limit below run against
        // a company-scoped query. `total` reflects only rows this
        // caller is allowed to see and `limit` cannot be consumed by
        // rows the per-row filter would strip later. The per-row
        // filter below still runs as defense in depth to catch cases
        // the SQL filter can't express (per-location scoping on top of
        // FMCS, soft-deleted sources, etc.).
        $baseQuery = $this->buildBaseQuery($rangeStart, $rangeEnd, $eventTypes)
            ->whereIn('source_type', $viewableSourceTypes);

        // Count only rows whose source row still exists (and, when the
        // source uses SoftDeletes, is not trashed). Truncating a source
        // table skips the HasCalendarEvents deleted observer, so a
        // reseed or manual truncate leaves orphan calendar_events rows
        // whose source_id no longer resolves. Those orphans fall
        // through this EXISTS filter instead of inflating the
        // "+N more" label the frontend renders from $total.
        $total = (clone $baseQuery)
            ->where(function ($q) use ($viewableSourceTypes) {
                foreach ($viewableSourceTypes as $sourceClass) {
                    $table = (new $sourceClass)->getTable();
                    $hasSoftDeletes = in_array(
                        \Illuminate\Database\Eloquent\SoftDeletes::class,
                        class_uses_recursive($sourceClass),
                        true,
                    );
                    $q->orWhere(function ($or) use ($sourceClass, $table, $hasSoftDeletes) {
                        $or->where('source_type', $sourceClass)
                            ->whereExists(function ($sub) use ($table, $hasSoftDeletes) {
                                $sub->selectRaw('1')
                                    ->from($table)
                                    ->whereColumn($table.'.id', 'calendar_events.source_id');
                                if ($hasSoftDeletes) {
                                    $sub->whereNull($table.'.deleted_at');
                                }
                            });
                    });
                }
            })
            ->count();

        $rows = $baseQuery->orderBy('start')->limit($limit)->get();

        // Now that count and fetch run against the same company-scoped
        // query, `truncated` can honestly reflect "more authorized
        // events exist beyond this batch" rather than the earlier
        // proxy that reported true whenever the pre-filter batch was
        // full, even when per-row filtering reduced the response to
        // fewer than $limit rows.
        $hitLimit = $total > $limit;

        $sourcesByType = $this->batchLoadSources($rows);
        $authorizedRows = $this->filterAuthorizedRows($rows, $sourcesByType, $request);

        return response()->json([
            'events' => (new CalendarEventsTransformer)->transformCalendarEvents($authorizedRows, $sourcesByType),
            'total' => $total,
            'truncated' => $hitLimit,
        ]);
    }

    /**
     * Base gate: viewer must be able to view AT LEAST ONE registered
     * HasCalendarEvents source model. Per-row policy checks inside
     * buildEvents() handle the actual event-level scoping.
     */
    protected function authorizeAnySource(): void
    {
        $this->authorize('canViewUsersAndCheckoutables');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveRange(Request $request): array
    {
        return [
            $this->parseDateOrDefault($request->input('start'), fn () => now()->subMonths(3)->startOfDay()),
            $this->parseDateOrDefault($request->input('end'), fn () => now()->addMonths(3)->endOfDay()),
        ];
    }

    /**
     * Force a `start` / `end` query param to a Carbon or fall back
     * to the provided default.
     */
    private function parseDateOrDefault(mixed $value, callable $default): Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return $default();
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return $default();
        }
    }

    /**
     * `event_type[]=a&event_type[]=b` (repeatable) or `event_type=a,b`
     * (single param, comma-split). Empty or missing means "all types".
     *
     * @return array<int, string>
     */
    protected function resolveEventTypes(Request $request): array
    {
        $eventTypes = $request->input('event_type');
        if (is_string($eventTypes)) {
            $eventTypes = array_filter(array_map('trim', explode(',', $eventTypes)));
        }

        return is_array($eventTypes) ? array_values(array_filter($eventTypes)) : [];
    }

    protected function resolveLimit(Request $request): int
    {
        $limit = (int) $request->input('limit', self::LIMIT_CEILING);

        return max(1, min($limit, self::LIMIT_CEILING));
    }

    /**
     * FullCalendar's fetchInfo passes ranges as [start, end) (end
     * exclusive), so a listDay view of today is start=today 00:00,
     * end=tomorrow 00:00. whereBetween is inclusive on both sides
     * though, which pulled tomorrow's midnight events into today's
     * listDay widget and inflated the truncation count. Explicit
     * `>= start` / `< end` matches FC's half-open interval semantics.
     */
    protected function buildBaseQuery(Carbon $rangeStart, Carbon $rangeEnd, array $eventTypes)
    {
        return CalendarEvent::query()
            ->where(function ($q) use ($rangeStart, $rangeEnd) {
                $q->where(function ($inner) use ($rangeStart, $rangeEnd) {
                    $inner->where('start', '>=', $rangeStart)
                        ->where('start', '<', $rangeEnd);
                })
                    ->orWhere(function ($inner) use ($rangeStart, $rangeEnd) {
                        $inner->where('end', '>=', $rangeStart)
                            ->where('end', '<', $rangeEnd);
                    })
                    ->orWhere(function ($inner) use ($rangeStart, $rangeEnd) {
                        $inner->where('start', '<', $rangeStart)
                            ->where('end', '>=', $rangeEnd);
                    });
            })
            ->when(! empty($eventTypes), fn ($q) => $q->whereIn('event_type', $eventTypes));
    }

    /**
     * One query per distinct source_type, keyed by id for O(1) lookup
     * in buildEvents. Avoids the N+1 a naive $row->source access
     * would produce. Includes trashed sources when the model uses
     * SoftDeletes so an event whose parent was soft-deleted between
     * the index write and the request still resolves for display.
     *
     * @return array<class-string, \Illuminate\Support\Collection>
     */
    protected function batchLoadSources($rows): array
    {
        $sourcesByType = [];
        foreach ($rows->groupBy('source_type') as $sourceType => $typeRows) {
            if (! class_exists($sourceType)) {
                continue;
            }
            $ids = $typeRows->pluck('source_id')->unique()->all();
            $sourcesByType[$sourceType] = $sourceType::query()
                ->when(
                    in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($sourceType), true),
                    fn ($q) => $q->withTrashed(),
                )
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');
        }

        return $sourcesByType;
    }

    /**
     * Filters rows down to those whose source still exists AND whose
     * source the current viewer can `view` under its own policy. Actual
     * event shaping happens in CalendarEventsTransformer once the row
     * set is known-safe to serialize.
     *
     * Per-row access via each source's own view policy: without this,
     * view-Asset holders without a particular license access would
     * still see its expiration event. Missing source means the parent
     * was deleted between the index write and now (mid-request delete
     * without a completed observer cascade); reconcile command cleans
     * up the orphan on its next pass.
     *
     * @return \Illuminate\Support\Collection<int, CalendarEvent>
     */
    protected function filterAuthorizedRows($rows, array $sourcesByType, Request $request)
    {
        $viewer = $request->user();

        return $rows->filter(function (CalendarEvent $row) use ($sourcesByType, $viewer) {
            $source = $sourcesByType[$row->source_type][$row->source_id] ?? null;

            if (! $source) {
                return false;
            }

            // Belt and suspenders against issue #19760: hide events
            // whose source has been soft-deleted. The display is the
            // user-visible symptom. The sync-path gate in
            // HasCalendarEvents::syncCalendarEvents prevents new ones
            // from being written in the first place, and the cleanup
            // migration soft-deletes any already written.
            if (method_exists($source, 'trashed') && $source->trashed()) {
                return false;
            }

            return $viewer?->can('view', $source);
        })->values();
    }
}
