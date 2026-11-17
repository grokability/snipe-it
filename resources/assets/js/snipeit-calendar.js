/**
 * Snipe-IT calendar init.
 *
 * Thin wrapper around FullCalendar v6 that per-entity calendar pages
 * call via a single global (window.snipeitCalendar.init). The reason
 * for the wrapper is that we expect several calendar views over time
 * (maintenances is the first, but expected asset checkins, upcoming
 * audits, user end_dates and similar are already on the list) and
 * having each blade page duplicate the FullCalendar wiring would
 * create N places to keep in sync when we upgrade or change defaults.
 *
 * Usage from a blade page:
 *
 *   <div id="my-calendar"></div>
 *   <script>
 *     window.snipeitCalendar.init('my-calendar', {
 *       events: '/api/v1/whatever/events',   // JSON feed URL
 *       initialView: 'dayGridMonth',          // optional, default
 *       locale: 'en',                          // optional
 *     });
 *   </script>
 *
 * The events endpoint must return FullCalendar's event object shape:
 *   [ { id, title, start, end?, url?, color?, extendedProps? }, ... ]
 * FullCalendar sends `start` and `end` query params for the range it
 * needs; the server can either honor them (recommended) or ignore
 * and always return the full set.
 */

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';

/**
 * Fetch events from our own /api/v1/... endpoints with the session
 * X-CSRF-TOKEN header attached, matching how every bootstrap-table
 * datatable authenticates browser calls to Snipe-IT's API. Without
 * this header the /api/v1/* routes reject session-authed browser
 * requests, and the calendar renders empty even though the query
 * returns rows. Passes through FullCalendar's `start` and `end`
 * range as query params so the server-side range filter works.
 *
 * The optional `state` object lets per-calendar UI (like filter
 * buttons) inject additional query params into every fetch. Read on
 * each call so mutating state.filter and triggering refetchEvents()
 * ships the new value on the very next request without rebuilding
 * the events source. `state.filter` can be a string (legacy single-
 * select mode, unified maintenance events endpoint) or a Set of
 * strings (multi-select mode - passed as ?filter=a,b so the endpoint
 * can WHERE-IN them).
 */
function fetchEventsFromSnipeApi(url, state) {
    return function (fetchInfo, successCallback, failureCallback) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        const separator = url.includes('?') ? '&' : '?';
        const params = new URLSearchParams({
            start: fetchInfo.startStr,
            end: fetchInfo.endStr,
        });
        if (state && state.limit) {
            params.set('limit', String(state.limit));
        }
        if (state && state.filter) {
            if (state.filter instanceof Set && state.filter.size > 0) {
                params.set('filter', Array.from(state.filter).join(','));
                Array.from(state.filter).forEach(function (v) {
                    params.append('event_type[]', v);
                });
            } else if (typeof state.filter === 'string') {
                params.set('filter', state.filter);
            }
        }

        fetch(url + separator + params.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Events fetch failed: ' + response.status);
                }
                return response.json();
            })
            .then(function (data) {
                // Backend wraps events in { events, truncated, total }
                // so the frontend can surface a "narrowing filters"
                // banner when the server-side cap is hit. Tolerates the
                // bare-array shape too so any legacy caller keeps
                // working.
                const events = Array.isArray(data) ? data : (data.events || []);
                if (state && typeof state.onFetchMeta === 'function') {
                    state.onFetchMeta({
                        truncated: !!(data && data.truncated),
                        total: data ? data.total : events.length,
                        returned: events.length,
                    });
                }
                successCallback(events);
            })
            .catch(function (err) {
                failureCallback(err);
            });
    };
}

/**
 * Build FullCalendar customButtons + toolbar-slot string from a
 * per-calendar filter-button config. Two modes:
 *
 *   - Single-select (default): each click sets state.filter to that
 *     button's name and clears the others. Matches the
 *     "completed / active / due_soon" pattern used by the maintenance-
 *     scoped events endpoint.
 *   - Multi-select (opts.multi === true): each click toggles that
 *     button's name in a Set on state.filter. Empty Set = "show
 *     everything". Matches the unified calendar's "show maintenance
 *     / audits / checkins / etc." checkbox-cluster UI.
 *
 * Also flips a `.fc-button-active` class on the just-clicked button
 * so users see which filter(s) are currently applied.
 */
