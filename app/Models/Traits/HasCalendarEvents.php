<?php

namespace App\Models\Traits;

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Opts a model into publishing rows into the calendar_events index
 * table so its date/datetime columns show up on the unified calendar
 * view (and any per-entity calendar page that queries the index).
 *
 * A source model implements calendarEventDefinitions() to declare
 * which of its columns become events. The trait's observer keeps the
 * index in sync on save / delete / restore. Nothing beyond dates gets
 * stored on the index row - name, url, color, and any extras are
 * resolved live from the source model at read time so this table
 * never drifts from source values that aren't date-shaped.
 *
 * Consumer models MUST implement:
 *
 *   public function calendarEventDefinitions(): array
 *   {
 *       return [
 *           [
 *               'field' => 'start_date',                    // required, source column OR accessor name
 *               'event_type' => 'maintenance.start',        // required, filter key
 *               'end_field' => 'expected_completion_date',  // optional, range event's end
 *               'trigger_fields' => ['status', 'foo_id'],   // optional, extra columns that must trigger a re-sync
 *                                                           // (needed when `field` is an accessor derived from
 *                                                           //  other columns, since getChanges doesn't see the
 *                                                           //  accessor itself)
 *               'all_day' => true,                          // optional, explicit all-day flag (overrides the
 *                                                           //  controller's cast-based auto-detection - useful when
 *                                                           //  `field` is an accessor that returns a bare Carbon
 *                                                           //  without a companion cast declaration)
 *           ],
 *           // additional entries per source column that becomes an event
 *       ];
 *   }
 */
trait HasCalendarEvents
{
    /**
     * Register the observer bindings on model boot. Runs once per
     * source model class per request. The class-static approach
     * lets the observer methods hit $this via the model callback
     * signature.
     *
     * Uses `created` + `updated` separately (not the combined `saved`)
     * so we can tell an actual INSERT from a later re-save on the
     * same in-memory instance. wasRecentlyCreated persists on a
     * model instance across delete/restore cycles, so branching on
     * it inside a `saved` hook made restores look like inserts and
     * hit the unique-constraint on updateOrCreate. Hooking the two
     * distinct events keeps the intent clear at the callsite too.
     */
    public static function bootHasCalendarEvents(): void
    {
        // Defer the sync via DB::afterCommit so the calendar_events upsert
        // fires AFTER the enclosing DB::transaction commits
        //  rather than inside it.
        static::created(function ($model) {
            DB::afterCommit(function () use ($model) {
                $model->forceSyncCalendarEvents();
            });
        });

        static::updated(function ($model) {
            DB::afterCommit(function () use ($model) {
                $model->syncCalendarEvents();
            });
        });

        static::deleted(function ($model) {
            // Soft-deletes cascade to the calendar_events rows too
            // (CalendarEvent uses SoftDeletes), so the filter WHERE
            // on the read path skips them automatically. Hard deletes
            // on the source cascade to a hard delete on the index
            // rows since the parent record is gone entirely.
            $usesSoft = in_array(SoftDeletes::class, class_uses_recursive($model), true);
            if ($usesSoft && $model->isForceDeleting()) {
                CalendarEvent::withTrashed()
                    ->where('source_type', $model::class)
                    ->where('source_id', $model->getKey())
                    ->forceDelete();

                return;
            }

            CalendarEvent::where('source_type', $model::class)
                ->where('source_id', $model->getKey())
                ->delete();
        });

        static::registerRestoredIfSoftDeletes();
    }

