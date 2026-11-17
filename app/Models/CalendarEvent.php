<?php

namespace App\Models;

use App\Models\Traits\CompanyableTrait;
use App\Models\Traits\HasCalendarEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Row in the calendar_events index table. One instance per
 * (source_type, source_id, source_field) triple - a single source
 * model can publish multiple rows if its calendarEventDefinitions()
 * declares multiple fields.
 *
 * Deliberately thin. This model exists for the observer's upsert
 * path and for the read-path range/type filter query; consumers that
 * want to render events read the source model live via ->source
 * rather than pulling values off this row.
 *
 * @mixin IdeHelperCalendarEvent
 */
class CalendarEvent extends Model
{
    use CompanyableTrait;
    use SoftDeletes;

    protected $fillable = [
        'source_type',
        'source_id',
        'source_field',
        'event_type',
        'start',
        'end',
        'company_id',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    /**
     * Polymorphic parent. Loaded eagerly (batched, per-source-type)
     * by the calendar events endpoint so a single fetch resolves
     * names / urls / colors live from the source models rather than
     * from any denormalized copy on this table.
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Every eligible App\Models\* class that uses the HasCalendarEvents
     * trait. Consolidated here (instead of hardcoded per call-site) so
     * adding a new source model is a one-line change - just adopt the
     * trait and this discovery picks it up automatically.
     *
     * Callers today:
     *   - Api\CalendarEventsController::index base permission gate
     *     (viewer must be able to view at least one source type)
     *   - Console\Commands\ReconcileCalendarEvents
     *   - The two backfill migrations
     *
     * Discovery walks app/Models/*.php via glob, filters to
     * non-abstract Model subclasses that use the trait. Cached at the
     * static-property level so the reflection cost is amortized over
     * a full request cycle (glob is cheap but not free, and the count
     * queries in the controller run alongside other work anyway).
     *
     * @return array<int, class-string>
     */
    public static function sourceModels(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $sources = [];
        foreach (glob(app_path('Models').'/*.php') as $file) {
            $class = 'App\\Models\\'.pathinfo($file, PATHINFO_FILENAME);
            if (! class_exists($class)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);
            if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait()) {
                continue;
            }
            if (! is_subclass_of($class, Model::class)) {
                continue;
            }
            if (in_array(HasCalendarEvents::class, class_uses_recursive($class), true)) {
                $sources[] = $class;
            }
        }

        return $cache = $sources;
    }
}