function buildFilterButtons(filterButtons, state, getCalendar, opts) {
    const multi = !!(opts && opts.multi);
    const urlStateEnabled = !(opts && opts.urlState === false);
    if (multi && !(state.filter instanceof Set)) {
        // Normalize state.filter to a Set for multi mode - callers
        // can pass a plain array (or null / undefined) in config and
        // we'll wrap it up.
        state.filter = new Set(Array.isArray(state.filter) ? state.filter : []);
    }

    // Render as native DOM in a container that lives above the
    // calendar element. Previously these were injected into
    // FullCalendar's headerToolbar `left` slot, which shoved the
    // month title far to the right on views where the title is
    // centered. Native-DOM rendering keeps FC's own toolbar
    // (prev/next/today/title/views) intact and gives filters their
    // own row above.
    const container = document.createElement('div');
    container.className = 'snipeit-calendar-filters';
    container.setAttribute('role', 'group');
    container.setAttribute('aria-label', 'Calendar filters');

    const buttonNodes = {};
    filterButtons.forEach(function (btn) {
        const buttonEl = document.createElement('button');
        buttonEl.type = 'button';
        // .btn-theme is Snipe-IT's own themed-color button chrome
        // (defined in overrides.less); .snipeit-calendar-filter-button
        // is our own hook that layers the checkmark + selected-state
        // ring on top. FullCalendar's .fc-button styles are scoped
        // inside `.fc` and don't apply to buttons rendered outside the
        // calendar element, which is why the earlier fc-button chrome
        // fell back to OS defaults.
        buttonEl.className = 'btn btn-theme btn-sm snipeit-calendar-filter-button';
        buttonEl.textContent = btn.text;
        buttonEl.setAttribute('aria-pressed', 'false');
        buttonEl.dataset.filterName = btn.name;
        buttonEl.addEventListener('click', function () {
            if (multi) {
                if (state.filter.has(btn.name)) {
                    state.filter.delete(btn.name);
                    buttonEl.classList.remove('active');
                    buttonEl.setAttribute('aria-pressed', 'false');
                } else {
                    state.filter.add(btn.name);
                    buttonEl.classList.add('active');
                    buttonEl.setAttribute('aria-pressed', 'true');
                }
            } else {
                state.filter = btn.name;
                Object.keys(buttonNodes).forEach(function (otherName) {
                    buttonNodes[otherName].classList.remove('active');
                    buttonNodes[otherName].setAttribute('aria-pressed', 'false');
                });
                buttonEl.classList.add('active');
                buttonEl.setAttribute('aria-pressed', 'true');
            }
            if (urlStateEnabled) {
                writeUrlState({ filter: serializeFilterForUrl(state.filter) });
            }
            const calendar = getCalendar();
            if (calendar) calendar.refetchEvents();
        });
        container.appendChild(buttonEl);
        buttonNodes[btn.name] = buttonEl;
    });

    return { container: container, buttonNodes: buttonNodes };
}

/**
 * Convert a PHP date() format string (what Snipe-IT stores in
 * settings.date_display_format and user overrides thereof) into a
 * FullCalendar / Cmdlet-style format string. Only handles the tokens
 * Snipe-IT actually uses in its shipped date-format options; unknown
 * tokens pass through as literal characters, which is safe because
 * the Cmdlet parser treats them as verbatim text.
 *
 * Used to feed dayPopoverFormat + potentially other locale-aware
 * FullCalendar format slots so the calendar's date labels read the
 * same way dates read in the rest of the app.
 */
function phpDateFormatToFullCalendar(phpFormat) {
    if (!phpFormat) return '';
    const map = {
        'Y': 'yyyy', 'y': 'yy',
        'F': 'MMMM', 'M': 'MMM', 'm': 'MM', 'n': 'M',
        'l': 'dddd', 'D': 'ddd', 'd': 'dd', 'j': 'd',
        'H': 'HH', 'G': 'H', 'h': 'hh', 'g': 'h',
        'i': 'mm', 's': 'ss', 'a': 'a', 'A': 'A',
    };
    let out = '';
    for (const c of phpFormat) {
        out += Object.prototype.hasOwnProperty.call(map, c) ? map[c] : c;
    }
    return out;
}

/**
 * URL <-> calendar-state syncing. Reads ?date, ?view, ?filter on
 * init and applies them as initial state; writes them back on
 * navigation (prev/next/today), view change, and filter click so
 * the current URL is always a shareable deep-link into the exact
 * state the user is looking at.
 *
 *   ?date=YYYY-MM-DD     - FullCalendar's initialDate
 *   ?view=dayGridMonth   - initialView (whatever plugins support)
 *   ?filter=a,b,c        - comma-separated event_type list (multi
 *                          mode) or single event_type (single mode)
 *
 * Uses replaceState rather than pushState so a scroll through
 * months doesn't stuff the browser back-stack with every visited
 * date. Users who want history entries can bookmark specific URLs.
 */
