#!/usr/bin/env node
/**
 * Screenshotter: Playwright-driven UI walkthrough that writes PNGs to
 * `.screenshotter/screenshots/`. See `.screenshotter/README.md` for a
 * full how-to, requirements, and data-caution notes.
 *
 * Usage:
 *   npm run screenshotter
 *   node .screenshotter/src/screenshotter.mjs
 *
 * Env overrides:
 *   BASE_URL         (default: https://snipe-it.test)
 *   USERNAME         (default: admin)
 *   PASSWORD         (default: password)
 *   OUT              (default: .screenshotter/screenshots, gitignored)
 *   HEADLESS         (default: true; set to "false" to watch it run)
 *   VIEWPORT_WIDTH   (default: 1840)
 *   VIEWPORT_HEIGHT  (default: 900)
 *   FRAME            (default: true; wrap each shot in local browser chrome.
 *                     Set FRAME=false to skip the frame post-processing.)
 *   ALL_ROUTES       (default: true; set to "false" to skip the full-app sweep)
 *   TABLE_PAGE_SIZE  (default: 10; caps bootstrap-table rows per shot)
 *   SUBMIT_FORMS     (default: true; posts each edit form after the
 *                     `-edit` shot to capture the `-edit-submitted` state.
 *                     Set SUBMIT_FORMS=false to skip the writes.)
 *   TABS             (default: false; opt in with TABS=true to walk each
 *                     view page's Bootstrap tabs and capture each tab's
 *                     content as `{section}/{user}-view-tab-{slug}`.)
 *
 * Section filter (skip everything except the named sections):
 *   node .screenshotter/src/screenshotter.mjs --section assets,licenses
 *
 *   Sections match the directory name under screenshots/, e.g.
 *   `assets`, `licenses`, `users`, `dashboard`, `settings`, `reports`,
 *   `custom-fields`, `fieldsets`, `maintenance-types`, etc. Managers
 *   are included automatically when their section is in the filter.
 *   Other sections' shots from prior runs are preserved (not wiped).
 *
 * Ad-hoc single-shot mode (skips the full walkthrough):
 *   node .screenshotter/src/screenshotter.mjs --one <path> [--as <username>] [--name <slug>] [--tab <label>]
 *
 *   Examples:
 *     node .screenshotter/src/screenshotter.mjs --one /hardware
 *     node .screenshotter/src/screenshotter.mjs --one /hardware/create --as assetmgr
 *     node .screenshotter/src/screenshotter.mjs --one /licenses/5 --as licensemgr --name license-detail
 *     node .screenshotter/src/screenshotter.mjs --one /hardware/1 --tab licenses
 *
 * DATA WARNING: this script captures whatever is currently rendering in
 * the connected database, in full. Do not run against production,
 * staging that mirrors production, or any DB containing real customer
 * data, PII, uploaded avatars/documents, or license keys. A freshly
 * seeded demo DB is the safe and reproducible default.
 */

import {chromium} from 'playwright';
import {mkdir, readdir, readFile, rm, writeFile} from 'node:fs/promises';
import {dirname, resolve} from 'node:path';
import {spawnSync} from 'node:child_process';
import {parseArgs} from 'node:util';

const BASE_URL = process.env.BASE_URL ?? 'https://snipe-it.test';
const USERNAME = process.env.USERNAME ?? 'admin';
const PASSWORD = process.env.PASSWORD ?? 'password';
const OUT = resolve(process.env.OUT ?? '.screenshotter/screenshots');
const HEADLESS = process.env.HEADLESS !== 'false';
const VIEWPORT = {
    width: Number(process.env.VIEWPORT_WIDTH ?? 1840),
    height: Number(process.env.VIEWPORT_HEIGHT ?? 900),
};
// Framing is on by default because the raw shots look bare without
// browser chrome. Set FRAME=false to skip the frame post-processing.
const FRAME = process.env.FRAME !== 'false';
const ALL_ROUTES = process.env.ALL_ROUTES !== 'false';
const TABLE_PAGE_SIZE = Number(process.env.TABLE_PAGE_SIZE ?? 10);
// Whether to submit edit forms after screenshotting them (to capture the
// success callout / validation-error state). Default on, since that's
// the whole point of the "-edit-submitted" shot. Set SUBMIT_FORMS=false
// to skip the write-back and avoid the resulting action_log entries,
// observer/notification/webhook fires, etc.
const SUBMIT_FORMS = process.env.SUBMIT_FORMS !== 'false';
// Whether to walk each view page's Bootstrap tabs and shoot each tab
// pane. Default OFF because tabs multiply the shot count per view page
// (a page with 5 tabs adds 4 extra shots per user). Set TABS=true to
// opt in when you want the full sweep.
const TABS = process.env.TABS === 'true';
// Color scheme: "light" (default), "dark", or "no-preference". Passed
// through to Playwright's emulateMedia which sets prefers-color-scheme.
// Snipe-IT honors the OS preference for dark mode when the user's
// account preference is set to "system".
const COLOR_SCHEME = process.env.COLOR_SCHEME ?? 'light';

