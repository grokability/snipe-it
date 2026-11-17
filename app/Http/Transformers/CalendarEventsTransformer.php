<?php

namespace App\Http\Transformers;

use App\Models\CalendarEvent;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Turns `calendar_events` rows + their resolved source models into the
 * FullCalendar-v6 Event Object payloads the calendar endpoint returns.
 *
 * Takes over both the shape (title with event-type label, all-day
 * detection from cast / definition, url + color from source presenter,
 * extendedProps) and the last-mile date formatting so the controller
 * doesn't have to know about any of it.
 *
 * The controller batch-loads sources per source_type (to avoid N+1)
 * and hands the pre-loaded map + filtered rows to
 * transformCalendarEvents(). Anything with no matching source or that
 * fails the per-row view policy is filtered out by the controller
 * before it reaches this transformer.
 */
class CalendarEventsTransformer
{
    /**
     * Human-readable labels for each `event_type` string emitted by
     * source models' `calendarEventDefinitions()`. Unknown types fall
     * through to the raw string so a new source at least reads
     * coherently before its label is wired up.
     *
     * @var array<string, string>
     */
    private const EVENT_TYPE_LABEL_KEYS = [
        'maintenance.start' => 'general.calendar_event_maintenance',
        'asset.audit_due' => 'general.calendar_event_asset_audit_due',
        'asset.expected_checkin' => 'general.calendar_event_asset_expected_checkin',
        'asset.checkout' => 'general.calendar_event_asset_checkout',
        'asset.eol' => 'general.calendar_event_asset_eol',
        'asset.warranty_expiration' => 'general.calendar_event_asset_warranty_expiration',
        'license.expiration' => 'general.calendar_event_license_expiration',
        'license.termination' => 'general.calendar_event_license_termination',
        'user.start_date' => 'general.calendar_event_user_start_date',
        'user.end_date' => 'general.calendar_event_user_end_date',
        'request.reservation' => 'general.calendar_event_request_reservation',
    ];

    /**
     * @param  iterable<CalendarEvent>  $rows
     * @param  array<class-string, \Illuminate\Support\Collection>  $sourcesByType
     * @return array<int, array<string, mixed>>
     */
    public function transformCalendarEvents(iterable $rows, array $sourcesByType): array
    {
        $out = [];
        foreach ($rows as $row) {
            $source = $sourcesByType[$row->source_type][$row->source_id] ?? null;
            if (! $source) {
                continue;
            }
            $out[] = $this->transformCalendarEvent($row, $source);
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public function transformCalendarEvent(CalendarEvent $row, Model $source): array
    {
        $allDay = $this->isAllDayField($source, $row->source_field);

        return [
            'id' => $row->id,
            'title' => $this->titleFor($source, $row->event_type),
            'start' => $this->formatDate($row->start, $allDay),
            'end' => $this->formatDate($row->end, $allDay, isEnd: true),
            'allDay' => $allDay,
            'url' => $this->urlFor($source),
            'color' => $this->colorFor($source),
            'extendedProps' => [
                'source_type' => $row->source_type,
                'source_id' => $row->source_id,
                'source_field' => $row->source_field,
                'event_type' => $row->event_type,
            ],
        ];
    }

    /**
     * Display name prefixed with the human-readable event-type label
     * so a viewer can tell why a row is on the calendar ("Audit due:
     * Laptop #7" vs. just "Laptop #7"). Presenter's name() is the
     * canonical Snipe-IT accessor, with a fallback to common attribute
     * names for models without a presenter wired up yet.
     *
     * Source models can expose a `calendarTitlePrefix()` method to
     * stamp a state marker at the front of the title (completed
     * maintenances use "✓ ", for example). Returning null or an
     * empty string leaves the title untouched.
     */
    private function titleFor(Model $source, string $eventType): string
    {
        if (method_exists($source, 'present')) {
            $presenter = $source->present();
            if (method_exists($presenter, 'name')) {
                $name = (string) $presenter->name();
            }
        }

        $name ??= (string) ($source->display_name ?? $source->name ?? (class_basename($source).'#'.$source->getKey()));

        $label = $this->eventTypeLabel($eventType);
        $title = $label ? sprintf('%s: %s', $label, $name) : $name;

        $prefix = method_exists($source, 'calendarTitlePrefix') ? (string) $source->calendarTitlePrefix() : '';

        return $prefix.$title;
    }

    private function eventTypeLabel(string $eventType): string
    {
        if (! array_key_exists($eventType, self::EVENT_TYPE_LABEL_KEYS)) {
            return $eventType;
        }

        return trans(self::EVENT_TYPE_LABEL_KEYS[$eventType]);
    }

    /**
     * True when the underlying source field is cast as a bare date
     * (not a datetime). Rendering these on a timegrid as ISO-8601
     * with a T00:00:00 component stacks the whole day's events at
     * midnight; FullCalendar's `allDay: true` mode turns them into a
     * solid bar across the day instead.
     */
    private function isAllDayField(Model $source, ?string $field): bool
    {
        if (! $field) {
            return false;
        }

        if (method_exists($source, 'calendarEventDefinitions')) {
            foreach ($source->calendarEventDefinitions() as $definition) {
                if (($definition['field'] ?? null) === $field && array_key_exists('all_day', $definition)) {
                    return (bool) $definition['all_day'];
                }
            }
        }

        $cast = $source->getCasts()[$field] ?? null;

        return in_array($cast, ['date', 'immutable_date'], true);
    }

    /**
     * Normalize to Carbon so we get toIso8601String() (Carbon-only,
     * not on DateTimeInterface). All-day events serialize as
     * `YYYY-MM-DD` (no time component) so FullCalendar doesn't
     * reinterpret them as UTC midnight in the browser's local zone.
     *
     * FullCalendar treats all-day `end` as EXCLUSIVE: an event with
     * end=2026-10-15 renders only through 2026-10-14. Range events
     * (maintenance, request reservations) therefore need end+1 so a
     * same-day completion still paints on that day. Only applied
     * when $isEnd is true so starts stay anchored on the day they
     * actually begin.
     */
    private function formatDate(mixed $date, bool $allDay, bool $isEnd = false): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        $date = $date instanceof Carbon
            ? $date
            : ($date instanceof DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date));

        if ($allDay && $isEnd) {
            $date = $date->copy()->addDay();
        }

        return $allDay ? $date->format('Y-m-d') : $date->toIso8601String();
    }

    private function urlFor(Model $source): ?string
    {
        // Source model can expose calendarUrl() directly (used by
        // CheckoutRequest which doesn't ride the Presenter/Presentable
        // chain) or via its presenter (Asset/License/etc use the
        // presenter path).
        if (method_exists($source, 'calendarUrl')) {
            return $source->calendarUrl();
        }

        if (method_exists($source, 'present')) {
            $presenter = $source->present();
            if (method_exists($presenter, 'calendarUrl')) {
                return $presenter->calendarUrl();
            }
        }

        return null;
    }

    /**
     * Null means "no per-event color". The frontend paints from a
     * per-event_type CSS palette so uncolored installs still read
     * cleanly.
     */
    private function colorFor(Model $source): ?string
    {
        if (method_exists($source, 'calendarColor')) {
            return $source->calendarColor();
        }

        if (method_exists($source, 'present')) {
            $presenter = $source->present();
            if (method_exists($presenter, 'calendarColor')) {
                return $presenter->calendarColor();
            }
        }

        return null;
    }
}