function readUrlState() {
    const params = new URLSearchParams(window.location.search);
    return {
        date: params.get('date'),
        view: params.get('view'),
        filter: params.get('filter'),
    };
}

/**
 * Format a Date as YYYY-MM-DD using its LOCAL components. FullCalendar
 * hands us a Date whose midnight is in the browser's local timezone;
 * calling .toISOString() first shifts it to UTC and then serializes,
 * which subtracts hours in UTC+N zones. That collapsed "Aug 1 local"
 * into "Jul 31 UTC" for anyone east of Greenwich, so reloading after
 * a Today click bounced them to the prior month. Reading local
 * getFullYear/getMonth/getDate keeps the calendar day stable no
 * matter the timezone.
 */
function toLocalYmd(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + d;
}

function writeUrlState(patch) {
    const params = new URLSearchParams(window.location.search);
    Object.keys(patch).forEach(function (key) {
        const value = patch[key];
        if (value === null || value === undefined || value === '') {
            params.delete(key);
        } else {
            params.set(key, value);
        }
    });
    const qs = params.toString();
    const url = window.location.pathname + (qs ? '?' + qs : '') + window.location.hash;
    window.history.replaceState(null, '', url);
}

function serializeFilterForUrl(filter) {
    if (filter instanceof Set) {
        return filter.size > 0 ? Array.from(filter).join(',') : null;
    }
    if (typeof filter === 'string' && filter) {
        return filter;
    }
    return null;
}