    /**
     * Wire up the restored() hook only when the source model itself
     * uses SoftDeletes. Calling static::restored() on a model without
     * the trait would throw at boot time.
     */
    protected static function registerRestoredIfSoftDeletes(): void
    {
        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(function ($model) {
                // Undelete any calendar_events rows this source
                // published before its own soft-delete. Query-builder
                // restore (not Model::restore) so no cascade of
                // additional model events fires here - the source's
                // own save() during restore() already ran through the
                // saved observer for anything date-related that
                // changed.
                CalendarEvent::withTrashed()
                    ->where('source_type', $model::class)
                    ->where('source_id', $model->getKey())
                    ->restore();

                // Also force a full re-sync so dates that drifted
                // between delete and restore get corrected, and so
                // the one-shot cleanup migration's force-deleted
                // calendar_events (see issue #19760) get rebuilt
                // from scratch on restore instead of coming back
                // empty. Deferred via afterCommit for the same
                // deadlock-surface reasoning as created / updated.
                DB::afterCommit(function () use ($model) {
                    $model->forceSyncCalendarEvents();
                });
            });
        }
    }

    /**
     * Upsert the calendar_events row for every declared field with a
     * populated date, and delete rows whose declared field is now
     * null. Idempotent - safe to call from the observer on every
     * save, from the reconcile command, or from a manual backfill.
     *
     * Wired into the source model's `updated` observer, so the
     * change-gate below covers the "renamed but no dates touched"
     * case. INSERTs go through forceSyncCalendarEvents() from the
     * `created` observer since getChanges is empty on freshly
     * inserted rows.
     */
    public function syncCalendarEvents(bool $force = false): void
    {
        $definitions = $this->calendarEventDefinitions();
        if (empty($definitions)) {
            return;
        }

        // Short-circuit for trashed sources so backfills, reconciles,
        // and stray saves on soft-deleted rows never create or revive
        // calendar_events. The deleted observer already soft-deleted
        // the matching rows when the source was soft-deleted, and the
        // restored observer re-syncs on restore, so leaving this
        // branch dark on trashed() is safe. See issue #19760.
        if (in_array(SoftDeletes::class, class_uses_recursive($this), true) && $this->trashed()) {
            return;
        }

        if (! $force && ! $this->calendarEventFieldsChanged($definitions)) {
            return;
        }

        foreach ($definitions as $definition) {
            $this->syncCalendarEventDefinition($definition);
        }
    }

    /**
     * Bypass the changes-check so the reconcile command can heal any
     * date drift caused by direct DB edits, migrations, or missed
     * observer fires.
     */
    public function forceSyncCalendarEvents(): void
    {
        $this->syncCalendarEvents(force: true);
    }

    /**
     * Whether any of the declared date fields (or their end_field
     * partners) were touched in the current save. Uses getChanges()
     * which is set after save() commits, matching where the observer
     * fires from.
     */
    protected function calendarEventFieldsChanged(array $definitions): bool
    {
        $changes = $this->getChanges();
        if (empty($changes)) {
            return false;
        }

        // company_id is an implicit trigger across every definition
        // because it writes to the denormalized calendar_events.company_id
        // the FMCS read filter runs against. Sources that resolve their
        // company from a parent (Maintenance via Asset) rely on the
        // parent model's own observer to cascade re-syncs, not on this
        // check, since the parent column isn't on the source's own
        // getChanges() payload.
        if (array_key_exists('company_id', $changes)) {
            return true;
        }

        foreach ($definitions as $definition) {
            if (array_key_exists($definition['field'], $changes)) {
                return true;
            }
            if (! empty($definition['end_field']) && array_key_exists($definition['end_field'], $changes)) {
                return true;
            }
            foreach ($definition['trigger_fields'] ?? [] as $triggerField) {
                if (array_key_exists($triggerField, $changes)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Upsert or delete the calendar_events row for one definition
     * entry. Deletes when the source's date column is null (the
     * event no longer exists) and upserts otherwise. Keyed on the
     * composite unique (source_type, source_id, source_field) so
     * repeated saves don't create duplicates.
     *
     * Lookup uses withTrashed so we find any soft-deleted row that
     * matches the composite key. Without this, an updateOrCreate
     * during a restore path finds nothing (soft-deleted row hidden
     * by default scope), tries INSERT, and trips the unique
     * constraint. Un-trashes the row inline if it was soft-deleted
     * so a restored source's events are immediately visible.
     */
    protected function syncCalendarEventDefinition(array $definition): void
    {
        $field = $definition['field'];
        $eventType = $definition['event_type'];
        $endField = $definition['end_field'] ?? null;

        $start = $this->{$field};

        if ($start === null || $start === '') {
            CalendarEvent::where('source_type', static::class)
                ->where('source_id', $this->getKey())
                ->where('source_field', $field)
                ->delete();

            return;
        }

        $end = $endField ? $this->{$endField} : null;
        $companyId = $this->calendarEventCompanyId();

        // Single-statement upsert instead of SELECT-then-INSERT/UPDATE.
        // Compiles to INSERT ... ON DUPLICATE KEY UPDATE on MySQL.
        //
        // The observers that call into this method dispatch it through
        // DB::afterCommit, so when a controller write path has an open
        // DB::transaction the upsert runs AFTER that transaction commits,
        // not inside it. The gap locks it takes on the composite unique
        // index live only for the duration of this single statement, with
        // no shared window against the enclosing write's row locks, so
        // the deadlock surface that forced the earlier retry wrapper is
        // gone. For callers that invoke syncCalendarEvents outside a
        // transaction (reconcile commands, direct model saves in tests),
        // DB::afterCommit fires inline, so the behavior is identical to
        // a direct call.
        $now = now();
        CalendarEvent::upsert(
            [
                [
                    'source_type' => static::class,
                    'source_id' => $this->getKey(),
                    'source_field' => $field,
                    'event_type' => $eventType,
                    'start' => $this->normalizeCalendarEventDate($start),
                    'end' => $this->normalizeCalendarEventDate($end),
                    'company_id' => $companyId,
                    'deleted_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['source_type', 'source_id', 'source_field'],
            ['event_type', 'start', 'end', 'company_id', 'deleted_at', 'updated_at']
        );
    }

    /**
     * upsert() bypasses Eloquent's attribute casting, so Carbon /
     * DateTime values on the source model have to be formatted into
     * the Y-m-d H:i:s string MySQL's datetime column expects. Scalar
     * strings pass through, null stays null.
     */
    private function normalizeCalendarEventDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }

    /**
     * Resolve the company_id this source model's calendar events belong
     * to under FMCS. Written to the denormalized `calendar_events.company_id`
     * column on every observer-driven write, so the read path can filter
     * at the SQL level before count / order / limit. The read-path null
     * handling mirrors CompanyableTrait: a null company_id means
     * "unscoped" and whether the event surfaces to a given caller depends
     * on the `null_company_is_floater` setting.
     *
     * Default is null for sources that don't have a single parent
     * company (users have the company_user pivot, requests derive their
     * company from the requestable). Source models with a direct
     * company_id column or a CompanyableChildTrait parent override this
     * to return the resolved id.
     */
    public function calendarEventCompanyId(): ?int
    {
        return null;
    }

    /**
     * Every declared calendar-event row for this source model
     * instance. Not eager-loaded anywhere by default; consumers that
     * need the reverse lookup can ->with('calendarEvents') on their
     * own query. Uses a polymorphic morphMany scoped to
     * (source_type, source_id).
     */
    public function calendarEvents()
    {
        return $this->morphMany(CalendarEvent::class, 'source');
    }

    /**
     * Contract for the source model to declare which columns become
     * calendar_events rows. Return an array of associative arrays;
     * see the class docblock for the recognized keys.
     *
     * @return array<int, array{field: string, event_type: string, end_field?: string, trigger_fields?: array<int, string>, all_day?: bool}>
     */
    abstract public function calendarEventDefinitions(): array;
}