const {values: cli} = parseArgs({
    options: {
        one: {type: 'string'},
        as: {type: 'string'},
        name: {type: 'string'},
        section: {type: 'string'},
        tab: {type: 'string'},
    },
    strict: false,
});
const ONE_SHOT = cli.one ?? null;
// Comma-separated list of section names to include; when set, everything
// else (other first-class objects, standalone pages like dashboard/
// settings/reports, the all-routes sweep, and any manager whose section
// isn't in the list) is skipped. Case-insensitive, whitespace-trimmed.
const SECTION_FILTER = cli.section
    ? new Set(cli.section.split(',').map((s) => s.trim().toLowerCase()).filter(Boolean))
    : null;
const includesSection = (name) => !SECTION_FILTER || SECTION_FILTER.has(name.toLowerCase());

const RUN_TIMESTAMP = (() => {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}-${pad(d.getHours())}${pad(d.getMinutes())}${pad(d.getSeconds())}`;
})();
const RUN_STARTED_AT = performance.now();

console.log(`Base URL:  ${BASE_URL}`);
console.log(`Login as:  ${cli.as ?? USERNAME}`);
console.log(`Output:    ${OUT}`);
console.log(`Viewport:  ${VIEWPORT.width}x${VIEWPORT.height}`);
console.log(`Framing:   ${FRAME ? 'local (generic-light)' : 'off'}`);
console.log(`Submit:    ${SUBMIT_FORMS ? 'on (edit forms are posted after shot)' : 'off (edit forms are not posted)'}`);
console.log(`Tabs:      ${TABS ? 'on (view pages walk each tab)' : 'off (view pages shoot base only)'}`);
console.log(`Color:     ${COLOR_SCHEME}`);
if (SECTION_FILTER) console.log(`Sections:  ${[...SECTION_FILTER].join(', ')}`);
if (ONE_SHOT) console.log(`Mode:      ad-hoc single shot (${ONE_SHOT})`);
console.log('');

// Wipe walkthrough shots so a full run never leaves stale images mixed
// with fresh ones. Preserve `adhoc/` so historical one-off images stick
// around. The script itself lives outside OUT (in .screenshotter/src/)
// so it can never accidentally be included in the wipe.
//
// When --section is used we only wipe the section dirs we're about to
// regenerate; other sections' shots from previous runs are preserved.
// Ad-hoc mode itself never wipes anything.
await mkdir(OUT, {recursive: true});
if (!ONE_SHOT) {
    const entries = await readdir(OUT, {withFileTypes: true});
    for (const entry of entries) {
        if (entry.name === 'adhoc') continue;
        if (SECTION_FILTER && !SECTION_FILTER.has(entry.name.toLowerCase())) continue;
        await rm(`${OUT}/${entry.name}`, {recursive: true, force: true});
    }
}

const browser = await chromium.launch({headless: HEADLESS});
const context = await browser.newContext({
    viewport: VIEWPORT,
    // Herd's local .test domains use self-signed certs.
    ignoreHTTPSErrors: true,
    // Set prefers-color-scheme so apps that honor it (Snipe-IT, when
    // the user's theme preference is "system") render in the requested
    // scheme without needing to click any in-app toggle.
    colorScheme: COLOR_SCHEME,
});

// Block debugbar / telescope / clockwork asset requests at the network
// level. If their JS never loads, their overlays can never render, and
// we don't have to fight CSS specificity to hide them (which was the
// approach in an earlier version and broke on Snipe-IT error pages
// where debugbar rendered visible JSON collector panels that our
// CSS selectors couldn't reach). Belt-and-suspenders: we also inject
// the hiding CSS via addInitScript below for any dev-tool asset paths
// this block misses.
await context.route('**/_debugbar/**', (route) => route.abort());
await context.route('**/telescope/**', (route) => route.abort());
await context.route('**/telescope-toolbar/**', (route) => route.abort());
await context.route('**/clockwork/**', (route) => route.abort());
await context.route('**/__clockwork/**', (route) => route.abort());

// Hide dev-only UI chrome from every page. Debugbar/Telescope/Clockwork
// would otherwise land in the middle of shots.
await context.addInitScript(() => {
    const inject = () => {
        if (document.getElementById('__screenshotter-hide')) return;
        const style = document.createElement('style');
        style.id = '__screenshotter-hide';
        style.textContent = `
            /* Laravel Debugbar: hide any element whose id or class
               starts with / contains "phpdebugbar". Catches the main
               toolbar, the resize handle, the open handler, and the
               expanded collector panels that render on error pages
               (which the narrower "exact-id" selectors missed). */
            [id^="phpdebugbar"], [class*="phpdebugbar"],
            /* Laravel Telescope Toolbar */
            [id^="telescope-toolbar"], [class*="telescope-toolbar"],
            /* Clockwork */
            #clockwork, #clockwork-toolbar,
            [data-clockwork], iframe[src*="clockwork"] {
                display: none !important;
            }
        `;
        (document.head || document.documentElement).appendChild(style);
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inject);
    } else {
        inject();
    }
});

const page = await context.newPage();
let shotCount = 0;

/**
 * Take a screenshot and log it. Auto-creates parent dirs for names with
 * slashes. Appends the run timestamp. When FRAME=true, post-processes
 * through the local frame template.
 */
async function shot(name, opts = {}) {
    const path = `${OUT}/${name}-${RUN_TIMESTAMP}.png`;
    await mkdir(dirname(path), {recursive: true});
    const {element, ...playwrightOpts} = opts;
    await page.waitForLoadState('networkidle').catch(() => {});
    // Cap bootstrap-table pagination on every shot, not just after
    // explicit waitForTable() calls. Post-submit redirects and other
    // "arrived here without knowing there's a table" flows would
    // otherwise render at the user's persisted per-page preference
    // (often 20 or 25). Safe no-op on pages without a snipe-table.
    await capPagination();
    // Capture the current URL path for the frame address bar (relative
    // path only, so the frame doesn't leak / dumb-look the local .test
    // hostname). Grabbed at shot time rather than inside frameLocally
    // because element-scoped screenshots may not represent a whole page.
    const pageUrl = new URL(page.url());
    const addressBar = pageUrl.pathname + (pageUrl.search || '');
    if (element) {
        await element.screenshot({path, ...playwrightOpts});
    } else {
        await page.screenshot({path, fullPage: true, ...playwrightOpts});
    }
    shotCount++;
    if (FRAME) {
        await frameLocally(path, addressBar);
        console.log(`  ✓ ${name}-${RUN_TIMESTAMP}.png (framed)`);
    } else {
        console.log(`  ✓ ${name}-${RUN_TIMESTAMP}.png`);
    }
}

/**
 * Wrap a PNG in a browser-chrome frame mimicking browserframe.com's
 * generic-light style. Runs entirely local via an inline HTML template.
 */
let framePage = null;
async function frameLocally(imagePath, urlPath = '') {
    if (!framePage) framePage = await context.newPage();
    const fp = framePage;
    const buf = await readFile(imagePath);
    const dims = pngSize(buf);
    const dataUri = 'data:image/png;base64,' + buf.toString('base64');

    // Symmetric padding so the box-shadow (radiating in all directions)
    // has room to render without clipping on any edge.
    const PADDING_X = 70;
    const PADDING_TOP = 70;
    const PADDING_BOTTOM = 70;
    const CHROME_HEIGHT = 44;
    const outerWidth = dims.width + PADDING_X * 2;
    const outerHeight = dims.height + CHROME_HEIGHT + PADDING_TOP + PADDING_BOTTOM;

    await fp.setViewportSize({width: outerWidth, height: outerHeight});

    // HTML-escape the URL path so a literal `<` / `>` / `&` in a
    // query string (rare, but possible) can't break out of the address
    // bar's text content.
    const escapedPath = urlPath.replace(/[&<>"']/g, (c) => (
        {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]
    ));

    await fp.setContent(`
<!doctype html>
<html>
<head>
<style>
  html, body { margin: 0; padding: 0; background: transparent; }
  body {
    padding: ${PADDING_TOP}px ${PADDING_X}px ${PADDING_BOTTOM}px ${PADDING_X}px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  }
  .frame {
    width: ${dims.width}px;
    background: #ffffff;
    border-radius: 10px;
    /* Ambient shadow radiating on all four sides. */
    box-shadow: 0 0 50px rgba(102, 102, 102, 0.5), 0 0 0 1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
  }
  .chrome {
    height: ${CHROME_HEIGHT}px;
    background: linear-gradient(#f6f6f6, #ececec);
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    padding: 0 14px;
    box-sizing: border-box;
    gap: 12px;
  }
  .dots { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
  .dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    box-shadow: inset 0 0 0 0.5px rgba(0, 0, 0, 0.15);
  }
  .dot.red { background: #ff5f57; }
  .dot.yellow { background: #febc2e; }
  .dot.green { background: #28c840; }
  /* Address bar: rounded pill, muted background, small monospace-ish
     text. Grown to fit the space between the traffic-light dots and
     the right edge, capped so long paths ellipsis instead of stretching
     the chrome. */
  .addr {
    flex: 1;
    max-width: 60%;
    margin: 0 auto;
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 999px;
    padding: 4px 12px;
    font-size: 12px;
    line-height: 1;
    color: #4a4a4a;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .shot { display: block; width: ${dims.width}px; height: ${dims.height}px; }
</style>
</head>
<body>
  <div class="frame">
    <div class="chrome">
      <div class="dots">
        <span class="dot red"></span>
        <span class="dot yellow"></span>
        <span class="dot green"></span>
      </div>
      ${escapedPath ? `<div class="addr">${escapedPath}</div>` : ''}
    </div>
    <img class="shot" src="${dataUri}"/>
  </div>
</body>
</html>
    `);

    await fp.evaluate(() => {
        const img = document.querySelector('img.shot');
        if (img.complete) return;
        return new Promise((resolve) => {
            img.addEventListener('load', resolve, {once: true});
            img.addEventListener('error', resolve, {once: true});
        });
    });

    const framed = await fp.screenshot({fullPage: true, omitBackground: true});
    await writeFile(imagePath, framed);
}

/**
 * Read a PNG's width and height from its IHDR chunk (bytes 16-24).
 */
function pngSize(buf) {
    return {width: buf.readUInt32BE(16), height: buf.readUInt32BE(20)};
}

/**
 * Cap any bootstrap-table on the current page to TABLE_PAGE_SIZE rows.
 * Safe to call on non-table pages.
 */
async function capPagination() {
    const shrank = await page.evaluate((pageSize) => {
        if (!window.jQuery || !window.jQuery.fn.bootstrapTable) return false;
        const $tables = window.jQuery('table.snipe-table');
        if (!$tables.length) return false;
        $tables.bootstrapTable('refreshOptions', {pageSize});
        return true;
    }, TABLE_PAGE_SIZE).catch(() => false);
    if (shrank) {
        await page.waitForSelector('.bootstrap-table:not(:has(.fixed-table-loading[style*="display: block"]))', {timeout: 15_000}).catch(() => {});
        await page.waitForTimeout(300);
    }
    return shrank;
}

/**
 * Wait for a bootstrap-table to finish its initial render, then cap
 * pagination.
 */
async function waitForTable() {
    await page.waitForSelector('.bootstrap-table:not(:has(.fixed-table-loading[style*="display: block"]))', {timeout: 15_000}).catch(() => {});
    await page.waitForTimeout(300);
    await capPagination();
}

/**
 * Return the id of the first non-actions-column entity link in the
 * current index table. Used by view/edit shots to avoid hardcoded ids
 * that change on reseed.
 */
async function getFirstEntityId(segment) {
    const href = await page.locator(`table.snipe-table tbody a[href*="/${segment}/"]`).filter({
        hasNot: page.locator('[href*="/edit"], [href*="/checkout"], [href*="/checkin"], [href*="/clone"], [href*="/delete"], [href*="/restore"]'),
    }).first().getAttribute('href').catch(() => null);
    if (!href) return null;
    const m = href.match(new RegExp(`/${segment}/(\\d+)(?:$|[/?#])`));
    return m ? m[1] : null;
}

/**
 * Take index + view + edit shots for one first-class object, filed
 * under `{name}/{viewer}-{page}` so a section directory holds all
 * viewers side by side for easy comparison.
 *
 * View pages get an extra pass that walks any Bootstrap `.nav-tabs`
 * present on the page and captures each tab's content
 * (`{name}/{viewer}-view-tab-{slug}`).
 *
 * Edit pages get an extra shot after submitting the form
 * (`{name}/{viewer}-edit-submitted`) to capture whatever the app renders
 * post-save, whether that's the success callout on the redirect target
 * or the validation-error state on the same page.
 */
async function shootIndexViewEdit({segment, name, hasView = true, hasEdit = true, hasCheckout = false, viewer}) {
    const who = viewer ?? USERNAME;
    console.log(`→ ${name} (as ${who})`);
    await page.goto(`${BASE_URL}/${segment}`);
    await waitForTable();
    await shot(`${name}/${who}-${name}-index`);

    if (!hasView && !hasEdit) return;

    const id = await getFirstEntityId(segment);
    if (!id) {
        console.log(`  ! no rows in ${name}, skipping view/edit`);
        return;
    }

    if (hasView) {
        await page.goto(`${BASE_URL}/${segment}/${id}`);
        await page.waitForLoadState('networkidle').catch(() => {});
        await shot(`${name}/${who}-${name}-view`);
        await toggleInfoPanelAndShoot(`${name}/${who}-${name}-view`);
        await walkTabs(`${name}/${who}-${name}-view`);
    }
    if (hasEdit) {
        await page.goto(`${BASE_URL}/${segment}/${id}/edit`);
        await page.waitForLoadState('networkidle').catch(() => {});
        await shot(`${name}/${who}-${name}-edit`);
        await submitEditForm(`${name}/${who}-${name}-edit-submitted`);
    }
    if (hasCheckout) {
        await page.goto(`${BASE_URL}/${segment}/${id}/checkout`);
        await page.waitForLoadState('networkidle').catch(() => {});
        await shot(`${name}/${who}-${name}-checkout`);
    }
}

/**
 * If the current page has `.nav-tabs a[data-toggle=tab]`, click through
 * each tab (except the one that's already active, which is the state
 * the base shot already captured), wait a beat for the pane transition,
 * and screenshot. Skips silently on pages with no tabs.
 */
/**
 * If the current page has the info-panel toggle button, capture the
 * opposite state as an extra shot. The base view shot already caught
 * whatever state the page loaded in (expanded by default); this adds
 * `{basename}-info-collapsed` (or -expanded, depending on the starting
 * state) so docs can show what a compact/expanded view looks like.
 *
 * Info-panel state is persisted in localStorage.side_panel_state, but
 * we don't need to touch that. Clicking the toggle button drives the
 * DOM changes directly via the expandInfoSidePanel / collapseInfoSidePanel
 * functions defined in the default layout.
 */
async function toggleInfoPanelAndShoot(basename) {
    const toggle = page.locator('#expand-info-panel-button:visible');
    if (!(await toggle.count().catch(() => 0))) return;

    // Look at the panel's current expanded state so the suffix reflects
    // what we actually captured (not what we requested).
    const wasExpanded = await page.evaluate(
        () => !!document.querySelector('.side-box.expanded')
    ).catch(() => true);

    await toggle.click().catch(() => {});
    await page.waitForTimeout(400); // let the panel fade / column resize
    const suffix = wasExpanded ? 'info-collapsed' : 'info-expanded';
    await shot(`${basename}-${suffix}`);

    // Restore original state so subsequent shots on this page (tabs,
    // etc.) aren't stuck in the toggled layout.
    await toggle.click().catch(() => {});
    await page.waitForTimeout(200);
}

async function walkTabs(basename) {
    if (!TABS) return;
    const tabs = await page.locator('.nav-tabs a[data-toggle="tab"]:visible').all();
    if (tabs.length <= 1) return;

    for (const tab of tabs) {
        // Skip the tab that's already showing when we arrived. Its content
        // is what the base view shot captured, so re-shooting is wasteful.
        const isActive = await tab.evaluate((el) => el.closest('li')?.classList.contains('active') ?? false).catch(() => false);
        if (isActive) continue;

        const rawLabel = ((await tab.textContent()) || '').trim();
        if (!rawLabel) continue;
        const slug = rawLabel.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        if (!slug) continue;

        await tab.click().catch(() => {});
        await page.waitForTimeout(400); // let the pane fade-in complete
        await waitForTable(); // some tabs house bootstrap-tables of their own
        await shot(`${basename}-tab-${slug}`);
    }
}

/**
 * Submit the primary form on the current page (typically an edit form)
 * and screenshot whatever renders afterward. Waits for network to
 * settle on the resulting page so callouts/toast messages are fully
 * rendered. Skips silently if there's no obvious submit button, or if
 * SUBMIT_FORMS=false was passed in the environment.
 */
async function submitEditForm(outName) {
    if (!SUBMIT_FORMS) return;
    // Force redirect_option=index so the post-submit destination is
    // always the section's index page. Without this, Snipe-IT's
    // Helper::getRedirectOption reads redirect_option=back (the form
    // default) and uses session's url.intended, which for pages whose
    // view template embeds a `<img src=".../qr_code">` gets set to the
    // qr_code endpoint (because that image load counts as a "previous
    // URL"). The result is that Save lands on a raw PNG, which isn't
    // what we want to screenshot.
    await page.evaluate(() => {
        const field = document.querySelector('form [name="redirect_option"]');
        if (field) field.value = 'index';
    });
    // Find the primary Save button of the edit form:
    //   - Exclude the topbar search form (#topSearchButton) which is the
    //     first `[type=submit]` on every page and would fire an empty
    //     asset-tag lookup ("Warning: Asset with tag not found.") if
    //     naively clicked.
    //   - Exclude the hidden logout form's submit that also lives in the
    //     header.
    //   - Prefer `.last()`: on Snipe-IT edit forms the Save button is
    //     rendered at the bottom of the form, after any inline widget
    //     submit buttons the form might contain.
    // Refactored forms wrap up with `<x-box.footer />` which always
    // renders the Save as `<button id="submit_button">`. Target that
    // first because it's a stable semantic hook that can't collide
    // with the topbar search or any inline widget submit.
    let submit = page.locator('#submit_button:visible').first();
    if (!(await submit.count().catch(() => 0))) {
        // Legacy forms that haven't been refactored to <x-box.footer />
        // fall back to a visible, non-topbar submit button. `.last()`
        // because Snipe-IT edit forms put Save at the bottom of the
        // form, after any inline widget submits.
        submit = page
            .locator('form button[type="submit"]:visible, form input[type="submit"]:visible')
            .filter({hasNot: page.locator('#topSearchButton')})
            .last();
    }

    // Extra guard: the locator above can still match nothing if the
    // page has no form. Check count before clicking.
    if (!(await submit.count().catch(() => 0))) return;

    // If the resolved element is inside the top navbar (some layouts
    // wrap the search form in `<header>` or `.main-header`), bail out
    // rather than fire the wrong submit.
    const insideHeader = await submit.evaluate(
        (el) => !!el.closest('header, nav, .main-header, .navbar')
    ).catch(() => false);
    if (insideHeader) return;

    await submit.click().catch(() => {});
    // Either the server redirects (success) or re-renders with errors.
    // Wait for the response and then for the new page to settle.
    await page.waitForLoadState('networkidle', {timeout: 15_000}).catch(() => {});
    await shot(outName);
}

/**
 * Log in as a specific user. Clears cookies first so repeated calls
 * cleanly switch accounts.
 */
async function loginAs(username, password = PASSWORD) {
    await context.clearCookies();
    await page.goto(`${BASE_URL}/login`);
    await page.fill('#username', username);
    await page.fill('#password-field', password);
    await Promise.all([
        page.waitForURL((url) => !url.pathname.endsWith('/login')),
        page.click('button[type=submit]'),
    ]);
    await page.waitForLoadState('networkidle').catch(() => {});
}

/**
 * Block-scoped session helper: log in as username, run fn, any shots
 * inside get captured under that session.
 */
async function asUser(username, fn, password) {
    console.log(`→ session: ${username}`);
    await loginAs(username, password);
    await fn();
}

/**
 * For one resource-manager, shoot the four canonical pages (index,
 * view, edit, create) scoped strictly to the resource they manage.
 * Shots land in `${name}/{username}-{page}` alongside the admin shots.
 */
async function shootManager({username, segment, name, hasCheckout = true}) {
    await asUser(username, async () => {
        await page.goto(`${BASE_URL}/${segment}`);
        await waitForTable();
        await shot(`${name}/${username}-${name}-index`);

        const id = await getFirstEntityId(segment);
        if (id) {
            await page.goto(`${BASE_URL}/${segment}/${id}`);
            await page.waitForLoadState('networkidle').catch(() => {});
            await shot(`${name}/${username}-${name}-view`);
            await toggleInfoPanelAndShoot(`${name}/${username}-${name}-view`);
            await walkTabs(`${name}/${username}-${name}-view`);

            await page.goto(`${BASE_URL}/${segment}/${id}/edit`);
            await page.waitForLoadState('networkidle').catch(() => {});
            await shot(`${name}/${username}-${name}-edit`);
            await submitEditForm(`${name}/${username}-${name}-edit-submitted`);

            if (hasCheckout) {
                await page.goto(`${BASE_URL}/${segment}/${id}/checkout`);
                await page.waitForLoadState('networkidle').catch(() => {});
                await shot(`${name}/${username}-${name}-checkout`);
            }
        } else {
            console.log(`  ! ${username}: no rows in ${name}, skipping view/edit`);
        }

        await page.goto(`${BASE_URL}/${segment}/create`);
        await page.waitForLoadState('networkidle').catch(() => {});
        await shot(`${name}/${username}-${name}-create`);
    });
}

// -------------------------------------------------------------------
// Ad-hoc single-shot mode (short-circuit)
// -------------------------------------------------------------------
if (ONE_SHOT) {
    const asUsername = cli.as ?? USERNAME;
    const uri = ONE_SHOT.replace(/^\//, '');
    const baseName = cli.name ?? (uri === '' ? 'root' : uri.replace(/[/?&=]/g, '__'));
    // If --tab was requested, append its slug to the filename so the
    // shot is self-identifying.
    const tabSuffix = cli.tab
        ? '-tab-' + cli.tab.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
        : '';
    const outName = `adhoc/${asUsername}-${baseName}${tabSuffix}`;

    console.log(`→ logging in as ${asUsername}`);
    await loginAs(asUsername, PASSWORD);

    console.log(`→ ${BASE_URL}/${uri}`);
    await page.goto(`${BASE_URL}/${uri}`, {waitUntil: 'domcontentloaded'});
    await page.waitForLoadState('networkidle').catch(() => {});
    await capPagination();

    // If --tab was requested, find the matching Bootstrap tab and
    // activate it before shooting. Case-insensitive substring match on
    // the visible tab labels, so `--tab licenses` matches "Licenses"
    // or "Licenses (5)". Fails loudly if nothing matches so the user
    // isn't left staring at a "why did this shoot the wrong tab" shot.
    if (cli.tab) {
        const wanted = cli.tab.toLowerCase();
        const tabs = await page.locator('.nav-tabs a[data-toggle="tab"]:visible').all();
        let matched = false;
        // Normalize labels so multiline tab text (icon + label + count
        // wrapped across nodes) doesn't blow up the log line or trip
        // the substring match on stray whitespace.
        const norm = (s) => (s || '').replace(/\s+/g, ' ').trim();
        for (const t of tabs) {
            const label = norm(await t.textContent()).toLowerCase();
            if (label.includes(wanted)) {
                await t.click();
                await page.waitForTimeout(400);
                await capPagination();
                matched = true;
                console.log(`  ↪ tab: "${label}"`);
                break;
            }
        }
        if (!matched) {
            const labels = await Promise.all(tabs.map(async (t) => norm(await t.textContent())));
            console.log(`  ! tab "${cli.tab}" not found. Available: ${labels.filter(Boolean).join(', ') || '(none)'}`);
            await browser.close();
            process.exit(1);
        }
    }

    await shot(outName);

    const elapsedMs = performance.now() - RUN_STARTED_AT;
    console.log(`Done. 1 screenshot written to ${OUT}/${outName}-${RUN_TIMESTAMP}.png in ${(elapsedMs / 1000).toFixed(2)}s.`);
    await browser.close();
    process.exit(0);
}

// -------------------------------------------------------------------
// Full walkthrough
// -------------------------------------------------------------------

console.log('→ logging in');
await loginAs(USERNAME, PASSWORD);

// First-class objects: index + view + edit for each.
// `hasView: false` skips the view shot for entities without a detail page.
const firstClassObjects = [
    {segment: 'hardware', name: 'assets', hasCheckout: true},
    {segment: 'licenses', name: 'licenses', hasCheckout: true},
    {segment: 'accessories', name: 'accessories', hasCheckout: true},
    {segment: 'consumables', name: 'consumables', hasCheckout: true},
    {segment: 'components', name: 'components', hasCheckout: true},
    {segment: 'kits', name: 'kits', hasCheckout: true},
    {segment: 'users', name: 'users'},
    {segment: 'models', name: 'models'},
    {segment: 'categories', name: 'categories'},
    {segment: 'manufacturers', name: 'manufacturers'},
    {segment: 'suppliers', name: 'suppliers'},
    {segment: 'locations', name: 'locations'},
    {segment: 'departments', name: 'departments'},
    {segment: 'companies', name: 'companies'},
    {segment: 'statuslabels', name: 'statuslabels'},
    {segment: 'depreciations', name: 'depreciations'},
    {segment: 'fields', name: 'custom-fields', hasView: false},
    {segment: 'fields/fieldsets', name: 'fieldsets'},
    {segment: 'maintenance-types', name: 'maintenance-types', hasView: false},
];
for (const obj of firstClassObjects) {
    if (!includesSection(obj.name)) continue;
    await shootIndexViewEdit(obj);
}

// Extras: create form, status-dropdown interaction, bulk pages.
if (includesSection('assets')) {
    console.log('→ assets extras');
    await page.goto(`${BASE_URL}/hardware/create`);
    await shot(`assets/${USERNAME}-assets-create`);

    await page.locator('#status_select_id + .select2').first().click().catch(async () => {
        await page.locator('label[for="status_id"] ~ .select2, label:has-text("Status") ~ .select2').first().click();
    });
    await page.waitForSelector('.select2-dropdown', {timeout: 5_000}).catch(() => {});
    await shot(`assets/${USERNAME}-assets-create-status-dropdown`, {fullPage: false});

    // Bulk actions live on the assets side of the app.
    await page.goto(`${BASE_URL}/hardware/bulkcheckout`);
    await shot(`assets/${USERNAME}-assets-bulk-checkout`);

    await page.goto(`${BASE_URL}/hardware/bulkcheckin`);
    await shot(`assets/${USERNAME}-assets-bulk-checkin`);
}

// Extra create forms for users and licenses (bundled with those sections
// so `--section users` includes the users create shot).
if (includesSection('users')) {
    console.log('→ users extras');
    await page.goto(`${BASE_URL}/users/create`);
    await shot(`users/${USERNAME}-users-create`);
}
if (includesSection('licenses')) {
    console.log('→ licenses extras');
    await page.goto(`${BASE_URL}/licenses/create`);
    await shot(`licenses/${USERNAME}-licenses-create`);
}

// Standalone singleton sections. These don't fit the index/view/edit
// shape so they're their own config; each entry is a section name plus
// a list of pages to hit within that section.
const standaloneSections = [
    {name: 'dashboard', pages: [{path: '/', suffix: 'index'}]},
    {name: 'settings', pages: [
        {path: '/admin', suffix: 'index'},
        {path: '/admin/settings', suffix: 'general'},
    ]},
    {name: 'reports', pages: [{path: '/reports', suffix: 'index'}]},
];
for (const section of standaloneSections) {
    if (!includesSection(section.name)) continue;
    console.log(`→ ${section.name}`);
    for (const p of section.pages) {
        await page.goto(`${BASE_URL}${p.path}`);
        await shot(`${section.name}/${USERNAME}-${section.name}-${p.suffix}`);
    }
}

// Resource-manager perspectives: same-page shots as each scoped user.
// Shots land in the same section directory as admin shots (via the
// `${name}/{username}-{page}` naming) so you can compare side by side.
const managers = [
    {username: 'assetmgr', segment: 'hardware', name: 'assets'},
    {username: 'licensemgr', segment: 'licenses', name: 'licenses'},
    {username: 'accessorymgr', segment: 'accessories', name: 'accessories'},
    {username: 'consumablemgr', segment: 'consumables', name: 'consumables'},
    {username: 'componentmgr', segment: 'components', name: 'components'},
    // Users are checkout targets, not checkoutable subjects, so no
    // `/users/{id}/checkout` route to shoot.
    {username: 'usermgr', segment: 'users', name: 'users', hasCheckout: false},
];
for (const mgr of managers) {
    if (!includesSection(mgr.name)) continue;
    await shootManager(mgr);
}

// -------------------------------------------------------------------
// All-routes superuser sweep
// -------------------------------------------------------------------
if (ALL_ROUTES && !SECTION_FILTER) {
    console.log('→ all-routes sweep (superuser)');

    const proc = spawnSync('php', ['artisan', 'route:list', '--json'], {
        encoding: 'utf8',
        maxBuffer: 32 * 1024 * 1024,
    });
    if (proc.status !== 0) {
        console.log(`  ! route:list failed: ${proc.stderr}`);
    } else {
        const routes = JSON.parse(proc.stdout);
        const skipPrefixes = [
            '_debugbar', 'api/', 'livewire/', 'livewire-', 'telescope', 'horizon',
            'oauth/', 'sanctum/', '_ignition', 'sso/',
            'password/', 'auth/logout', 'logout', 'login',
            'saml/', 'scim/', 'google/',
            'setup/', 'setup', 'health',
            'test-email',
        ];
        const skipContains = ['/export', 'export.'];
        const skipSuffixes = ['.js', '.map', '.json', '.xml', '.csv', '.pdf'];

        const usable = routes.filter((r) => {
            if (!r.method || !r.method.includes('GET')) return false;
            const uri = (r.uri || '').replace(/^\//, '');
            if (uri.includes('{')) return false;
            if (uri === '') return false;
            if (skipPrefixes.some((p) => uri === p.replace(/\/$/, '') || uri.startsWith(p))) return false;
            if (skipContains.some((c) => uri.includes(c))) return false;
            if (skipSuffixes.some((s) => uri.endsWith(s))) return false;
            return true;
        });
        console.log(`  ${usable.length} parameter-free GET routes to sweep`);

        await loginAs(USERNAME, PASSWORD);

        for (const r of usable) {
            const uri = r.uri.replace(/^\//, '');
            const slug = uri === '' ? '_root' : uri.replace(/[/?&=]/g, '__');
            try {
                await page.goto(`${BASE_URL}/${uri}`, {waitUntil: 'domcontentloaded', timeout: 20_000});
                await page.waitForLoadState('networkidle', {timeout: 10_000}).catch(() => {});
                await capPagination();
                await shot(`all-routes/${slug}`);
            } catch (e) {
                console.log(`  ! ${uri}: ${e.message.split('\n')[0]}`);
            }
        }
    }
}

console.log('');
const elapsedMs = performance.now() - RUN_STARTED_AT;
const elapsed = elapsedMs > 60_000
    ? `${Math.floor(elapsedMs / 60_000)}m ${((elapsedMs % 60_000) / 1000).toFixed(1)}s`
    : `${(elapsedMs / 1000).toFixed(2)}s`;
console.log(`Done. ${shotCount} screenshots written to ${OUT} in ${elapsed}.`);
await browser.close();