function init(elementId, config) {
    const el = document.getElementById(elementId);
    if (!el) {
        return null;
    }

    // URL-state read-in. Callers opt out with `urlState: false` in
    // config (default is on), for e.g. dashboard-widget calendars
    // that shouldn't try to steal the page's URL. Read once at init
    // time; the initial state comes from URL if present, from
    // config otherwise.
    const urlStateEnabled = config.urlState !== false;
    const urlState = urlStateEnabled ? readUrlState() : { date: null, view: null, filter: null };

    // Shared state that fetchEventsFromSnipeApi reads on each fetch.
    // The filter-buttons machinery below mutates state.filter and
    // triggers refetchEvents(), which re-runs the fetcher, which
    // picks up the new filter as a query param. Externalizing state
    // lets buttons and any future controls (search box, category
    // multi-select, etc.) coordinate through one object.
    let initialFilter = config.filter || null;
    if (urlState.filter) {
        // Multi-mode caller (filterMulti: true) always parses to an
        // array; single-mode gets the raw string. buildFilterButtons
        // wraps the array as a Set later.
        initialFilter = config.filterMulti
            ? urlState.filter.split(',').filter(Boolean)
            : urlState.filter;
    }
    const state = {
        filter: initialFilter,
        limit: config.limit || null,
        onFetchMeta: typeof config.onFetchMeta === 'function' ? config.onFetchMeta : null,
    };

    // Late-bind calendar so button handlers can grab the instance
    // (the calendar isn't constructed yet at the time customButtons
    // is defined). Assigned right before .render() below.
    let calendarInstance = null;
    const getCalendar = function () { return calendarInstance; };

    // Callers pass `events` as a URL (most common: our own /api/v1
    // events endpoint). Wrap that URL in the CSRF-authed fetcher so
    // the API accepts the request. Callers who pass a function
    // themselves get to skip this and manage the fetch on their own.
    let eventsSource = config.events;
    if (typeof eventsSource === 'string') {
        eventsSource = fetchEventsFromSnipeApi(eventsSource, state);
    }

    // If the caller declared filter buttons, build a standalone DOM
    // container that gets inserted *above* the calendar element (not
    // into FullCalendar's headerToolbar). Keeps the FC toolbar clean
    // so the month title stays centered instead of getting shoved to
    // the right by an inline row of filter chips.
    let filterContainer = null;
    let filterButtonNodes = {};
    if (Array.isArray(config.filterButtons) && config.filterButtons.length > 0) {
        const built = buildFilterButtons(config.filterButtons, state, getCalendar, {
            multi: !!config.filterMulti,
            urlState: urlStateEnabled,
        });
        filterContainer = built.container;
        filterButtonNodes = built.buttonNodes;
        el.parentNode.insertBefore(filterContainer, el);
    }

    // Peel the wrapper-only keys out of `config` before we spread the
    // rest into FullCalendar's options. FC treats anything it doesn't
    // recognize as an unknown option and warns to the console. These
    // are all consumed above or below (state.filter, state.limit,
    // state.onFetchMeta, the buildFilterButtons branch, the
    // phpDateFormat -> dayPopoverFormat translation, our wrapped
    // eventsSource overriding config.events). `fcConfig` carries just
    // the FC-recognized overrides the caller wants to apply.
    const {
        urlState: _urlState,
        limit: _limit,
        onFetchMeta: _onFetchMeta,
        filter: _filter,
        filterMulti: _filterMulti,
        filterButtons: _filterButtons,
        phpDateFormat: _phpDateFormat,
        events: _events,
        ...fcConfig
    } = config;

    const options = Object.assign(
        {
            initialView: urlState.view || 'dayGridMonth',
            initialDate: urlState.date || undefined,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            },
            height: 'auto',
            // firstDay follows the site-wide week_start (0=Sun, 1=Mon,
            // etc.) that layouts/default.blade.php already publishes
            // via window.snipeit.settings.first_day_of_week. Falls back
            // to 0 (Sunday) when the calendar is loaded on a page that
            // hasn't set it.
            firstDay: (window.snipeit && window.snipeit.settings && typeof window.snipeit.settings.first_day_of_week === 'number')
                ? window.snipeit.settings.first_day_of_week
                : 0,
            // If the event object carries a `url`, clicking it navigates
            // via a normal same-tab link rather than a popup. Individual
            // callers can override with their own eventClick handler.
            //
            // The scheme allowlist below guards window.location.href
            // against a `javascript:` / `data:` / `vbscript:` URI
            // sneaking in through the event payload (defence-in-depth:
            // the API builds URLs from source-model presenters we
            // control, but treating anything that flows into
            // location.href as untrusted is cheap and closes a class
            // of XSS the linter flagged).
            eventClick: config.eventClick || function (info) {
                var url = info.event.url;
                if (url && /^(https?:\/\/|\/)/i.test(url)) {
                    info.jsEvent.preventDefault();
                    window.location.href = url;
                }
            },
            // datesSet fires on every navigation (prev/next/today) AND
            // on view change AND on initial render. Write the current
            // date + view back to the URL so scrolling around leaves
            // a shareable link at every step. Skipped when the caller
            // opted out of URL syncing via urlState: false.
            datesSet: function (arg) {
                if (!urlStateEnabled) return;
                writeUrlState({
                    date: toLocalYmd(arg.view.currentStart),
                    view: arg.view.type,
                });
            },
        },
        // Allow per-page overrides for any of the above.
        fcConfig,
    );

    // plugins + events are always set by us - callers can't drop the
    // plugins without breaking the calendar, and the wrapped events
    // source above needs to win over the raw string from config.
    // dayGrid: month view (default calendar page look).
    // timeGrid: week/day views with hour-of-day rails, matches
    //   https://fullcalendar.io/docs/timegrid-view.
    // list: agenda-style list view used by dashboard/today widgets
    //   and as a text-first alternative for a11y users.
    // interaction: click / drag / select handlers - always required.
    options.plugins = [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin];
    options.events = eventsSource;

    // dayPopoverFormat controls the header of the "+N more" popover
    // for days that carry too many events to fit inline. When the
    // caller passes a phpDateFormat (from the site's setting or the
    // per-user override), convert to FullCalendar's token style so
    // the popover date reads the same as dates elsewhere in Snipe-IT.
    // Left as-is if the caller doesn't provide one - FullCalendar's
    // locale-aware default takes over.
    if (config.phpDateFormat && !options.dayPopoverFormat) {
        options.dayPopoverFormat = phpDateFormatToFullCalendar(config.phpDateFormat);
    }

    calendarInstance = new Calendar(el, options);
    calendarInstance.render();

    // Prime the "active" visual state on the initially-selected
    // filter button(s) so the UI reflects state.filter from the
    // start. Single-mode uses one string; multi-mode uses a Set.
    if (filterContainer) {
        const initialActive = state.filter instanceof Set
            ? Array.from(state.filter)
            : (state.filter ? [state.filter] : []);
        initialActive.forEach(function (name) {
            const btn = filterButtonNodes[name];
            if (btn) {
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');
            }
        });
    }

    return calendarInstance;
}

window.snipeitCalendar = { init };
